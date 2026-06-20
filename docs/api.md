# GlycoFlow API Specification

## 1. Base Path

```text
/api/
```

Initial endpoints:

```text
/api/login.php
/api/logout.php
/api/session.php
/api/analyze-image.php
/api/measurements.php
/api/meals.php
/api/reminders.php
/api/push-subscriptions.php
/api/history.php
/api/stats.php
/api/meal-analysis.php
/api/images.php
```

## 2. General Rules

- HTTPS in production;
- JSON except authenticated image streaming;
- UTF-8;
- authentication unless documented otherwise;
- CSRF for mutations;
- centralized validation and errors;
- ownership checks;
- no internal error leakage.

## 3. Headers

JSON:

```text
Content-Type: application/json
Accept: application/json
```

Image upload:

```text
Content-Type: multipart/form-data
Accept: application/json
```

CSRF:

```text
X-CSRF-Token: <token>
```

## 4. Response Shapes

Success:

```json
{
  "data": {}
}
```

Collection:

```json
{
  "data": [],
  "meta": {
    "page": 1,
    "pageSize": 50,
    "total": 0,
    "hasMore": false
  }
}
```

Error:

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The submitted data is invalid.",
    "fields": {}
  }
}
```

## 5. Stable Error Codes

```text
AUTH_REQUIRED
AUTH_INVALID_CREDENTIALS
AUTH_SESSION_EXPIRED
CSRF_INVALID
METHOD_NOT_ALLOWED
UNSUPPORTED_MEDIA_TYPE
MALFORMED_JSON
VALIDATION_ERROR
RESOURCE_NOT_FOUND
RESOURCE_CONFLICT
RATE_LIMITED
CAMERA_IMAGE_REQUIRED
IMAGE_UPLOAD_FAILED
IMAGE_TOO_LARGE
IMAGE_TYPE_UNSUPPORTED
IMAGE_INVALID
AI_TIMEOUT
AI_PROVIDER_ERROR
AI_INVALID_RESPONSE
AI_LOW_CONFIDENCE
REMINDER_ALREADY_COMPLETED
REMINDER_NOT_AVAILABLE
PUSH_SUBSCRIPTION_INVALID
INTERNAL_ERROR
```

## 6. Time and Numbers

Use ISO 8601 timestamps.

Use JSON numbers, not localized numeric strings.

Primary units:

```text
glucose: mmol/L
weight: kg
waist: cm
```

## 7. Authentication

### POST `/api/login.php`

Request:

```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

Response:

```json
{
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "timezone": "Europe/Sofia",
      "startingWeightKg": 127
    },
    "csrfToken": "generated-token"
  }
}
```

### POST `/api/logout.php`

Authenticated and CSRF protected.

Success: `204 No Content`.

### GET `/api/session.php`

Authenticated:

```json
{
  "data": {
    "authenticated": true,
    "user": {},
    "csrfToken": "generated-token"
  }
}
```

Unauthenticated:

```json
{
  "data": {
    "authenticated": false,
    "user": null,
    "csrfToken": null
  }
}
```

## 8. Image Analysis

### POST `/api/analyze-image.php`

Multipart fields:

```text
image
captureContext
reminderId
clientCapturedAt
```

Capture contexts:

```text
direct
post_meal
reminder
```

Direct may classify:

```text
glucose_meter
scale
food
unknown
```

Reminder context requires a valid owned reminder.

Example glucose response:

```json
{
  "data": {
    "analysisId": 101,
    "kind": "glucose_meter",
    "confidence": 0.96,
    "recognized": {
      "value": 5.8,
      "unit": "mmol_l"
    },
    "proposal": {
      "measurementType": "fasting_glucose",
      "measuredAt": "2026-06-20T04:50:00Z",
      "glucoseMmolL": 5.8
    },
    "warnings": []
  }
}
```

Example food response:

```json
{
  "data": {
    "analysisId": 103,
    "kind": "food",
    "confidence": 0.89,
    "recognized": {
      "description": "Chicken with rice and green salad",
      "detectedItems": [
        "chicken",
        "rice",
        "green salad"
      ]
    },
    "proposal": {
      "startedAt": "2026-06-20T10:20:00Z",
      "description": "Chicken with rice and green salad"
    },
    "temporaryImageToken": "temporary-token",
    "warnings": []
  }
}
```

Analysis does not create a permanent record.

## 9. Measurements

### GET `/api/measurements.php`

Query parameters:

```text
id
from
to
type
mealId
page
pageSize
sort
direction
```

### POST `/api/measurements.php`

Example fasting request:

```json
{
  "measurementType": "fasting_glucose",
  "measuredAt": "2026-06-20T07:50:00+03:00",
  "glucoseMmolL": 5.8,
  "mealId": null,
  "reminderId": null,
  "sourceType": "camera_ai",
  "analysisId": 101,
  "notes": null,
  "clientRequestId": "uuid"
}
```

