# CLAUDE.md

## Project Overview

GlycoFlow is a private, mobile-first health tracking application for recording:

* blood glucose measurements;
* body weight;
* meals;
* post-meal glucose responses;
* fasting and eating windows;
* trends and statistics.

The primary interaction model is camera-first. The user should rarely need to type data manually.

This is not a medical diagnostic application. Do not generate medical diagnoses, treatment recommendations, or clinical claims.

## Source of Truth

Before implementing a task, read:

1. `CLAUDE.md`;
2. the relevant files under `docs/`;
3. the existing implementation and tests;
4. the GitHub issue or user request defining the task.

When documentation and implementation conflict, stop and report the conflict instead of silently choosing one.

Do not invent product behavior that is not documented or explicitly requested.

## Core Product Principles

### Mobile-first

The application is designed primarily for phones.

All screens must be usable:

* on a narrow mobile viewport;
* with one hand where practical;
* with large touch targets;
* without horizontal scrolling;
* without desktop-style dense forms;
* without requiring precise taps.

Desktop support is secondary.

### Camera-first

The main action is taking a photo. The application determines what the photo contains and proposes the corresponding record.

Direct camera capture behavior:

* glucose meter → fasting glucose measurement;
* body scale → weight measurement;
* food → meal start;
* unknown object → ask the user what was photographed or allow another photo.

A directly photographed glucose meter is treated as `fasting_glucose`, regardless of the time of day.

AI recognition must produce a proposal. The user must be able to confirm or correct the result before it is permanently saved.

Manual entry must remain available as a fallback.

### Minimize user input

Automatically set reasonable defaults:

* current date;
* current time;
* measurement type based on context;
* meal relationship;
* elapsed minutes after a meal;
* measurement unit.

Do not ask the user for information the application already knows.

## Locked User Flows

### Direct capture: glucose meter

1. The user opens the application.
2. The camera is immediately accessible.
3. The user photographs a glucose meter.
4. AI recognizes the device and displayed value.
5. The application proposes:

    * type: `fasting_glucose`;
    * unit: `mmol/L`;
    * current date and time.
6. The user confirms or edits the result.
7. The measurement is saved.

### Direct capture: body scale

1. The user photographs a body scale.
2. AI recognizes the displayed weight.
3. The application proposes:

    * type: `weight`;
    * unit: `kg`;
    * current date and time.
4. The user confirms or edits the result.
5. The measurement is saved.

### Direct capture: food

1. The user photographs food.
2. The photo means that a meal is starting now.
3. AI proposes a concise description of the food.
4. The application proposes the current date and time as the meal start.
5. The user may edit the description or time.
6. The user may enable reminders:

    * after 2 hours;
    * after 3 hours;
    * both;
    * none.
7. The meal and selected reminders are saved.

There is no separate “New meal” action required before photographing food.

### Manual post-meal measurement

1. The user selects “After meal”.
2. The camera opens expecting a glucose meter.
3. AI recognizes the glucose value.
4. The application searches for a relevant meal approximately 2 or 3 hours earlier.
5. The application proposes the closest suitable meal.
6. The actual elapsed minutes are calculated from the meal start to the measurement time.
7. The user confirms or selects another meal.
8. The measurement is saved as `post_meal_glucose`.

When no suitable meal exists, allow the user to:

* enter the meal time manually;
* enter a short meal description;
* select an older recorded meal;
* save the measurement without a linked meal.

### Reminder-driven post-meal measurement

1. A scheduled reminder triggers a push notification.
2. The user taps the notification.
3. The application opens directly on the camera screen.
4. The application already knows:

    * the meal;
    * the reminder;
    * whether it was scheduled for 2 or 3 hours;
    * the meal start time.
5. The camera waits for a glucose meter photo.
6. AI recognizes the value.
7. The actual elapsed minutes are calculated.
8. The user confirms the result.
9. The measurement is saved and the reminder is completed.

Do not ask the user to select the meal again in this flow.

## Technical Architecture

The production architecture is:

```text
Angular PWA
    ↓ HTTPS JSON API
PHP backend
    ↓ PDO prepared statements
MySQL / MariaDB
```

