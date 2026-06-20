# GlycoFlow Deployment

## 1. Environments

- local development;
- CI;
- shared-hosting production.

Node.js is not used in production.

## 2. Local Requirements

- Node.js LTS compatible with the selected Angular version;
- npm;
- PHP 8.2+;
- Composer;
- MySQL or MariaDB;
- Git.

Optional:

- local HTTPS;
- Docker for development only.

## 3. Local Layout

```text
glycoflow/
├── frontend/
├── backend/
├── database/
├── cron/
├── docs/
└── ...
```

## 4. Configuration

Commit only `.env.example` or configuration examples.

Local and production secrets remain untracked.

Expected server-side values:

```text
APP_ENV
APP_URL
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
AI_API_KEY
AI_MODEL
VAPID_PUBLIC_KEY
VAPID_PRIVATE_KEY
VAPID_SUBJECT
STORAGE_PATH
LOG_PATH
```

## 5. Frontend Build

From `frontend/`:

```bash
npm ci
npm run lint
npm test
npm run build
```

The production output is static.

## 6. Backend Preparation

From `backend/`:

```bash
composer install --no-dev --optimize-autoloader
```

If Composer is unavailable on hosting, build `vendor/` locally and deploy it.

## 7. Production Layout

Preferred:

```text
/home/account/
├── glycoflow-private/
│   ├── config/
│   ├── backend/
│   │   ├── src/
│   │   ├── vendor/
│   │   └── bootstrap.php
│   ├── storage/
│   │   ├── temporary/
│   │   ├── meals/
│   │   └── logs/
│   └── cron/
└── public_html/
    ├── index.html
    ├── assets/
    ├── manifest.webmanifest
    ├── ngsw-worker.js
    ├── .htaccess
    └── api/
```

Only public frontend assets and endpoint entry files belong in `public_html`.

## 8. Rewrite Rules

SPA routes should fall back to `index.html`.

Do not rewrite:

- `/api/*`;
- existing static assets;
- service worker files;
- manifest.

## 9. HTTPS

Required for:

- camera APIs;
- service worker;
- push notifications;
- secure cookies.

Do not deploy production without HTTPS.

## 10. Database

Create database and user through hosting control panel.

Use least-privilege credentials.

Apply migrations in order.

Back up before schema changes.

Never edit an applied migration.

## 11. First User

Public registration is not part of MVP.

Create the initial user using:

- a protected CLI script;
- a one-time script removed immediately;
- or a controlled database seed.

Never store a plain password in the repository.

## 12. Cron

Example conceptual command:

```bash
php /home/account/glycoflow-private/cron/send-reminders.php
```

Preferred frequency: once per minute when hosting permits it.

The worker must remain safe if scheduled less frequently.

## 13. File Permissions

- config readable only by account;
- storage writable by PHP;
- uploads not executable;
- logs not public;
- public assets readable;
- endpoint files executable by PHP.

## 14. Service Worker Updates

Deploy frontend assets atomically where possible.

Avoid partially replacing files during active use.

Use versioned Angular build assets.

Test update behavior on the target phone.

## 15. Push Setup

1. generate VAPID key pair;
2. store private key server-side;
3. expose public key through safe configuration or endpoint;
4. register service worker;
5. request permission after explanation;
6. save subscription;
7. test deep link.

## 16. Smoke Tests

After deployment:

- open app;
- login;
- session check;
- camera permission;
- direct capture with mock or controlled input;
- save measurement;
- create meal;
- schedule reminder;
- verify cron;
- tap notification;
- save reminder measurement;
- verify history;
- verify stats;
- verify image access;
- verify logout.

## 17. Backups

Back up:

- database;
- retained meal images;
- production configuration in a secure location.

Define retention and restore procedure.

## 18. Rollback

Frontend:

- keep previous build archive;
- restore previous static files.

Backend:

- keep previous release;
- avoid destructive migrations;
- deploy code before/after migration according to compatibility.

Database:

- restore only when necessary and with backup verification.

## 19. CI

Initial CI should:

- install frontend dependencies;
- lint;
- test;
- build;
- install PHP dependencies;
- run syntax checks;
- run backend tests;
- scan for obvious secrets.

Deployment may remain manual initially.

## 20. Production Checklist

- HTTPS active;
- secure cookies active;
- secrets outside web root;
- debug disabled;
- logs private;
- uploads private;
- `.env` not public;
- API errors sanitized;
- cron installed;
- VAPID configured;
- AI key configured;
- migrations applied;
- initial user created;
- backup completed;
- smoke tests passed.
