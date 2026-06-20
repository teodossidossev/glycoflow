<?php

declare(strict_types=1);

namespace GlycoFlow\Http;

/**
 * Immutable JSON response value object.
 *
 * Construction (the success()/error() factories and encode()) is free of side
 * effects so response shapes can be unit-tested. Only emit() touches headers
 * and output.
 */
final class JsonResponse
{
    /**
     * Pre-encoded safe payload used when the body cannot be JSON encoded.
     */
    private const INTERNAL_ERROR_BODY = '{"error":{"code":"INTERNAL_ERROR","message":"An unexpected error occurred."}}';

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly int $status,
        public readonly array $body,
        public readonly array $headers = [],
    ) {
    }

    /**
     * Build a successful response wrapping the payload in a "data" envelope.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public static function success(array $data, int $status = 200, array $headers = []): self
    {
        return new self($status, ['data' => $data], $headers);
    }

    /**
     * Build an error response in the documented error envelope.
     *
     * @param array<string, string>|null $fields
     * @param array<string, string> $headers
     */
    public static function error(
        string $code,
        string $message,
        int $status,
        ?array $fields = null,
        ?string $requestId = null,
        array $headers = [],
    ): self {
        $error = [
            'code' => $code,
            'message' => $message,
        ];
        if ($fields !== null) {
            $error['fields'] = $fields;
        }
        if ($requestId !== null) {
            $error['requestId'] = $requestId;
        }

        return new self($status, ['error' => $error], $headers);
    }

    /**
     * Encode the body to a JSON string, falling back to a safe error payload
     * when encoding fails.
     */
    public function encode(): string
    {
        return $this->resolve()[1];
    }

    /**
     * Return the status code that will actually be sent. When the body cannot
     * be encoded the effective status is 500, regardless of the original status.
     */
    public function effectiveStatus(): int
    {
        return $this->resolve()[0];
    }

    /**
     * Send the effective status code, headers, and encoded body. Side-effecting.
     */
    public function emit(): void
    {
        [$status, $json] = $this->resolve();

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        echo $json;
    }

    /**
     * Resolve the body to an encoded string and its effective status.
     *
     * @return array{0: int, 1: string}
     */
    private function resolve(): array
    {
        $json = json_encode($this->body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json !== false) {
            return [$this->status, $json];
        }

        // Encoding failed (for example invalid UTF-8): degrade to a safe 500.
        return [500, self::INTERNAL_ERROR_BODY];
    }
}
