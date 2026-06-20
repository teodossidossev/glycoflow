# ADR 006: Web Push Through PHP Cron

## Status

Accepted

## Context

The product needs phone reminders after 2 or 3 hours.

Production has no persistent worker but may provide cron.

## Decision

Use standard Web Push.

Store subscriptions per user and device.

Use a PHP cron worker to claim due reminders, send notifications, and record delivery attempts.

Notification taps deep-link to the reminder camera flow.

## Consequences

Positive:

- works with shared hosting;
- no daemon required;
- supports installed PWA.

Negative:

- timing depends on cron frequency;
- platform behavior varies;
- iOS requires installed PWA for Web Push.

## Alternatives

- In-app reminders only: insufficient.
- Native local notifications: requires native packaging.
- External scheduler SaaS: unnecessary dependency for MVP.

## Review Triggers

- cron granularity is inadequate;
- push reliability is unacceptable;
- native packaging is adopted.
