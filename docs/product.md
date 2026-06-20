# GlycoFlow Product Specification

## 1. Product Summary

GlycoFlow is a private, mobile-first application for tracking:

- blood glucose;
- body weight;
- meals;
- post-meal glucose response;
- reminders;
- long-term trends.

The application is camera-first. The user should normally record data by taking a photo instead of filling in a form.

Primary pattern:

```text
Open app
→ take photo
→ AI recognizes context and value
→ user confirms or corrects
→ record is saved
```

Manual entry remains available as fallback.

## 2. Product Goal

The application replaces manual Excel or Google Sheets tracking.

It should reduce the effort required to:

- record glucose;
- record weight;
- describe meals;
- remember 2-hour and 3-hour measurements;
- calculate elapsed time after a meal;
- calculate trends;
- compare glucose response between meals.

The goal is not to reproduce a spreadsheet on a phone.

## 3. Target User

The initial product is for one private user.

The database and API still use user ownership so multi-user support can be added later.

Primary device: smartphone.

Desktop is secondary and mainly useful for:

- history review;
- larger charts;
- imports;
- troubleshooting.

## 4. Product Principles

### Camera-first

The user should not choose a record type before each direct capture.

The application infers the type from the photographed object.

### Mobile-first

Primary flows must be easy on a phone:

- large touch targets;
- short flows;
- minimal typing;
- clear confirmation;
- no horizontal scrolling;
- no dense tables on mobile.

### Context-aware

The application should use what it already knows:

- direct glucose-meter photo means fasting glucose;
- direct food photo means meal starts now;
- reminder deep link identifies the meal;
- elapsed time is calculated;
- current date and time are prefilled.

### User confirmation

AI output must be confirmed before persistence.

### Fast normal path

A successful direct record should normally require:

1. open;
2. photograph;
3. confirm.

### Private by default

No advertising, public profiles, social sharing, or third-party analytics by default.

## 5. Core Objects

### User

Owns all data and settings.

### Measurement

Initial types:

```text
fasting_glucose
pre_meal_glucose
post_meal_glucose
random_glucose
weight
waist
```

### Meal

Contains:

- start time;
- description;
- optional food photo;
- optional notes;
- optional reminders.

### Reminder

Scheduled post-meal measurement request.

Initial targets:

- 2 hours;
- 3 hours.

### Push subscription

One registered browser installation.

## 6. Primary Navigation

Suggested navigation:

- Home;
- History;
- Analysis;
- Settings.

The camera action must be prominent.

There is no required “New meal” action.

## 7. Direct Capture

The user opens the main camera and photographs:

- glucose meter;
- body scale;
- food;
- unknown object.

Expected classifications:

```text
glucose_meter
scale
food
unknown
```

### Direct glucose-meter capture

Creates a proposal:

- type: `fasting_glucose`;
- recognized value;
- unit: `mmol/L`;
- current date and time.

The direct-capture rule applies regardless of the hour.

### Direct scale capture

Creates a proposal:

- type: `weight`;
- recognized value;
- unit: `kg`;
- current date and time.

### Direct food capture

Means the meal starts now.

Creates a proposal:

- meal description;
- current date and time;
- reminder options:
  - 2 hours;
  - 3 hours;
  - both;
  - none.

### Unknown result

Offer:

- retake;
- choose Glucose meter;
- choose Scale;
- choose Food;
- manual entry;
- cancel.

## 8. Post-Meal Measurement

### Manual entry point

User selects “After meal”.

The camera opens expecting a glucose meter.

After recognition, the application finds likely meals from approximately 2 or 3 hours earlier and proposes the closest candidate.

The actual elapsed minutes are stored or derived from timestamps.

When no meal is found, allow:

- manual meal time;
- short description;
- older meal selection;
- save without a linked meal.

### Reminder entry point

When a reminder is due:

1. phone shows a push notification;
2. user taps it;
3. application opens directly on the camera;
4. reminder and meal are already known;
5. user photographs the glucose meter;
6. AI reads the value;
7. application calculates actual elapsed time;
8. user confirms;
9. measurement is saved and reminder completed.