Additional integrations:

```text
PHP backend
    ↓
AI image analysis provider

PHP cron
    ↓
Web Push notifications
    ↓
Angular service worker
```

The browser must never connect directly to MySQL.

Node.js is used only for local development, tests, and frontend builds. Production hosting does not provide Node.js.

## Frontend Stack

Use:

* Angular;
* TypeScript;
* Angular standalone components;
* Angular Router;
* Angular HttpClient;
* Angular Signals;
* typed Reactive Forms;
* SCSS;
* Chart.js;
* official Angular service worker and PWA support.

Do not introduce React.

Do not introduce NgRx unless explicitly requested and justified by actual complexity.

Use the Angular version already configured in the repository. Do not upgrade Angular or other major dependencies as part of an unrelated task.

## Frontend Architecture

Expected high-level structure:

```text
frontend/src/app/
├── core/
│   ├── api/
│   ├── auth/
│   ├── camera/
│   ├── notifications/
│   └── interceptors/
├── features/
│   ├── capture/
│   ├── dashboard/
│   ├── history/
│   ├── meals/
│   ├── meal-analysis/
│   └── settings/
├── shared/
│   ├── components/
│   ├── models/
│   └── utils/
├── app.config.ts
└── app.routes.ts
```

Keep feature-specific code inside its feature directory.

Do not place business logic directly in components when it belongs in a service or a pure utility.

Prefer small components with clear responsibilities.

## TypeScript Rules

Always follow these rules:

* use `const` whenever reassignment is not required;
* never use `var`;
* always use semicolons;
* always use braces for `if`, `else`, loops, and similar blocks;
* write all code comments in English;
* avoid ambiguous shorthand syntax;
* avoid `any`;
* use `unknown` and validate before narrowing;
* prefer explicit interfaces and types for API boundaries;
* keep strict TypeScript settings enabled;
* do not disable lint rules without a documented reason.

Use descriptive names. Avoid abbreviations unless they are well established in the project.

## Angular Rules

* Use standalone components.
* Prefer Signals for application and component state.
* Use RxJS where it naturally integrates with Angular APIs or asynchronous streams.
* Do not convert everything to RxJS unnecessarily.
* Use typed Reactive Forms for editable forms.
* Keep templates readable.
* Do not place complex expressions in templates.
* Use route-level lazy loading for major features when appropriate.
* Use functional interceptors unless the existing project establishes another pattern.
* Unsubscribe safely from manually managed subscriptions.
* Prefer Angular lifecycle utilities such as `takeUntilDestroyed()` where appropriate.

## Camera Rules

Primary camera access:

* `navigator.mediaDevices.getUserMedia()`;
* rear-facing camera preference;
* video preview;
* canvas capture;
* client-side resize and compression before upload.

Fallback:

```html
<input
  type="file"
  accept="image/*"
  capture="environment"
/>
```

The application must handle:

* denied camera permission;
* unavailable camera;
* unsupported browser APIs;
* blurred or unreadable images;
* AI recognition failure;
* upload failure;
* retry and manual-entry fallback.

Do not assume camera access always succeeds.

## PWA and Notification Rules

Use the official Angular service worker.

Push notifications must support deep links into the correct application flow.

A reminder notification must contain enough context to open the post-meal capture route for a specific reminder.

Example conceptual route:

```text
/capture/post-meal?reminderId=123
```

Do not expose sensitive information in notification text.

Push subscriptions must be stored per user and device.

Invalid or expired push subscriptions must be removable.

Reminder delivery must be idempotent. A cron retry must not send the same reminder repeatedly.

## Backend Stack

Use:

* PHP 8.2 or newer;
* Composer;
* JSON HTTP endpoints;
* PHP sessions;
* PDO;
* prepared statements;
* centralized error handling;
* centralized request parsing;
* centralized validation;
* PHP cURL or an approved HTTP client for external APIs.

Do not introduce a PHP framework unless explicitly requested.

## PHP Rules

All PHP source files must begin with:

```php
<?php

declare(strict_types=1);
```

Follow these rules:

