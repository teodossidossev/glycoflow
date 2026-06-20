<?php

declare(strict_types=1);

namespace GlycoFlow\Tests\Unit\Config;

use GlycoFlow\Config\Environment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EnvironmentTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $envSnapshot = [];

    /**
     * @var array<string, mixed>
     */
    private array $serverSnapshot = [];

    /**
     * @var array<string, string>
     */
    private array $getenvSnapshot = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Snapshot each source independently so it can be restored exactly.
        // getenv() without an argument is typed array|false, so guard against
        // environments where it does not return an array.
        $currentEnvironment = getenv();
        $this->envSnapshot = $_ENV;
        $this->serverSnapshot = $_SERVER;
        $this->getenvSnapshot = is_array($currentEnvironment) ? $currentEnvironment : [];
    }

    protected function tearDown(): void
    {
        // Restore $_ENV and $_SERVER to their exact prior contents.
        $_ENV = $this->envSnapshot;
        $_SERVER = $this->serverSnapshot;

        // Restore getenv()/putenv() state exactly: drop keys added during the
        // test, then reapply every original key and value. getenv() is typed
        // array|false, so guard before iterating.
        $currentEnvironment = getenv();
        foreach (is_array($currentEnvironment) ? $currentEnvironment : [] as $key => $value) {
            if (!array_key_exists($key, $this->getenvSnapshot)) {
                putenv($key);
            }
        }
        foreach ($this->getenvSnapshot as $key => $value) {
            putenv($key . '=' . $value);
        }

        parent::tearDown();
    }

    public function testReturnsRequiredStringValue(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_STRING', 'present');

        self::assertSame('present', (new Environment())->getString('GLYCOFLOW_TEST_STRING'));
    }

    public function testRequiredStringThrowsWhenMissing(): void
    {
        $this->expectException(RuntimeException::class);

        (new Environment())->getString('GLYCOFLOW_TEST_MISSING');
    }

    public function testRequiredStringRejectsEmptyValue(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_EMPTY', '');

        $this->expectException(RuntimeException::class);

        (new Environment())->getString('GLYCOFLOW_TEST_EMPTY');
    }

    public function testRequiredStringRejectsWhitespaceOnlyValue(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_WHITESPACE', "  \t ");

        $this->expectException(RuntimeException::class);

        (new Environment())->getString('GLYCOFLOW_TEST_WHITESPACE');
    }

    public function testRequiredStringPreservesInternalSpacing(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_SPACED', ' value with spaces ');

        self::assertSame(' value with spaces ', (new Environment())->getString('GLYCOFLOW_TEST_SPACED'));
    }

    public function testOptionalStringFallsBackToDefault(): void
    {
        $environment = new Environment();

        self::assertSame('fallback', $environment->getOptionalString('GLYCOFLOW_TEST_MISSING', 'fallback'));
        self::assertNull($environment->getOptionalString('GLYCOFLOW_TEST_MISSING'));
    }

    public function testOptionalStringCanBeEmpty(): void
    {
        // Optional values such as DB_PASSWORD must be allowed to be empty.
        $this->setEnv('GLYCOFLOW_TEST_OPTIONAL_EMPTY', '');

        self::assertSame('', (new Environment())->getOptionalString('GLYCOFLOW_TEST_OPTIONAL_EMPTY', 'fallback'));
    }

    public function testOptionalStringReturnsSetValue(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_OPTIONAL', 'set-value');

        self::assertSame('set-value', (new Environment())->getOptionalString('GLYCOFLOW_TEST_OPTIONAL', 'fallback'));
    }

    public function testParsesIntegerValue(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_INT', '3306');

        self::assertSame(3306, (new Environment())->getInt('GLYCOFLOW_TEST_INT'));
    }

    public function testIntegerFallsBackToDefaultWhenUnset(): void
    {
        self::assertSame(3306, (new Environment())->getInt('GLYCOFLOW_TEST_MISSING', 3306));
    }

    public function testIntegerThrowsOnNonNumericValue(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_INT', 'not-a-number');

        $this->expectException(RuntimeException::class);

        (new Environment())->getInt('GLYCOFLOW_TEST_INT');
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function booleanProvider(): array
    {
        return [
            'one' => ['1', true],
            'true' => ['true', true],
            'yes' => ['yes', true],
            'on' => ['on', true],
            'zero' => ['0', false],
            'false' => ['false', false],
            'no' => ['no', false],
            'off' => ['off', false],
            'mixed case true' => ['TrUe', true],
        ];
    }

    #[DataProvider('booleanProvider')]
    public function testParsesBooleanValue(string $raw, bool $expected): void
    {
        $this->setEnv('GLYCOFLOW_TEST_BOOL', $raw);

        self::assertSame($expected, (new Environment())->getBool('GLYCOFLOW_TEST_BOOL'));
    }

    public function testBooleanFallsBackToDefaultWhenUnset(): void
    {
        self::assertTrue((new Environment())->getBool('GLYCOFLOW_TEST_MISSING', true));
        self::assertFalse((new Environment())->getBool('GLYCOFLOW_TEST_MISSING'));
    }

    public function testBooleanThrowsOnInvalidValue(): void
    {
        $this->setEnv('GLYCOFLOW_TEST_BOOL', 'maybe');

        $this->expectException(RuntimeException::class);

        (new Environment())->getBool('GLYCOFLOW_TEST_BOOL');
    }

    private function setEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}
