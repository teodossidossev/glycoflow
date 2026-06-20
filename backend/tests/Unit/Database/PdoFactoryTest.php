<?php

declare(strict_types=1);

namespace GlycoFlow\Tests\Unit\Database;

use GlycoFlow\Config\Environment;
use GlycoFlow\Database\PdoFactory;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PdoFactoryTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $touchedKeys = [];

    protected function tearDown(): void
    {
        foreach ($this->touchedKeys as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }
        $this->touchedKeys = [];

        parent::tearDown();
    }

    public function testBuildsUtf8mb4MysqlDsn(): void
    {
        $this->setEnv('DB_HOST', '127.0.0.1');
        $this->setEnv('DB_PORT', '3306');
        $this->setEnv('DB_NAME', 'glycoflow');

        $factory = new PdoFactory(new Environment());

        self::assertSame('mysql:host=127.0.0.1;port=3306;dbname=glycoflow;charset=utf8mb4', $factory->dsn());
    }

    public function testPortDefaultsTo3306WhenUnset(): void
    {
        $this->setEnv('DB_HOST', 'db.internal');
        $this->setEnv('DB_NAME', 'glycoflow');

        $factory = new PdoFactory(new Environment());

        self::assertStringContainsString('port=3306;', $factory->dsn());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function outOfRangePortProvider(): array
    {
        return [
            'zero' => ['0'],
            'negative' => ['-1'],
            'above maximum' => ['99999'],
        ];
    }

    #[DataProvider('outOfRangePortProvider')]
    public function testRejectsPortOutsideValidRange(string $port): void
    {
        $this->setEnv('DB_HOST', '127.0.0.1');
        $this->setEnv('DB_PORT', $port);
        $this->setEnv('DB_NAME', 'glycoflow');

        $factory = new PdoFactory(new Environment());

        $this->expectException(RuntimeException::class);

        $factory->dsn();
    }

    public function testProvidesSafePdoOptions(): void
    {
        $factory = new PdoFactory(new Environment());
        $options = $factory->options();

        self::assertSame(PDO::ERRMODE_EXCEPTION, $options[PDO::ATTR_ERRMODE]);
        self::assertSame(PDO::FETCH_ASSOC, $options[PDO::ATTR_DEFAULT_FETCH_MODE]);
        self::assertFalse($options[PDO::ATTR_EMULATE_PREPARES]);
    }

    private function setEnv(string $key, string $value): void
    {
        $this->touchedKeys[] = $key;
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}
