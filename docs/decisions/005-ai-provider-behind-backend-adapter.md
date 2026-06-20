# ADR 005: AI Provider Behind a Backend Adapter

## Status

Accepted

## Context

The product needs image classification and OCR-like extraction for meters, scales, and food.

Provider models and response formats may change.

API keys must remain private.

## Decision

Call the AI provider only from PHP.

Expose an internal `ImageAnalysisProvider` abstraction.

Normalize provider output to:

```text
kind
confidence
value
unit
foodDescription
detectedItems
```

Validate all output.

## Consequences

Positive:

- secrets remain server-side;
- provider can change;
- frontend contract stays stable;
- mock provider supports development.

Negative:

- PHP must handle image upload and provider errors;
- provider latency affects request time.

## Alternatives

- Direct browser call: rejected because it exposes secrets.
- Provider-specific response in frontend: rejected due to coupling.

## Review Triggers

- provider latency requires asynchronous processing;
- model costs require a different architecture;
- a specialized local OCR service is introduced.
