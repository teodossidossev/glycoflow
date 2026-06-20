# GlycoFlow Implementation Plan

## Principle

Implement in small, reviewable increments.

One GitHub issue should normally produce:

- one branch;
- one focused diff;
- tests;
- one pull request.

Do not build the whole application in one task.

## Phase 0: Repository Bootstrap

1. Add documentation.
2. Add `.editorconfig`.
3. Add `.gitignore`.
4. Add `.env.example`.
5. Add root README.
6. Add CI skeleton.
7. Confirm branch and PR rules.

## Phase 1: Angular Bootstrap

1. Create Angular project under `frontend/`.
2. Use standalone components.
3. Enable strict TypeScript.
4. Add Router.
5. Add SCSS.
6. Add PWA support.
7. Add lint and tests.
8. Create mobile shell.
9. Create placeholder routes.

Deliverable: static Angular build works.

## Phase 2: PHP Bootstrap

1. Create `backend/`.
2. Add Composer.
3. Add bootstrap.
4. Add configuration loader.
5. Add PDO factory.
6. Add JSON request parser.
7. Add JSON response helper.
8. Add centralized error handler.
9. Add API exception types.
10. Add health/smoke endpoint for development if needed.

Deliverable: PHP JSON endpoint works without exposing internal errors.

## Phase 3: Database

1. Create migrations.
2. Add users.
3. Add meals.
4. Add reminders.
5. Add measurements.
6. Add push subscriptions.
7. Add image analyses.
8. Add push deliveries.
9. Add migration runner or documented manual process.
10. Add development seed.

Deliverable: clean schema can be created from zero.

## Phase 4: Authentication

1. User repository.
2. Auth service.
3. Session configuration.
4. CSRF service.
5. Login endpoint.
6. Logout endpoint.
7. Session endpoint.
8. Angular auth service.
9. Route guard.
10. HTTP interceptor.
11. Login screen.
12. Tests.

Deliverable: protected app shell.

## Phase 5: Camera Foundation

1. Camera service.
2. Permission handling.
3. Rear-camera preference.
4. Video preview.
5. Capture to canvas.
6. File input fallback.
7. Client resize/compression.
8. Capture state store.
9. Error states.
10. Tests.

Deliverable: image can be captured or selected on a phone.

## Phase 6: Mock Image Analysis

1. Define normalized analysis contract.
2. Add mock provider.
3. Add upload validator.
4. Add temporary storage.
5. Add `analyze-image.php`.
6. Add Angular analysis service.
7. Route to glucose/weight/meal confirmation.
8. Add unknown flow.
9. Tests.

Deliverable: camera UX works without a real AI provider.

## Phase 7: Measurements

1. Measurement validator.
2. Measurement repository.
3. Measurement service.
4. GET/POST/PUT/DELETE endpoint.
5. Glucose confirmation.
6. Weight confirmation.
7. Manual fallback.
8. Idempotency support.
9. Tests.

Deliverable: fasting glucose and weight can be saved.

## Phase 8: Meals

1. Meal validator.
2. Meal repository.
3. Meal service.
4. Food image finalization.
5. Meal endpoint.
6. Meal confirmation UI.
7. Reminder option UI.
8. Tests.

Deliverable: food photo creates a meal.

## Phase 9: Reminder Records

1. Reminder repository.
2. Reminder service.
3. Creation with meal transaction.
4. Reminder endpoint.
5. Pending reminder UI.
6. State validation.
7. Tests.

Deliverable: 2-hour and 3-hour reminders exist in the database.

## Phase 10: Post-Meal Manual Flow

1. Candidate meal query.
2. Ranking service.
3. “After meal” route.
4. Camera in glucose-only context.
5. Candidate confirmation.
6. Multiple candidates.
7. No-meal fallback.
8. Tests.

Deliverable: manual post-meal capture works.

## Phase 11: PWA Push

1. Generate VAPID keys.
2. Add push subscription service.
3. Add subscription endpoint.
4. Add service worker notification handling.
5. Add permission UX.
6. Add deep-link route.
7. Add mock push sender.
8. Tests.

Deliverable: device can register and receive test push.

## Phase 12: Reminder Cron

1. Due reminder query.
2. Atomic claim.
3. Push sender adapter.
4. Delivery records.
5. Retry policy.
6. Expired subscription handling.
7. Cron entry script.
8. Tests for overlapping runs.

Deliverable: due reminders generate push notifications.

## Phase 13: Reminder Completion

1. Load reminder context.
2. Open camera directly.
3. Analyze glucose.
4. Confirm actual elapsed time.
5. Save measurement and complete reminder transactionally.
6. Handle already completed reminder.
7. Tests.

Deliverable: notification-to-camera flow is complete.

## Phase 14: History

1. Unified history query.
2. Pagination.
3. Filters.
4. Search.
5. Mobile cards.
6. Detail screens.
7. Edit.
8. Delete.
9. Tests.

Deliverable: records can be reviewed and corrected.

## Phase 15: Dashboard

1. Stats service.
2. Current and starting weight.
3. Weight change.
4. Latest fasting glucose.
5. 7-day and 30-day averages.
6. Threshold count.
7. Chart data.
8. Period filter.
9. Empty states.
10. Tests.

Deliverable: core trends are visible.

## Phase 16: Meal Analysis

1. Linked measurement query.
2. Pre-meal selection.
3. Closest 2-hour and 3-hour selection.
4. Actual elapsed time.
5. Absolute change.
6. Cards and detail view.
7. Tests.

Deliverable: meal response comparison works.

## Phase 17: Real AI Provider

1. Provider adapter.
2. Server-side configuration.
3. Strict structured response.
4. Timeout.
5. Retry policy.
6. Normalization.
7. Low-confidence handling.
8. Cost and latency logging.
9. Integration tests outside normal CI.
10. Replace mock by configuration.

Deliverable: real image recognition works.

## Phase 18: Production Deployment

1. Build frontend.
2. Prepare backend vendor.
3. Create production database.
4. Apply migrations.
5. Create initial user.
6. Configure secrets.
7. Configure storage.
8. Configure rewrites.
9. Configure cron.
10. Run smoke tests.
11. Verify backups.

## Phase 19: Historical Import

1. Inspect source spreadsheet.
2. Define mapping.
3. Create import batch model.
4. CSV parser.
5. Preview.
6. validation.
7. duplicate detection.
8. transaction.
9. report.
10. production import.

## Issue Template

Each issue should contain:

- goal;
- scope;
- out of scope;
- acceptance criteria;
- relevant docs;
- test requirements;
- manual verification steps.

## Claude Code Prompt Pattern

Example:

```text
Implement only the camera preview and capture state described in docs/user-flows.md.

Scope:
- camera permission;
- rear-camera preference;
- video preview;
- capture to canvas;
- file input fallback;
- tests.

Out of scope:
- image upload;
- AI analysis;
- backend changes;
- confirmation screens.

Do not add unrelated dependencies.
Run lint, tests, and production build.
Report changed files and command results.
```
