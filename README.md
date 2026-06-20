# GlycoFlow

GlycoFlow is a private, mobile-first, camera-first tracker for:

- fasting glucose;
- post-meal glucose;
- weight;
- meals;
- reminders;
- trends and meal response.

The normal interaction is:

```text
Take a photo
→ AI recognizes glucose meter, body scale, or food
→ user confirms
→ record is saved
```

## Planned stack

### Frontend

- Angular
- TypeScript
- standalone components
- Signals
- typed Reactive Forms
- Angular Router
- Angular HttpClient
- Angular PWA / service worker
- SCSS
- Chart.js

### Backend

- PHP 8.2+
- JSON API
- PHP sessions
- CSRF protection
- PDO prepared statements
- Composer

### Data and integrations

- MySQL / MariaDB
- AI image analysis through the PHP backend
- Web Push with VAPID
- PHP cron for reminder delivery

## Production constraints

- shared hosting;
- no Node.js runtime;
- static Angular build;
- PHP and MySQL/MariaDB;
- HTTPS;
- browser never connects directly to MySQL.

## Documentation

Start with:

- [`CLAUDE.md`](CLAUDE.md)
- [`docs/README.md`](docs/README.md)
- [`docs/product.md`](docs/product.md)
- [`docs/user-flows.md`](docs/user-flows.md)
- [`docs/architecture.md`](docs/architecture.md)
- [`docs/data-model.md`](docs/data-model.md)
- [`docs/api.md`](docs/api.md)
- [`docs/security.md`](docs/security.md)
- [`docs/testing.md`](docs/testing.md)
- [`docs/deployment.md`](docs/deployment.md)
- [`docs/implementation-plan.md`](docs/implementation-plan.md)
- [`docs/decisions/`](docs/decisions/)

## Development principle

Implementation should proceed in small, reviewable steps. One GitHub issue should normally result in one focused branch and one focused pull request.
