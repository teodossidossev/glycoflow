# GlycoFlow Architecture

## 1. Constraints

Production shared hosting provides:

- PHP 8.2+;
- MySQL or MariaDB;
- HTTPS;
- cron where available;
- no Node.js runtime;
- no persistent worker;
- no WebSocket server.

Node.js is used only for local development, tests, builds, and CI.

## 2. High-Level Architecture

```text
Mobile browser / Installed PWA
            │
            │ HTTPS
            ▼
      Angular static app
            │
            │ JSON / multipart
            ▼
        PHP API
            │
            ├── sessions
            ├── CSRF
            ├── validation
            ├── authorization
            ├── AI integration
            ├── image storage
            └── business logic
            │
            ▼
      MySQL / MariaDB
```

Reminder flow:

```text
PHP cron
→ due reminder query
→ Web Push
→ Angular service worker
→ phone notification
```

AI flow:

```text
Angular camera
→ PHP upload endpoint
→ AI provider
→ normalized validated result
→ Angular confirmation
→ PHP save endpoint
→ database
```

Analysis and persistence are separate operations.

## 3. Angular Responsibilities

- UI;
- routing;
- camera access;
- image preview;
- image resize and compression;
- API calls;
- confirmation flows;
- local UI state;
- push subscription;
- notification deep links;
- charts;
- manual fallbacks.

Angular does not perform:

- database access;
- final authorization;
- trusted validation;
- AI-secret storage;
- reminder delivery.

## 4. PHP Responsibilities

- authentication;
- sessions;
- CSRF;
- validation;
- authorization;
- ownership;
- database access;
- AI requests;
- AI normalization;
- image lifecycle;
- reminder creation;
- push subscriptions;
- push sending;
- statistics;
- centralized errors.

## 5. Database Responsibilities

Store:

- users;
- meals;
- measurements;
- reminders;
- push subscriptions;
- optional AI metadata;
- optional push-delivery metadata.

Enforce:

- keys;
- constraints;
- ownership relationships;
- uniqueness;
- indexes.

## 6. AI Boundary

The AI provider receives only what is necessary.

It must not receive:

- database credentials;
- session cookies;
- unrelated history;
- unnecessary identity data.

The application uses a provider adapter that returns a normalized internal result.

## 7. Cron Worker

A PHP CLI script:

- finds due reminders;
- claims them safely;
- sends push;
- records attempts;
- handles expired subscriptions;
- updates state;
- retries eligible failures.

It must be idempotent and safe under overlapping execution.

## 8. Angular Structure

```text
frontend/src/app/
├── core/
│   ├── api/
│   ├── auth/
│   ├── camera/
│   ├── notifications/
│   ├── interceptors/
│   ├── guards/
│   └── error-handling/
├── features/
│   ├── auth/
│   ├── capture/
│   ├── dashboard/
│   ├── history/
│   ├── meals/
│   ├── meal-analysis/
│   └── settings/
├── shared/
│   ├── components/
│   ├── models/
│   ├── pipes/
│   ├── directives/
│   └── utils/
├── app.config.ts
├── app.routes.ts
└── app.ts
```

### Core

Application-wide infrastructure.

### Features

Feature-owned routes, services, state, components, tests.

### Shared

Reusable presentation and utilities without feature business logic.

## 9. State

Use Signals for:

- capture state;
- AI proposal;
- selected meal;
- save state;
- dashboard period;
- notification permission;
- current user.

Use HttpClient observables for HTTP.

Server data remains authoritative.

## 10. Camera State Machine

```text
idle
requesting_camera
camera_ready
capturing
processing_image
uploading
analyzing
reviewing_glucose
reviewing_weight
reviewing_meal
reviewing_post_meal
saving
success
error
```

A third-party state machine is not required for MVP.

## 11. PHP Structure

```text
backend/
├── public/
│   └── api/
│       ├── login.php
│       ├── logout.php
│       ├── session.php
│       ├── analyze-image.php
│       ├── measurements.php
│       ├── meals.php
│       ├── reminders.php
│       ├── push-subscriptions.php
│       ├── history.php
│       ├── stats.php
│       ├── meal-analysis.php
│       └── images.php
├── src/
│   ├── Auth/
│   ├── Config/
│   ├── Database/
│   ├── Http/
│   ├── Validation/
│   ├── Repositories/
│   ├── Services/
│   ├── AI/
│   ├── Images/
│   ├── Push/
│   ├── Statistics/
│   └── Support/
├── bootstrap.php
├── composer.json
└── composer.lock
```

