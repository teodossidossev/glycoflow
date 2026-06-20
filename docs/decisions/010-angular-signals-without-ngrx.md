# ADR 010: Angular Signals Without NgRx for the MVP

## Status

Accepted

## Context

The application has moderate state needs but is initially small and private.

Angular includes Signals, Router, HttpClient, and Reactive Forms.

## Decision

Use Signals and services for frontend state.

Use RxJS for HTTP and natural asynchronous streams.

Do not add NgRx in the MVP.

## Consequences

Positive:

- lower complexity;
- fewer dependencies;
- easier agent-assisted maintenance;
- adequate for capture and dashboard state.

Negative:

- the team must maintain clear service boundaries;
- very complex future workflows may outgrow the approach.

## Alternatives

- NgRx from the start: rejected as premature.
- Component-only state: rejected because shared state is required.

## Review Triggers

- state transitions become difficult to reason about;
- auditability or time-travel debugging becomes necessary;
- multiple complex feature stores emerge.
