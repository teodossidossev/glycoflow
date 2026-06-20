# ADR 003: PHP JSON API and Session Authentication

## Status

Accepted

## Context

Production supports PHP but not Node.js.

The frontend and backend can be deployed on the same origin.

## Decision

Use PHP JSON endpoints with PHP session authentication.

Use CSRF protection for mutations.

Do not use JWT for the initial same-origin application.

## Consequences

Positive:

- native hosting compatibility;
- simple same-origin cookies;
- straightforward logout and session invalidation;
- no frontend token storage.

Negative:

- session storage must be configured correctly;
- CSRF protection is required;
- horizontal scaling would need shared session storage, which is not relevant initially.

## Alternatives

- JWT: unnecessary complexity and more client-side security responsibility.
- PHP framework: not required for the initial API size.

## Review Triggers

- multiple independent clients require token auth;
- API becomes public;
- hosting environment changes.