Do not ask the user to choose the meal again.

## 9. Confirmation Screens

### Glucose

Show:

- value;
- unit;
- type;
- date;
- time;
- linked meal and elapsed time where relevant.

Actions:

- Save;
- Edit;
- Retake;
- Cancel.

### Weight

Show:

- value;
- unit;
- date;
- time.

### Meal

Show:

- photo preview;
- description;
- start date and time;
- reminder options.

## 10. Manual Entry

Manual entry is fallback.

Support:

- fasting glucose;
- post-meal glucose;
- random glucose;
- weight;
- waist;
- meal;
- date and time edits.

Keep forms contextual and short.

## 11. Home Dashboard

Initial values:

- current weight;
- starting weight;
- total weight change;
- latest fasting glucose;
- 7-day fasting average;
- 30-day fasting average;
- 7-day weight average;
- count of fasting values below 6.0;
- pending reminders;
- recent activity.

Initial charts:

- weight trend;
- fasting glucose trend.

Period options:

- 7 days;
- 30 days;
- 90 days;
- all time;
- custom.

## 12. History

Mobile presentation: cards or timeline.

Each record should show:

- date;
- time;
- type;
- value;
- unit;
- meal description where relevant;
- elapsed time where relevant.

Support:

- edit;
- delete;
- date filter;
- type filter;
- meal-description search.

## 13. Meal Analysis

For each meal show when available:

- description;
- photo;
- start time;
- pre-meal glucose;
- closest 2-hour measurement;
- closest 3-hour measurement;
- actual elapsed minutes;
- absolute change.

Never invent a missing baseline.

Avoid causal claims.

## 14. Starting Weight

The application supports a configured starting weight.

Fallback: earliest recorded weight.

## 15. Fasting and Eating Windows

Part of future scope, not required for the first camera-first MVP.

Possible future behavior:

- first meal opens eating window;
- explicit action closes it;
- fasting duration is calculated;
- user can correct times.

## 16. Import

Future CSV/Excel import should support:

- preview;
- column mapping;
- validation;
- duplicate detection;
- error report;
- safe rollback.

## 17. Photo Retention

Glucose-meter and scale photos:

- temporary;
- deleted after confirmed save by default.

Food photos:

- may be retained;
- optimized;
- private.

## 18. Failure Recovery

Handle:

- camera permission denied;
- camera unavailable;
- unsupported browser;
- network failure;
- AI timeout;
- invalid AI output;
- low confidence;
- expired session;
- invalid reminder;
- deleted meal;
- save failure.

Always provide a clear next action.

## 19. Offline Behavior

Initial MVP may cache the application shell.

Do not claim a record is saved until the server confirms it.

Full offline queueing is later scope.

## 20. Privacy

Requirements:

- authentication;
- user ownership;
- authenticated image access;
- no frontend secrets;
- no advertising;
- no public sharing;
- no medical claims.

## 21. MVP Scope

### Authentication

- login;
- logout;
- session check.

### Direct capture

- glucose meter;
- scale;
- food;
- unknown fallback;
- confirmation and correction.

### Measurements

- fasting glucose;
- post-meal glucose;
- weight;
- manual fallback.

### Meals

- creation from photo;
- AI description;
- editable start time;
- optional retained photo.

### Reminders

- 2-hour;
- 3-hour;
- push notification;
- camera deep link;
- completion.

### History

- timeline;
- filtering;
- editing;
- deleting.

### Dashboard

- core cards;
- basic charts.

### Meal analysis

- linked measurements;
- closest 2-hour and 3-hour readings;
- actual elapsed time;
- absolute change.

## 22. Outside Initial MVP

- native iOS/Android apps;
- smartwatch integration;
- Bluetooth meter integration;
- CGM integration;
- Apple Health;
- Health Connect;
- clinician portal;
- medication tracking;
- calorie counting;
- dosing recommendations;
- billing;
- public registration;
- password-recovery email;
- full offline sync.
