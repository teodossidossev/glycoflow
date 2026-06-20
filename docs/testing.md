# GlycoFlow Testing Strategy

## 1. Goals

Tests should protect behavior, not implementation trivia.

Critical areas:

- authentication;
- ownership;
- camera state transitions;
- AI normalization;
- reminder idempotency;
- data validation;
- dashboard calculations;
- deep links;
- fallback behavior.

## 2. Frontend Testing

Use the test runner configured by the Angular project.

Cover:

- services;
- Signals state;
- guards;
- interceptors;
- forms;
- pure utilities;
- critical components;
- route handling.

### Camera flow

Test:

- permission granted;
- permission denied;
- camera unavailable;
- capture;
- upload;
- analyzing;
- glucose proposal;
- weight proposal;
- food proposal;
- unknown;
- low confidence;
- retry;
- manual fallback.

### Post-meal flow

Test:

- candidate meal;
- multiple meals;
- no meal;
- reminder context;
- actual elapsed time display;
- already-completed reminder.

### Session flow

Test:

- initial session check;
- login;
- logout;
- expired session;
- resume intended route.

### Dashboard

Test:

- normal data;
- missing values;
- period changes;
- loading state;
- error state.

## 3. Backend Testing

Use PHPUnit or the selected backend test framework.

Cover:

- validators;
- services;
- repositories where practical;
- endpoint integration;
- transactions.

### Authentication

- valid login;
- invalid login;
- session regeneration;
- logout;
- session response.

### CSRF

- missing token;
- invalid token;
- valid token;
- safe methods.

### Ownership

- own resource;
- foreign resource;
- missing resource;
- no existence leak.

### Measurements

- valid fasting;
- valid weight;
- valid post-meal;
- missing required value;
- invalid type/value combination;
- invalid meal;
- duplicate reminder completion.

### Meals

- meal without reminders;
- meal with 2-hour reminder;
- meal with both reminders;
- duplicate offset;
- edited start time;
- deletion behavior.

### AI

- valid glucose;
- valid scale;
- valid food;
- unknown;
- low confidence;
- invalid schema;
- timeout;
- provider error.

### Uploads

- valid JPEG/PNG/WebP;
- spoofed MIME;
- oversized file;
- invalid image;
- unsafe filename.

### Push and reminders

- due query;
- claim;
- successful send;
- retry;
- terminal failure;
- expired subscription;
- duplicate cron execution;
- completion transaction.

### Statistics

- current weight;
- starting weight;
- weight change;
- 7-day average;
- 30-day average;
- threshold count;
- moving averages;
- meal analysis.

## 4. Integration Tests

Critical end-to-end backend scenarios:

1. login;
2. analyze mock image;
3. confirm fasting measurement;
4. create meal with reminders;
5. complete reminder with measurement;
6. verify history and stats.

## 5. Browser / E2E Tests

Add after core flows stabilize.

Suggested coverage:

- login;
- direct glucose capture with mocked camera/provider;
- weight capture;
- meal capture;
- manual post-meal;
- reminder deep link;
- history edit;
- dashboard refresh.

Do not depend on real AI in normal CI.

## 6. Mocks

Use:

- mock image-analysis provider;
- mock push sender;
- fixed clock;
- isolated test database;
- fake storage adapter.

Provider-specific integration tests may run separately.

## 7. Time Testing

Inject or abstract the clock where practical.

Test:

- UTC conversion;
- Europe/Sofia display;
- daylight-saving transitions;
- meal elapsed minutes;
- reminder schedule.

## 8. Test Data

Use synthetic data.

Do not commit real personal health records.

## 9. Quality Gates

Pull requests should pass:

### Frontend

```text
npm ci
lint
tests
production build
```

### Backend

```text
composer install
syntax check
tests
```

### Repository

- secret scan;
- no generated secrets;
- migrations ordered;
- docs updated when behavior changes.

## 10. Definition of Done

A feature is not complete until:

- behavior tests exist;
- critical failure paths are covered;
- lint passes;
- build passes;
- no unrelated tests are weakened;
- limitations are documented.