Example reminder-driven request:

```json
{
  "measurementType": "post_meal_glucose",
  "measuredAt": "2026-06-20T15:28:00+03:00",
  "glucoseMmolL": 6.7,
  "mealId": 55,
  "reminderId": 77,
  "sourceType": "camera_ai",
  "analysisId": 111,
  "notes": null,
  "clientRequestId": "uuid"
}
```

When `reminderId` is present, one transaction must:

1. validate reminder;
2. validate meal;
3. create measurement;
4. complete reminder;
5. commit.

### PUT `/api/measurements.php?id=...`

Updates documented editable fields.

### DELETE `/api/measurements.php?id=...`

Deletes owned measurement only.

## 10. Meals

### GET `/api/meals.php`

Query parameters:

```text
id
from
to
search
hasMeasurements
page
pageSize
sort
direction
matchFor
```

`matchFor` returns candidate meals for a post-meal timestamp.

### POST `/api/meals.php`

Example:

```json
{
  "startedAt": "2026-06-20T13:20:00+03:00",
  "description": "Chicken with rice and green salad",
  "aiDescription": "Chicken with rice and green salad",
  "temporaryImageToken": "temporary-token",
  "sourceType": "camera_ai",
  "analysisId": 103,
  "notes": null,
  "reminderOffsetsMinutes": [
    120,
    180
  ],
  "clientRequestId": "uuid"
}
```

Transaction:

1. create meal;
2. create reminders;
3. finalize image;
4. commit.

### PUT `/api/meals.php?id=...`

May update:

- start time;
- description;
- notes;
- pending reminder times when explicitly requested.

### DELETE `/api/meals.php?id=...`

Must:

- remove owned meal;
- cancel/delete reminders;
- clear linked measurement `meal_id`;
- preserve measurements;
- remove images.

## 11. Meal Matching

Example:

```text
GET /api/meals.php?matchFor=2026-06-20T15:25:00+03:00
```

Response items include:

- meal;
- elapsed minutes;
- closest target;
- distance;
- recommended flag.

Only owned meals before the timestamp are returned.

## 12. Reminders

### GET `/api/reminders.php`

Filters:

```text
id
status
mealId
from
to
page
pageSize
```

### POST `/api/reminders.php`

Request:

```json
{
  "mealId": 55,
  "targetOffsetMinutes": 180
}
```

### PATCH `/api/reminders.php?id=...`

Actions:

```text
dismiss
cancel
```

Invalid transitions return `409`.

## 13. Push Subscriptions

### POST `/api/push-subscriptions.php`

Registers or updates a subscription.

### GET `/api/push-subscriptions.php`

Returns safe device metadata only.

Do not return endpoint keys or auth secrets.

### DELETE `/api/push-subscriptions.php?id=...`

Removes one device subscription.

## 14. History

### GET `/api/history.php`

Query:

```text
from
to
types
search
page
pageSize
direction
```

Returns unified reverse chronological meal and measurement records.

## 15. Statistics

### GET `/api/stats.php`

Query:

```text
period
from
to
```

Periods:

```text
7d
30d
90d
all
custom
```

Returns:

- weight summary;
- fasting summary;
- pending reminders;
- chart series.

Missing values use `null`, not misleading zeroes.

## 16. Meal Analysis

### GET `/api/meal-analysis.php`

Query:

```text
mealId
from
to
search
page
pageSize
```

Returns:

- meal;
- pre-meal value;
- closest 2-hour value;
- closest 3-hour value;
- actual elapsed minutes;
- change from baseline.

## 17. Images

### GET `/api/images.php`

Query:

```text
mealId
type
```

Types:

```text
thumbnail
display
```

Verify ownership and resolve physical paths server-side.

## 18. Idempotency

Create requests support:

```text
clientRequestId
```

Repeated identical requests should return the existing result where practical.

Conflicting reuse returns `409`.

## 19. Pagination

Defaults:

```text
page = 1
pageSize = 50
```

Page size is bounded.

## 20. Sorting

Whitelist sortable fields.

Never place arbitrary client values into SQL fragments.

## 21. Search

Meal search:

- trimmed;
- bounded length;
- prepared statements;
- escaped wildcard behavior;
- ownership filtered.

## 22. Rate Limiting

Especially for:

- login;
- image analysis;
- push registration.

Return `429` with `RATE_LIMITED`.

## 23. Logging

May log:

- request ID;
- endpoint;
- method;
- user ID;
- status;
- duration;
- safe error code.

Must not log:

- passwords;
- cookies;
- CSRF;
- API keys;
- push secrets;
- raw images;
- complete health payloads.

## 24. Initial Endpoint Order

```text
1. session.php
2. login.php
3. logout.php
4. analyze-image.php with mock provider
5. measurements.php
6. meals.php
7. reminders.php
8. push-subscriptions.php
9. reminder cron
10. history.php
11. stats.php
12. meal-analysis.php
13. images.php
```
