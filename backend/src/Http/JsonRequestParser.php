<?php

declare(strict_types=1);

namespace GlycoFlow\Http;

use JsonException;

/**
 * Parses a JSON request body into an associative array.
 *
 * The parser operates on a supplied raw body string rather than reading global
 * input directly, which keeps the decoding logic unit-testable.
 */
final class JsonRequestParser
{
    /**
     * Decode a JSON object body into an associative array.
     *
     * @return array<string, mixed>
     *
     * @throws ApiException when the body is empty (and not allowed), malformed,
     *                      or does not represent a JSON object.
     */
    public function parse(string $rawBody, bool $allowEmpty = false): array
    {
        $trimmed = trim($rawBody);
        if ($trimmed === '') {
            if ($allowEmpty) {
                return [];
            }

            throw ApiException::malformedJson('A JSON request body is required.');
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw ApiException::malformedJson(previous: $exception);
        }

        // A top-level JSON object always begins with "{". Checking the leading
        // structural character distinguishes "{}" (accepted) from "[]" and other
        // JSON arrays (rejected), which would otherwise both decode to an empty
        // PHP array. Scalars and null fail the is_array() check.
        if (!is_array($decoded) || $trimmed[0] !== '{') {
            throw ApiException::malformedJson('The request body must be a JSON object.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
