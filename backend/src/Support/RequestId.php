<?php

declare(strict_types=1);

namespace GlycoFlow\Support;

/**
 * Generates opaque request identifiers used to correlate logs and responses.
 */
final class RequestId
{
    /**
     * Generate a cryptographically random, log- and JSON-safe identifier.
     *
     * The result is a 32-character lowercase hexadecimal string.
     */
    public static function generate(): string
    {
        return bin2hex(random_bytes(16));
    }
}
