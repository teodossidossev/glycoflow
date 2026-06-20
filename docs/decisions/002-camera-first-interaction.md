# ADR 002: Camera-First Interaction

## Status

Accepted

## Context

Manual Excel tracking is slow because the user must type measurements and meal descriptions.

The photographed object already provides enough context to infer the record type.

## Decision

Use one primary camera action.

Direct capture mapping:

- glucose meter → fasting glucose;
- body scale → weight;
- food → meal start;
- unknown → fallback choices.

Use a separate “After meal” entry point only because that context changes a glucose-meter photo from fasting to post-meal.

## Consequences

Positive:

- minimal input;
- intuitive mobile flow;
- no record-type menu before direct capture.

Negative:

- AI classification errors must be handled;
- camera permission is critical;
- explicit manual fallback is required.

## Alternatives

- Separate Add glucose / Add weight / Add meal buttons: rejected as unnecessary friction.
- One large form: rejected as poor mobile UX.

## Review Triggers

- classification is too unreliable;
- users frequently need category override;
- camera flow proves slower than explicit actions.
