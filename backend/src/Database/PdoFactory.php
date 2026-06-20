<?php

declare(strict_types=1);

namespace GlycoFlow\Database;

use GlycoFlow\Config\Environment;
use PDO;
use RuntimeException;

/**
 * Creates configured PDO connections to the MySQL/MariaDB database.
 *
 * No connection is opened until create() is called, which keeps construction
 * side-effect free and avoids touching the database during unit tests.
 */
final class PdoFactory
{
    public function __construct(private readonly Environment $environment)
    {
    }

    /**
     * Open a new PDO connection using the documented DB_* configuration.
     */
    public function create(): PDO
    {
        $user = $this->environment->getString('DB_USER');
        $password = $this->environment->getOptionalString('DB_PASSWORD', '') ?? '';

        return new PDO($this->dsn(), $user, $password, $this->options());
    }

    /**
     * Build the MySQL DSN from configuration without opening a connection.
     */
    public function dsn(): string
    {
        $host = $this->environment->getString('DB_HOST');
        $port = $this->environment->getInt('DB_PORT', 3306);
        $name = $this->environment->getString('DB_NAME');

        if ($port < 1 || $port > 65535) {
            throw new RuntimeException('DB_PORT must be a TCP port between 1 and 65535.');
        }

        return sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
    }

    /**
     * Return the PDO driver options applied to every connection.
     *
     * @return array<int, mixed>
     */
    public function options(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }
}
