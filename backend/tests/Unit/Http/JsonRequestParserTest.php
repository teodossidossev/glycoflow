<?php

declare(strict_types=1);

namespace GlycoFlow\Tests\Unit\Http;

use GlycoFlow\Http\ApiException;
use GlycoFlow\Http\JsonRequestParser;
use PHPUnit\Framework\TestCase;

final class JsonRequestParserTest extends TestCase
{
    private JsonRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new JsonRequestParser();
    }

    public function testParsesValidJsonObject(): void
    {
        $result = $this->parser->parse('{"email":"user@example.com","remember":true}');

        self::assertSame(['email' => 'user@example.com', 'remember' => true], $result);
    }

    public function testParsesEmptyJsonObject(): void
    {
        self::assertSame([], $this->parser->parse('{}'));
    }

    public function testThrowsOnMalformedJson(): void
    {
        try {
            $this->parser->parse('{"email": ');
            self::fail('Expected ApiException was not thrown.');
        } catch (ApiException $exception) {
            self::assertSame('MALFORMED_JSON', $exception->errorCode());
            self::assertSame(400, $exception->statusCode());
        }
    }

    public function testRejectsJsonArray(): void
    {
        $this->expectException(ApiException::class);

        $this->parser->parse('[1, 2, 3]');
    }

    public function testRejectsEmptyJsonArray(): void
    {
        // "[]" and "{}" both decode to an empty PHP array; only "{}" is valid.
        try {
            $this->parser->parse('[]');
            self::fail('Expected ApiException was not thrown for an empty JSON array.');
        } catch (ApiException $exception) {
            self::assertSame('MALFORMED_JSON', $exception->errorCode());
            self::assertSame(400, $exception->statusCode());
        }
    }

    public function testRejectsScalarJson(): void
    {
        $this->expectException(ApiException::class);

        $this->parser->parse('42');
    }

    public function testRejectsEmptyBodyByDefault(): void
    {
        $this->expectException(ApiException::class);

        $this->parser->parse('');
    }

    public function testAllowsEmptyBodyWhenPermitted(): void
    {
        self::assertSame([], $this->parser->parse('', allowEmpty: true));
    }
}
