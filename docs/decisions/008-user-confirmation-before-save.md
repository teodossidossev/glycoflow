# ADR 008: User Confirmation Before Save

## Status

Accepted

## Context

AI may misread health measurements or misclassify an image.

Silent persistence would create unsafe and misleading records.

## Decision

AI output is always a proposal.

The user must confirm or correct before permanent save.

Available actions:

- Save;
- Edit;
- Retake;
- Manual entry;
- Cancel.

## Consequences

Positive:

- prevents silent AI errors;
- keeps the user in control;
- supports correction.

Negative:

- adds one interaction step;
- truly zero-touch recording is not supported.

## Alternatives

- Auto-save above a confidence threshold: rejected.
- Save and allow later correction: rejected for health measurements.

## Review Triggers

This decision should be treated as locked unless a substantially safer verification mechanism exists.
