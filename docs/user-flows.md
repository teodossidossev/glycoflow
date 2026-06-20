# GlycoFlow User Flows

## 1. General Model

```text
Open application
→ open camera
→ capture image
→ AI classifies
→ application proposes data
→ user confirms or corrects
→ server saves
→ success feedback
```

AI-recognized health data is never saved without confirmation.

## 2. Application Entry

### Authenticated

1. Check session.
2. Show Home.
3. Show camera action immediately.
4. Show pending reminders.
5. Allow capture without menu navigation.

### Unauthenticated

1. Show Login.
2. Submit email and password.
3. On success, load CSRF token.
4. Resume intended route, including reminder deep links.
5. On failure, show generic credentials error.

## 3. Home

Priority:

1. Capture;
2. After meal;
3. pending reminders;
4. dashboard summary;
5. recent records.

No horizontal scrolling.

## 4. Direct Capture Flow

### Start

1. Request camera access when needed.
2. Prefer rear camera.
3. Show live preview.
4. Show capture button.
5. Offer file-picker fallback.
6. Offer cancel.

Do not ask what will be photographed.

### Processing

1. Freeze preview.
2. Show processing state.
3. Resize and compress.
4. Upload.
5. Analyze.
6. Validate normalized result.
7. Route to correct confirmation screen.

Possible classifications:

```text
glucose_meter
scale
food
unknown
```

### Glucose meter

1. Recognize value.
2. Set type `fasting_glucose`.
3. Propose current date and time.
4. Normalize to `mmol/L`.
5. Show glucose confirmation.

### Scale

1. Recognize weight.
2. Set type `weight`.
3. Propose current date and time.
4. Normalize to `kg`.
5. Show weight confirmation.

### Food

1. Propose current date and time as meal start.
2. Show AI description.
3. Allow edit.
4. Offer 2-hour and 3-hour reminders.
5. Show meal confirmation.

### Unknown

Offer:

- Retake;
- Glucose meter;
- Scale;
- Food;
- Manual entry;
- Cancel.

## 5. Glucose Confirmation

Show:

- value;
- unit;
- type;
- date;
- time;
- confidence warning where relevant.

Actions:

- Save;
- Edit;
- Retake;
- Cancel.

On save:

1. Validate.
2. Submit.
3. Server saves.
4. Delete temporary image if retention is disabled.
5. Show success.
6. Refresh Home and History.

## 6. Weight Confirmation

Show:

- weight;
- unit;
- date;
- time;
- confidence warning.

Same action pattern as glucose.

## 7. Meal Confirmation

Show:

- photo preview;
- description;
- start date and time;
- reminder options.

Save without reminders:

1. Save meal.
2. Finalize image.
3. Return Home.

Save with reminders:

1. Save meal.
2. Create selected reminders.
3. Calculate times from confirmed meal start.
4. Show scheduled times.
5. Return Home.

## 8. Manual Post-Meal Flow

### Start

1. User selects “After meal”.
2. Camera opens in post-meal mode.
3. Camera expects a glucose meter.

### Recognition

1. Recognize glucose value.
2. Propose current time.
3. Load recent meals.
4. Calculate elapsed time.
5. Rank against 120 and 180 minutes.
6. Show best candidate.

### Strong candidate

Show:

- glucose value;
- meal description;
- meal start;
- measurement time;
- actual elapsed time;
- closest target.

Actions:

- Save;
- Choose another meal;
- Edit;
- Retake;
- Cancel.

### Multiple candidates

Highlight best candidate and list alternatives.

### No suitable meal

Offer:

- add missing meal;
- choose older meal;
- enter meal time;
- save without link;
- cancel.

Preserve the recognized glucose result.

## 9. Reminder-Driven Post-Meal Flow

### Delivery

1. Cron finds due reminder.
2. Claim reminder.
3. Send push.
4. Record delivery.
5. Update state.

### Tap

1. PWA opens or focuses.
2. Process reminder deep link.
3. Check session.
4. Load reminder.
5. Load meal.
6. Open camera directly.

Do not show Home first.

### Camera context

Show:

- “2 hours after meal” or “3 hours after meal”;
- description;
- start time;
- instruction to photograph the meter.

### Completion

1. Photograph glucose meter.
2. Recognize value.
3. Calculate actual elapsed minutes.
4. Confirm.
5. Save measurement in a transaction.
6. Mark reminder completed.
7. Show success.

### Already completed

Show that the reminder is already completed.

Do not create another measurement automatically.

## 10. Manual Entry Fallback

### Glucose

Fields:

- value;
- type;
- date;
- time;
- linked meal when relevant;
- notes.

### Weight

Fields:

- weight;
- date;
- time;
- notes.

### Meal

Fields:

- description;
- start date and time;
- reminders;
- notes.

Forms must remain contextual.

## 11. History

1. Open History.
2. Show reverse chronological cards.
3. Filter by date and type.
4. Search meals.
5. Open details.
6. Edit or delete.

Deleting a meal should preserve measurements and clear their `meal_id`.

## 12. Dashboard

Load:

- current weight;
- starting weight;
- total change;
- latest fasting glucose;
- 7-day and 30-day averages;
- 7-day weight average;
- below-threshold count;
- trends;
- pending reminders.

Changing period refreshes cards and charts.

Empty data uses neutral empty states, not zeroes.

## 13. Meal Analysis

Show meals with linked values.

Each meal may include:

- pre-meal;
- closest 2-hour value;
- closest 3-hour value;
- actual elapsed minutes;
- change from baseline.

Missing baseline remains unavailable.

## 14. Settings

Initial settings:

- email;
- timezone;
- starting weight;
- push permission;
- devices;
- food-photo retention;
- logout.

Request notification permission only after explaining why.

## 15. Session Expiry

During an in-progress flow:

1. preserve practical local state;
2. redirect to Login;
3. resume after success;
4. avoid duplicate save.

## 16. Network Failure

Before analysis:

- Retry;
- Retake;
- Manual entry;
- Cancel.

After analysis but before save:

- keep proposal;
- Retry save;
- Edit;
- Cancel.

## 17. Duplicate Prevention

- disable save during active request;
- support client request IDs;
- make reminder completion unique;
- do not create duplicates on retry.

## 18. Camera Failure

Explain failure and offer:

- Retry;
- file upload;
- manual entry;
- Cancel.

## 19. AI Failure

For invalid, incomplete, or low-confidence results:

- show warning;
- allow correction;
- allow retake;
- allow category override;
- apply independent plausibility checks.

## 20. Unsaved Changes

Warn before discarding meaningful edits.

A untouched camera screen may close without warning.

## 21. Success Messages

Examples:

```text
Fasting glucose saved: 5.8 mmol/L
Weight saved: 124.3 kg
Meal saved. Reminder scheduled for 15:10.
Post-meal glucose saved: 6.7 mmol/L
```

## 22. Capture State Model

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

Only valid transitions are allowed.

## 23. Shared Rules

- current date and time are defaults;
- date and time are editable;
- do not ask for known context;
- never save AI results without confirmation;
- always provide manual fallback;
- preserve corrections after recoverable errors;
- avoid duplicates;
- refresh affected views after save;
- display in user timezone;
- store UTC;
- never expose internal errors.
