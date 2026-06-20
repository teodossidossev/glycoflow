# GlycoFlow Data Model

## 1. General Rules

Use:

- MySQL or MariaDB;
- InnoDB;
- `utf8mb4`;
- foreign keys;
- UTC timestamps;
- unsigned integer IDs;
- PDO prepared statements.

All user-owned rows include `user_id`.

Application-controlled values use `VARCHAR`, not SQL `ENUM`.

## 2. Initial Tables

```text
users
meals
measurements
reminders
push_subscriptions
image_analyses
push_deliveries
```

## 3. Relationships

```text
users 1 ─── N meals
users 1 ─── N measurements
users 1 ─── N reminders
users 1 ─── N push_subscriptions

meals 1 ─── N measurements
meals 1 ─── N reminders

reminders 1 ─── 0..1 measurements
reminders 1 ─── N push_deliveries
```

A measurement may exist without a meal.

A meal may exist without measurements.

## 4. Users

Fields:

```text
id
email
password_hash
timezone
starting_weight_kg
created_at
updated_at
```

Rules:

- email normalized to lowercase;
- password stored only as `password_hash()`;
- timezone is a valid IANA value;
- default timezone is `Europe/Sofia`;
- starting weight may differ from earliest imported weight.

## 5. Meals

Fields:

```text
id
user_id
started_at
description
ai_description
photo_path
photo_thumbnail_path
notes
source_type
created_at
updated_at
```

Allowed source values:

```text
camera_ai
manual
import
```

Rules:

- `started_at` in UTC;
- `description` is user-confirmed;
- `ai_description` preserves original proposal;
- physical paths are private;
- meal may exist without reminders or measurements.

## 6. Measurements

Fields:

```text
id
user_id
meal_id
reminder_id
measured_at
measurement_type
glucose_mmol_l
weight_kg
waist_cm
source_type
ai_confidence
notes
client_request_id
created_at
updated_at
```

Types:

```text
fasting_glucose
pre_meal_glucose
post_meal_glucose
random_glucose
weight
waist
```

Sources:

```text
camera_ai
manual
import
```

Rules:

- glucose types require `glucose_mmol_l`;
- weight requires `weight_kg`;
- waist requires `waist_cm`;
- linked meal belongs to the same user;
- meal starts before measurement;
- reminder-driven measurements use `post_meal_glucose`;
- reminder relationship is unique;
- elapsed time is derived from timestamps.

## 7. Reminders

Fields:

```text
id
user_id
meal_id
reminder_type
target_offset_minutes
scheduled_at
status
attempt_count
last_attempt_at
sent_at
completed_at
dismissed_at
failed_at
failure_code
processing_token
processing_started_at
created_at
updated_at
```

Types:

```text
post_meal_2h
post_meal_3h
custom
```

Statuses:

```text
pending
processing
sent
completed
dismissed
failed
cancelled
```

Rules:

- one reminder per meal and offset;
- 2-hour offset = 120;
- 3-hour offset = 180;
- scheduled time is derived from meal start;
- actual measurement time remains separate;
- worker claiming uses processing state/token.

## 8. Push Subscriptions

Fields:

```text
id
user_id
endpoint
endpoint_hash
public_key
auth_token
content_encoding
device_label
user_agent
is_active
last_success_at
last_failure_at
failure_count
created_at
updated_at
```

Rules:

- endpoint hash is unique;
- secrets are never logged;
- multiple devices per user;
- expired endpoints are disabled or deleted.

## 9. Image Analyses

Fields:

```text
id
user_id
analysis_type
provider
model
classification
confidence
recognized_value
recognized_unit
food_description
result_json
status
request_id
duration_ms
created_at
```

Analysis types:

```text
direct_capture
post_meal_capture
reminder_capture
category_retry
```

Statuses:

```text
success
low_confidence
unknown
invalid_response
provider_error
timeout
```

Rules:

- no raw image bytes;
- no secret headers;
- normalized data only;
- retention may later be limited.

## 10. Push Deliveries

Fields:

```text
id
reminder_id
push_subscription_id
status
provider_status_code
failure_code
attempted_at
created_at
```

Statuses:

```text
sent
failed
expired_subscription
skipped
```

## 11. Time

Store authoritative timestamps in UTC.

Frontend sends ISO 8601 with offset or UTC.

Display uses configured user timezone.

## 12. Numeric Precision

Recommended:

```text
glucose_mmol_l DECIMAL(5, 2)
weight_kg      DECIMAL(6, 2)
waist_cm       DECIMAL(6, 2)
ai_confidence  DECIMAL(5, 4)
```

Do not use floating-point columns.

## 13. Plausibility

Backend validation applies conservative ranges.

Do not silently clamp values.

Out-of-range values require correction or explicit confirmation depending on policy.

## 14. Ownership

Every resource query includes `user_id`.

A resource ID alone is never authorization.

## 15. Meal Matching

Query recent meals before the measurement.

Application logic ranks by distance to:

```text
120 minutes
180 minutes
```

Do not embed business ranking entirely in SQL.

## 16. Dashboard Indexes

Support:

- latest weight;
- latest fasting glucose;
- trend queries;
- linked-meal queries;
- due reminders.

## 17. Deletion

Measurement:

- delete row only.

Meal:

- remove/cancel reminders;
- set linked measurement `meal_id` to null;
- remove image files.

Push subscription:

- remove one device.

## 18. Import

Future imports should use `source_type = import`.

Possible later metadata:

```text
import_batches
import_row_number
import_source
imported_at
```

## 19. Duplicate Prevention

Use:

- disabled save button;
- `client_request_id`;
- unique reminder relationship;
- import duplicate detection.

## 20. Migration Policy

Recommended initial order:

```text
001_create_users.sql
002_create_meals.sql
003_create_reminders.sql
004_create_measurements.sql
005_create_push_subscriptions.sql
006_create_image_analyses.sql
007_create_push_deliveries.sql
```

Never edit an applied migration.

## 21. Initial SQL Shape

The final SQL migration must preserve the rules in this document. Exact DDL may be adjusted for the actual MySQL/MariaDB version available on hosting.
