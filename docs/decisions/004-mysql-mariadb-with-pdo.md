# ADR 004: MySQL/MariaDB with PDO

## Status

Accepted

## Context

Shared hosting provides MySQL or MariaDB.

The browser must never access the database directly.

## Decision

Use MySQL/MariaDB with InnoDB, `utf8mb4`, foreign keys, and PDO prepared statements.

All data access goes through repositories in PHP.

## Consequences

Positive:

- compatible with hosting;
- transactional;
- mature;
- adequate for the expected scale.

Negative:

- differences between MySQL and MariaDB must be considered;
- migrations must avoid unsupported version-specific features.

## Alternatives

- SQLite: weaker fit for shared-hosted multi-request use.
- PostgreSQL: not guaranteed on hosting.
- Browser database access: prohibited.

## Review Triggers

- hosting changes;
- database feature requirements exceed compatibility target.
