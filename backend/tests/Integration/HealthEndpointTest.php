<?php

declare(strict_types=1);

namespace GlycoFlow\Tests\Integration;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the health endpoint script end to end in an isolated process so the
 * bootstrap's global handler registration and emitted output stay contained.
 */
final class HealthEndpointTest extends TestCase
{
    private const ENDPOINT = __DIR__ . '/../../public/api/health.php';

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testGetReturnsOkPayloadWithStatus200(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        require self::ENDPOINT;
        $output = (string) ob_get_clean();

        self::assertSame(200, http_response_code());
        self::assertSame(
            ['data' => ['status' => 'ok', 'service' => 'glycoflow-api']],
            json_decode($output, true),
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testNonGetReturnsMethodNotAllowedWithStatus405(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        require self::ENDPOINT;
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);

        self::assertSame(405, http_response_code());
        self::assertSame('METHOD_NOT_ALLOWED', $decoded['error']['code']);
        self::assertArrayNotHasKey('status', $decoded['error']);
    }
}