## 12. Endpoint Layer

Endpoints:

1. load bootstrap;
2. verify method;
3. authenticate;
4. parse request;
5. check CSRF for mutations;
6. call service;
7. return response.

No raw SQL or complex business logic in endpoints.

## 13. Service Layer

Coordinates repositories, validators, storage, providers, and transactions.

## 14. Repository Layer

Contains all database access.

Every user-owned query includes `user_id`.

## 15. Validation

### Request

- fields;
- types;
- formats;
- lengths;
- accepted values;
- ranges.

### Domain

- ownership;
- reminder state;
- meal timing;
- type/context consistency.

### AI

- supported classification;
- required values;
- accepted units;
- confidence range;
- bounded strings.

## 16. Transactions

Use for:

- meal plus reminders;
- reminder-driven measurement plus completion;
- meal deletion plus reminder cancellation;
- multi-row state changes.

## 17. API

- HTTPS;
- JSON for standard operations;
- multipart for images;
- authenticated streaming for retained images.

## 18. Authentication

PHP sessions.

After login:

- regenerate ID;
- store user ID;
- issue CSRF token.

## 19. CSRF

Require token for:

```text
POST
PUT
PATCH
DELETE
```

## 20. Image Analysis

```text
capture
→ resize
→ upload
→ validate
→ temporary storage
→ AI
→ normalize
→ return proposal
```

Temporary files use random names and private storage.

## 21. AI Adapter

Conceptual internal output:

```json
{
  "kind": "glucose_meter",
  "confidence": 0.96,
  "value": 5.8,
  "unit": "mmol_l",
  "foodDescription": null,
  "detectedItems": []
}
```

Provider settings remain server-side.

## 22. Image Storage

Glucose-meter and scale images:

- temporary;
- deleted after confirmation.

Food images:

```text
private-storage/
└── users/
    └── {user-id}/
        └── meals/
            └── {generated-filename}
```

Serve through an authenticated PHP endpoint.

## 23. Reminders

Creation:

1. save meal;
2. calculate times;
3. create reminders;
4. commit.

States:

```text
pending
processing
sent
completed
dismissed
failed
cancelled
```

Worker claims before sending.

## 24. Push Subscriptions

One user may have multiple subscriptions.

Store endpoint, keys, device metadata, activity state, and failure state.

Never return secret subscription keys to the frontend after registration.

## 25. Statistics

Calculate server-side.

Initial statistics:

- current weight;
- starting weight;
- total change;
- latest fasting glucose;
- 7-day and 30-day averages;
- 7-day weight average;
- below-threshold count;
- chart series;
- meal response.

## 26. Time

Store UTC.

Display in user timezone.

Initial default:

```text
Europe/Sofia
```

## 27. Deletion

Measurement:

- delete measurement only.

Meal:

- cancel/delete reminders;
- set measurement `meal_id` to null;
- remove retained images.

Push subscription:

- remove selected device only.

## 28. Logging

May include:

- request ID;
- endpoint;
- method;
- status;
- category;
- latency;
- delivery result.

Must not include:

- passwords;
- cookies;
- CSRF tokens;
- API keys;
- push secrets;
- raw images;
- full health history.

## 29. Production Layout

```text
/home/account/
├── glycoflow-private/
│   ├── config/
│   ├── backend/
│   ├── storage/
│   ├── logs/
│   └── cron/
└── public_html/
    ├── index.html
    ├── assets/
    ├── manifest.webmanifest
    ├── ngsw-worker.js
    ├── .htaccess
    └── api/
```

## 30. Shared-Hosting Compatibility

Do not require:

- Node.js in production;
- Docker in production;
- Redis;
- RabbitMQ;
- persistent daemons;
- WebSockets;
- SSR;
- Kubernetes.

## 31. Review Triggers

Revisit architecture if:

- PWA push is unreliable on the target device;
- browser camera limits block the core flow;
- native APIs become mandatory;
- app-store distribution is required;
- offline-first sync becomes essential.
