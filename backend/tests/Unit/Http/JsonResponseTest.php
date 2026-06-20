<?php

declare(strict_types=1);

namespace GlycoFlow\Tests\Unit\Http;

use GlycoFlow\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

final class JsonResponseTest extends TestCase
{
    public function testSuccessWrapsPayloadInDataEnvelope(): void
    {
        $response = JsonResponse::success(['status' => 'ok']);

        self::assertSame(200, $response->status);
        self::assertSame(['data' => ['status' => 'ok']], $response->body);
    }

    public function testErrorBuildsErrorEnvelope(): void
    {
        $response = JsonResponse::error('VALIDATION_ERROR', 'Invalid.', 422, ['email' => 'Required.']);

        self::assertSame(422, $response->status);
        self::assertSame(
            ['error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Invalid.', 'fields' => ['email' => 'Required.']]],
            $response->body,
        );
    }

    public function testErrorOmitsOptionalKeysWhenAbsent(): void
    {
        $response = JsonResponse::error('METHOD_NOT_ALLOWED', 'Nope.', 405);

        self::assertSame(
            ['error' => ['code' => 'METHOD_NOT_ALLOWED', 'message' => 'Nope.']],
            $response->body,
        );
    }

    public function testErrorIncludesRequestIdWhenProvided(): void
    {
        $response = JsonResponse::error('INTERNAL_ERROR', 'Oops.', 500, null, 'abc123');

        self::assertArrayHasKey('requestId', $response->body['error']);
        self::assertSame('abc123', $response->body['error']['requestId']);
    }

    public function testEncodeProducesDocumentedHealthShape(): void
    {
        $response = JsonResponse::success(['status' => 'ok', 'service' => 'glycoflow-api']);

        self::assertSame('{"data":{"status":"ok","service":"glycoflow-api"}}', $response->encode());
    }

    public function testEncodeFailureFallsBackToInternalErrorWithStatus500(): void
    {
        // An invalid UTF-8 byte sequence makes json_encode() fail.
        $response = JsonResponse::success(['broken' => "\xC3\x28"]);

        self::assertSame(200, $response->status);
        self::assertSame(500, $response->effectiveStatus());
        self::assertSame(
            '{"error":{"code":"INTERNAL_ERROR","message":"An unexpected error occurred."}}',
            $response->encode(),
        );
    }

    public function testHeadersAreRetained(): void
    {
        $response = JsonResponse::error('METHOD_NOT_ALLOWED', 'Nope.', 405, null, null, ['Allow' => 'GET']);

        self::assertSame(['Allow' => 'GET'], $response->headers);
    }
}
