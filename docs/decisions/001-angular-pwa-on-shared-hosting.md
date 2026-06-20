# ADR 001: Angular PWA on Shared Hosting

## Status

Accepted

## Context

The project must run on shared hosting with PHP, MySQL/MariaDB, HTTPS, and no Node.js runtime.

The user prefers Angular and does not want React.

The product needs camera access, installability, push notifications, and static deployment.

## Decision

Use Angular as a mobile-first Progressive Web Application.

Use:

- standalone components;
- TypeScript;
- Router;
- HttpClient;
- Signals;
- typed Reactive Forms;
- official service worker;
- SCSS;
- Chart.js.

Build locally or in CI and deploy static files.

## Consequences

Positive:

- one frontend codebase;
- no Node.js in production;
- official Angular architecture;
- suitable for PWA camera and push flows.

Negative:

- browser and OS differences;
- iOS PWA limitations;
- service worker update complexity;
- less native integration than a native app.

## Alternatives

- React: rejected by user preference.
- Native apps: too expensive for MVP.
- SSR: unnecessary and incompatible with hosting constraints.
- Capacitor: possible later fallback.

## Review Triggers

- browser camera blocks the core flow;
- push is unreliable on the target phone;
- app-store distribution becomes required;
- native integrations become mandatory.
