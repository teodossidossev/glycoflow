<?php

declare(strict_types=1);

namespace GlycoFlow\Http;

use ErrorException;
use GlycoFlow\Support\RequestId;
use Throwable;

/**
 * Converts exceptions into client-safe JSON responses and centralises error
 * handling for endpoints.
 *
 * Known ApiExceptions keep their safe status and payload. Unexpected throwables
 * are reduced to a generic INTERNAL_ERROR response carrying a request ID, while
 * the internal details are written to the error log only.
 */
final class ExceptionHandler
{
    /**
     * Register PHP error and uncaught-exception handlers.
     *
     * PHP warnings/notices are converted to exceptions so they flow through the
     * same centralised handling instead of leaking into the response body.
     */
    public function register(): void
    {
        set_error_handler($this->convertErrorToException(...));
        set_exception_handler($this->handleUncaught(...));
    }

    /**
     * Convert a throwable into a client-safe JSON response without emitting it.
     */
    public function toResponse(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ApiException) {
            return JsonResponse::error(
                $exception->errorCode(),
                $exception->publicMessage(),
                $exception->statusCode(),
                $exception->fields(),
            );
        }

        $requestId = RequestId::generate();
        $this->log($exception, $requestId);

        return JsonResponse::error(
            'INTERNAL_ERROR',
            'An unexpected error occurred.',
            500,
            null,
            $requestId,
        );
    }

    /**
     * Promote a PHP error into an ErrorException when it is reportable.
     */
    public function convertErrorToException(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if ((error_reporting() & $severity) === 0) {
            // The error was suppressed (for example with @); let PHP handle it.
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Emit a safe response for an uncaught throwable.
     */
    public function handleUncaught(Throwable $exception): void
    {
        $this->toResponse($exception)->emit();
    }

    /**
     * Log internal error details with the correlating request ID.
     */
    private function log(Throwable $exception, string $requestId): void
    {
        error_log(sprintf(
            '[glycoflow] requestId=%s %s: %s in %s:%d',
            $requestId,
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        ));
    }
}
