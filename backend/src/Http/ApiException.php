<?php

declare(strict_types=1);

namespace GlycoFlow\Http;

use RuntimeException;
use Throwable;

/**
 * Application exception carrying a client-safe error payload.
 *
 * The public message is always safe to return to the client. Any internal
 * detail must be passed as the previous throwable so it can be logged but never
 * exposed in the response.
 */
final class ApiException extends RuntimeException
{
    /**
     * @param array<string, string>|null $fields
     */
    public function __construct(
        private readonly string $errorCode,
        private readonly string $publicMessage,
        private readonly int $statusCode,
        private readonly ?array $fields = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($publicMessage, 0, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function publicMessage(): string
    {
        return $this->publicMessage;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>|null
     */
    public function fields(): ?array
    {
        return $this->fields;
    }

    /**
     * The request body could not be decoded as JSON.
     */
    public static function malformedJson(
        string $message = 'The request body is not valid JSON.',
        ?Throwable $previous = null,
    ): self {
        return new self('MALFORMED_JSON', $message, 400, null, $previous);
    }

    /**
     * The HTTP method is not permitted for the endpoint.
     */
    public static function methodNotAllowed(string $message = 'The HTTP method is not allowed for this endpoint.'): self
    {
        return new self('METHOD_NOT_ALLOWED', $message, 405);
    }

    /**
     * The request failed validation.
     *
     * @param array<string, string> $fields
     */
    public static function validation(array $fields, string $message = 'The submitted data is invalid.'): self
    {
        return new self('VALIDATION_ERROR', $message, 422, $fields);
    }
}