* use strict parameter and return types where practical;
* use `final` for classes that are not designed for inheritance;
* use PDO prepared statements for every query containing external data;
* never concatenate user input into SQL;
* never expose stack traces, SQL errors, credentials, or internal paths to the client;
* return structured JSON errors;
* use appropriate HTTP status codes;
* keep endpoint files small;
* place database access in repositories;
* place business logic in services;
* place validation in dedicated validators;
* verify ownership with `user_id` in every user-owned resource query.

Do not use PHP short tags.

## API Rules

All API responses must be JSON.

Successful response shape should remain consistent.

Example:

```json
{
  "data": {}
}
```

Error response shape:

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The submitted data is invalid.",
    "fields": {}
  }
}
```

Do not send raw exception messages to the client.

Write operations must require:

* an authenticated PHP session;
* a valid CSRF token;
* validated JSON input;
* resource ownership verification.

Use correct status codes, including:

* `200` for successful reads and updates;
* `201` for successful creation;
* `204` for successful deletion without a body;
* `400` for malformed requests;
* `401` for unauthenticated requests;
* `403` for invalid CSRF or forbidden access;
* `404` for unavailable resources;
* `409` for state conflicts;
* `422` for validation errors;
* `500` for unexpected server errors.

## Authentication and Security

Use:

* `password_hash()`;
* `password_verify()`;
* PHP sessions;
* secure session cookie settings;
* CSRF protection.

Session cookies must be:

* `HttpOnly`;
* `Secure` in production;
* configured with an appropriate `SameSite` policy.

Regenerate the session ID after successful login.

Do not place credentials, private keys, database passwords, or AI API keys in frontend code or committed files.

Production secrets should be stored outside `public_html` whenever hosting permits it.

Do not commit:

* `.env`;
* production configuration;
* API keys;
* database credentials;
* VAPID private keys;
* uploaded user images.

## AI Integration Rules

AI requests must be made only by the PHP backend.

The frontend must never receive the AI provider API key.

The AI response must use a strict structured format.

Supported image classifications:

```text
glucose_meter
scale
food
unknown
```

Expected data may include:

```text
kind
value
unit
food_description
detected_items
confidence
```

Backend validation is mandatory even when structured output is used.

Never trust:

* AI classification;
* recognized numeric values;
* units;
* food descriptions;
* confidence scores.

AI output is a proposal, not the final saved record.

The user must be able to:

* confirm;
* edit;
* retake the photo;
* enter the data manually.

Low-confidence results must be clearly indicated.

Do not allow AI output to execute SQL, choose file paths, or directly mutate stored data.

## Image Handling

Before accepting an upload:

* validate MIME type from the file contents;
* validate file size;
* reject unsupported formats;
* generate a random server-side filename;
* never trust the original filename;
* prevent uploaded files from being executed as PHP.

Images from glucose meters and scales should normally be deleted after successful recognition and confirmation unless retention is explicitly enabled.

Food photos may be retained for history and analysis.

Stored images must be outside the public web root where possible. Access should go through an authenticated PHP endpoint.

Do not log raw image data.

## Database

Use:

* MySQL or MariaDB;
* InnoDB;
* `utf8mb4`;
* foreign keys;
* indexed user and timestamp columns.

Store timestamps in UTC.

Display and edit times using the user timezone. The initial expected timezone is:

```text
Europe/Sofia
```

Primary units:

* glucose: `mmol/L`;
* weight: `kg`;
* waist: `cm`.

Expected core tables:

```text
users
meals
measurements
reminders
push_subscriptions
```

Additional tables may be added only when justified.

## Database Migration Rules

* Never edit an already applied migration.
* Create a new numbered migration for every schema change.
* Keep migrations deterministic.
* Do not add destructive migrations without explicit approval.
* Add indexes based on actual query patterns.
* Define foreign-key behavior explicitly.
* Do not use SQL `ENUM` for application-controlled values unless specifically approved.
* Keep application validation aligned with database constraints.

## Data Relationships

A meal is a first-class entity.

A post-meal glucose measurement may reference a meal through `meal_id`.

The application should calculate elapsed time from:

```text
measurement.measured_at - meal.started_at
```

Do not rely only on a label such as “2 hours”. Store the actual measurement timestamp.

A reminder should reference its meal and intended offset.

Reminder status must support at least:

```text
pending
sent
completed
dismissed
failed
```

## Statistics

Dashboard calculations must have explicit definitions.

Initial definitions:

* current weight: latest measurement containing a weight value;
* starting weight: configured starting weight, otherwise earliest available weight;
* latest fasting glucose: latest `fasting_glucose` measurement;
* 7-day and 30-day fasting averages: based on recorded fasting glucose measurements in the selected interval;
* weight trend: use daily values and a clearly documented moving-average calculation;
* post-meal response: compare linked measurements with the relevant meal and available pre-meal baseline.

Do not silently change calculation definitions.

Avoid presenting correlation as proven causation.

## Testing

Every implementation task should include appropriate tests.

Frontend tests should cover:

* services;
* state transitions;
* validation;
* critical user flows;
* error and fallback behavior.

Backend tests should cover:

* validation;
* authentication requirements;
* CSRF behavior;
* authorization and ownership;
* repository behavior;
* reminder state transitions;
* AI response validation.

Do not create tests that only reproduce implementation details without protecting behavior.

## Accessibility

All interactive elements must:

* have accessible names;
* be keyboard reachable where applicable;
* have visible focus states;
* use sufficient contrast;
* avoid relying only on color;
* support screen-reader labels.

Camera screens must provide text instructions and non-camera fallbacks.

## Performance

Optimize for mobile networks.

* Resize images before upload.
* Avoid uploading original high-resolution photos unnecessarily.
* Lazy-load major routes.
* Keep the initial bundle small.
* Avoid large UI libraries without justification.
* Paginate long histories.
* Do not load full-resolution food images in lists.
* Avoid duplicate API requests.

## Repository Structure

Expected top-level structure:

```text
glycoflow/
├── frontend/
├── backend/
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── imports/
├── cron/
├── docs/
├── .github/
├── CLAUDE.md
├── README.md
├── .editorconfig
├── .gitignore
└── .env.example
```

Do not move top-level directories without explicit approval.

## Working Rules for Claude Code

Before changing code:

1. Read the request completely.
2. Inspect the relevant existing files.
3. Read related documentation.
4. Identify the smallest safe implementation.
5. State assumptions when the requirement is ambiguous.

During implementation:

* change only files required for the task;
* keep the diff focused;
* preserve existing architectural patterns;
* do not rewrite working code without a reason;
* do not add unrelated features;
* do not add dependencies without explicit approval or a clear documented necessity;
* do not upgrade dependencies as part of unrelated work;
* do not change formatting across unrelated files;
* do not remove tests to make a build pass;
* do not weaken TypeScript, lint, validation, authentication, or security settings;
* do not use destructive Git commands;
* do not commit secrets.

After implementation:

1. Run the relevant formatter.
2. Run lint.
3. Run tests.
4. Run the production build.
5. Report what changed.
6. Report the commands executed and their results.
7. Report any known limitation or unfinished part.

When a command cannot be run, say so explicitly.

## Scope Control

Do not implement the entire application in one task.

Work incrementally.

Good task boundaries include:

* repository bootstrap;
* one database migration;
* one API endpoint;
* one camera component;
* one AI response validator;
* one reminder state transition;
* one dashboard statistic;
* one import step.

Do not continue into the next task without being asked.

## Definition of Done

A task is complete only when:

* the requested behavior is implemented;
* the implementation follows this document;
* relevant tests exist and pass;
* lint passes;
* the affected application builds;
* no secrets are committed;
* no unrelated files are changed;
* documentation is updated when behavior or architecture changes;
* limitations are reported clearly.

## Prohibited Decisions Without Approval

Do not:

* replace Angular;
* introduce React;
* introduce NgRx;
* introduce a PHP framework;
* change the database engine;
* change authentication away from PHP sessions;
* expose MySQL to the browser;
* store AI credentials in the frontend;
* remove user confirmation after AI recognition;
* turn the application into a medical diagnostic tool;
* add analytics, advertising, or third-party tracking;
* add cloud infrastructure that conflicts with shared-hosting deployment;
* change core user flows defined in this document.
