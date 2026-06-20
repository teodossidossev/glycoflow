<?php

declare(strict_types=1);

namespace GlycoFlow\Config;

use Dotenv\Dotenv;
use RuntimeException;

/**
 * Reads configuration from the process environment.
 *
 * Values are resolved from $_ENV, $_SERVER, and getenv() in that order.
 * A repository-root .env file may be loaded for local development; production
 * relies on real environment variables. Exception messages never include the
 * resolved values, only the variable name, to avoid leaking secrets.
 */
final class Environment
{
    public function __construct(private readonly ?string $rootPath = null)
    {
    }

    /**
     * Build an environment and load a repository-root .env file when present.
     */
    public static function load(string $rootPath): self
    {
        $environment = new self($rootPath);
        $environment->loadDotenvIfPresent();

        return $environment;
    }

    /**
     * Load the repository-root .env file when it exists, otherwise continue.
     */
    public function loadDotenvIfPresent(): void
    {
        if ($this->rootPath === null) {
            return;
        }

        $envFile = $this->rootPath . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($envFile)) {
            return;
        }

        // safeLoad() does not overwrite existing values and does not throw when
        // the file is missing, which keeps non-local environments unaffected.
        Dotenv::createImmutable($this->rootPath)->safeLoad();
    }

    /**
     * Return a required string value or throw when it is unset, empty, or
     * whitespace-only. The original (untrimmed) value is returned so intentional
     * internal spacing is preserved.
     */
    public function getString(string $key): string
    {
        $value = $this->rawValue($key);
        if ($value === null || trim($value) === '') {
            throw new RuntimeException(sprintf('Required environment variable "%s" is not set.', $key));
        }

        return $value;
    }

    /**
     * Return an optional string value, falling back to the provided default.
     */
    public function getOptionalString(string $key, ?string $default = null): ?string
    {
        $value = $this->rawValue($key);

        return $value ?? $default;
    }

    /**
     * Return an integer value, using the default when unset and validating format.
     */
    public function getInt(string $key, ?int $default = null): int
    {
        $value = $this->rawValue($key);
        if ($value === null || $value === '') {
            if ($default !== null) {
                return $default;
            }

            throw new RuntimeException(sprintf('Required environment variable "%s" is not set.', $key));
        }

        if (preg_match('/^-?\d+$/', $value) !== 1) {
            throw new RuntimeException(sprintf('Environment variable "%s" must be an integer.', $key));
        }

        return (int) $value;
    }

    /**
     * Return a boolean value, using the default when unset and validating format.
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->rawValue($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return match (strtolower(trim($value))) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => throw new RuntimeException(
                sprintf('Environment variable "%s" must be a boolean.', $key)
            ),
        };
    }

    /**
     * Resolve a raw string value from the available environment sources.
     */
    private function rawValue(string $key): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            return $this->normalize($_ENV[$key]);
        }

        if (array_key_exists($key, $_SERVER)) {
            return $this->normalize($_SERVER[$key]);
        }

        $value = getenv($key);

        return $value === false ? null : $value;
    }

    /**
     * Normalize a superglobal entry into a string or null.
     */
    private function normalize(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (string) $value;
        }

        return null;
    }
}
