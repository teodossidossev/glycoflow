<?php

declare(strict_types=1);

namespace GlycoFlow\Tests\Unit\Support;

use GlycoFlow\Support\RequestId;
use PHPUnit\Framework\TestCase;

final class RequestIdTest extends TestCase
{
    public function testGeneratesLowercaseHexIdentifier(): void
    {
        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', RequestId::generate());
    }

    public function testGeneratesDistinctIdentifiers(): void
    {
        self::assertNotSame(RequestId::generate(), RequestId::generate());
    }
}
