# ADR 007: Separate Meals and Measurements

## Status

Accepted

## Context

Meals have descriptions, photos, reminders, and multiple related measurements.

Keeping all meal fields inside measurement rows would duplicate data and complicate analysis.

## Decision

Represent meals and measurements as separate entities.

Post-meal measurements may reference a meal.

Reminders reference meals.

## Consequences

Positive:

- clean one-to-many relationships;
- better meal analysis;
- no duplicated meal description across readings;
- reminders have a stable parent.

Negative:

- more joins;
- manual post-meal flow needs matching logic.

## Alternatives

- Single measurements table with meal fields: rejected due to duplication.
- Full normalized food-item model: deferred as unnecessary for MVP.

## Review Triggers

- meal composition analysis requires food tags or ingredients;
- fasting windows need first-class entities.
