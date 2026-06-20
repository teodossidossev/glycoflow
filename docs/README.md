# GlycoFlow Documentation

## Product

- `product.md` — product goals, scope, and requirements.
- `user-flows.md` — locked user journeys and state transitions.

## Technical design

- `architecture.md` — system components and boundaries.
- `data-model.md` — entities, relationships, and schema rules.
- `api.md` — HTTP API contract.
- `security.md` — security model and operational rules.
- `testing.md` — test strategy and quality gates.
- `deployment.md` — local, CI, and shared-hosting deployment.
- `implementation-plan.md` — incremental implementation sequence.

## Architecture decisions

See `decisions/README.md`.

Accepted ADRs:

1. Angular PWA on shared hosting.
2. Camera-first interaction.
3. PHP JSON API with session authentication.
4. MySQL/MariaDB through PDO.
5. AI provider behind a backend adapter.
6. Web Push reminders through PHP cron.
7. Meals and measurements as separate entities.
8. User confirmation before persistence.
9. Private image storage and limited retention.
10. Angular Signals without NgRx for the MVP.
