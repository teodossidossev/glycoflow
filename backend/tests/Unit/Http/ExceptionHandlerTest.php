<?php

declare(strict_types=1);

namespace GlycoFlow\Tests\Unit\Http;

use GlycoFlow\Http\ApiException;
use GlycoFlow\Http\ExceptionHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionHandlerTest extends TestCase
{
    public function testKnownApiExceptionPreservesSafePayload(): void
    {
        $exception = ApiException::validation(['email' => 'Required.'], 'The submitted data is invalid.');

        $response = (new ExceptionHandler())->toResponse($exception);

        self::assertSame(422, $response->status);
        self::assertSame(
            [
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The submitted data is invalid.',
                    'fields' => ['email' => 'Required.'],
                ],
            ],
            $response->body,
        );
        self::assertArrayNotHasKey('requestId', $response->body['error']);
    }

    public function testUnexpectedExceptionIsSanitized(): void
    {
        $exception = new RuntimeException('Database connection string mysql://secret@host failed.');

        $response = $this->captureWithoutLogOutput(
            static fn (): \GlycoFlow\Http\JsonResponse => (new ExceptionHandler())->toResponse($exception)
        );

        self::assertSame(500, $response->status);
        self::assertSame('INTERNAL_ERROR', $response->body['error']['code']);
        self::assertSame('An unexpected error occurred.', $response->body['error']['message']);

        // The internal message must never reach the client payload.
        $encoded = $response->encode();
        self::assertStringNotContainsString('secret', $encoded);
        self::assertStringNotContainsString('mysql', $encoded);
    }

    public function testUnexpectedExceptionResponseCarriesRequestId(): void
    {
        $response = $this->captureWithoutLogOutput(
            static fn (): \GlycoFlow\Http\JsonResponse =>
                (new ExceptionHandler())->toResponse(new RuntimeException('boom'))
        );

        self::assertArrayHasKey('requestId', $response->body['error']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $response->body['error']['requestId']);
    }

    /**
     * Run a callback while redirecting error_log() output to a temporary file so
     * the expected log line does not pollute the test output.
     *
     * @param callable(): \GlycoFlow\Http\JsonResponse $callback
     */
    private function captureWithoutLogOutput(callable $callback): \GlycoFlow\Http\JsonResponse
    {
        $logFile = tempnam(sys_get_temp_dir(), 'glycoflow-log-');
        self::assertIsString($logFile);

        $previousErrorLog = ini_get('error_log');
        ini_set('error_log', $logFile);

        try {
            return $callback();
        } finally {
            ini_set('error_log', $previousErrorLog === false ? '' : $previousErrorLog);
            @unlink($logFile);
        }
    }
}
