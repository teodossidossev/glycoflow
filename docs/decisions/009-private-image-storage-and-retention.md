# ADR 009: Private Image Storage and Limited Retention

## Status

Accepted

## Context

Images may contain personal health or household information.

Glucose-meter and scale photos have little value after extraction.

Food photos remain useful for history and analysis.

## Decision

- Meter and scale images are temporary and deleted after confirmation by default.
- Food images may be retained as optimized private files.
- Images are stored outside public web root where possible.
- Images are served through an authenticated PHP endpoint.
- Original filenames are never trusted.

## Consequences

Positive:

- reduced privacy risk;
- lower storage use;
- retained meal context.

Negative:

- authenticated image streaming is required;
- file/database cleanup must be coordinated.

## Alternatives

- Keep all images: rejected.
- Keep no images: rejected because meal photos are useful.
- Public image URLs: rejected.

## Review Triggers

- user enables optional meter-photo retention;
- cloud object storage is introduced;
- storage limits require stronger retention policies.
