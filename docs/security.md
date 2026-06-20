# GlycoFlow Security Model

## 1. Security Goals

Protect:

- account credentials;
- session state;
- health-related records;
- meal photos;
- AI credentials;
- database credentials;
- push subscription secrets.

Prevent:

- unauthorized access;
- cross-user data access;
- CSRF;
- SQL injection;
- arbitrary file upload;
- path traversal;
- duplicate reminder actions;
- secret leakage;
- unsafe AI trust.

## 2. Trust Boundaries

Untrusted:

- browser input;
- URL parameters;
- JSON payloads;
- file metadata;
- AI output;
- push deep-link IDs;
- client timestamps;
- client-provided ownership IDs.

Trusted only after validation:

- authenticated session user ID;
- server-side configuration;
- repository results filtered by `user_id`;
- normalized backend-generated identifiers.

## 3. Authentication

- PHP sessions;
- `password_hash()`;
- `password_verify()`;
- generic invalid-credentials message;
- session ID regeneration after login;
- login rate limiting;
- secure cookie settings.

Cookies:

- `HttpOnly`;
- `Secure` in production;
- appropriate `SameSite`;
- limited path where practical.

## 4. Authorization

Every user-owned query includes `user_id`.

Never accept a user ID from the client as ownership proof.

Do not reveal whether another user's resource exists.

## 5. CSRF

Require CSRF token for:

```text
POST
PUT
PATCH
DELETE
```

Use a request header such as:

```text
X-CSRF-Token
```

Rotate or renew tokens according to session lifecycle.

## 6. SQL Injection

- PDO prepared statements;
- no user input concatenated into SQL;
- whitelist sort fields;
- whitelist direction;
- validate identifiers as integers;
- keep SQL in repositories.

## 7. Input Validation

Validate:

- JSON shape;
- required fields;
- types;
- lengths;
- accepted values;
- numeric ranges;
- timestamps;
- relationships;
- resource ownership.

Never rely only on frontend validation.

## 8. File Upload Security

Validate:

- upload status;
- actual MIME type from file contents;
- maximum size;
- dimensions where practical;
- supported format;
- decode success.

Use random generated names.

Never trust original names or extensions.

Store outside public web root where possible.

Prevent execution.

Do not accept arbitrary destination paths.

## 9. Image Access

Food photos are served only through an authenticated endpoint.

The endpoint accepts a logical identifier, not a filesystem path.

Set safe content type and cache headers.

Return `404` for unavailable or unauthorized images.

## 10. AI Security

AI output is untrusted.

Validate:

- classification;
- unit;
- value;
- confidence;
- description length;
- detected-item length;
- structured shape.

AI output never:

- writes directly to the database;
- chooses paths;
- executes code;
- executes SQL;
- bypasses confirmation.

Keep AI keys in server-side configuration.

## 11. Push Security

Store push endpoint and keys securely.

Never log:

- subscription auth secret;
- full endpoint if avoidable;
- VAPID private key.

Deep-link reminder IDs are not authorization.

Backend validates ownership and state.

## 12. Reminder Idempotency

Use:

- `processing` state;
- atomic claiming;
- unique reminder-to-measurement relationship;
- transaction for completion;
- delivery attempt records.

## 13. Session Expiry

Frontend handles `401` centrally.

In-progress work may be preserved temporarily.

After login, intended route resumes.

Do not duplicate saves.

## 14. Error Handling

Client receives safe code and message.

Server logs internal details with request ID.

Never expose:

- SQL;
- table names;
- stack traces;
- credentials;
- physical paths;
- provider secrets.

## 15. Logging

Do not log:

- passwords;
- session IDs;
- cookies;
- CSRF tokens;
- database credentials;
- AI keys;
- push secrets;
- raw image bytes;
- complete request bodies for health records.

## 16. Secrets

Never commit:

- `.env`;
- production config;
- credentials;
- VAPID private key;
- AI key;
- uploaded files.

Use `.env.example` with placeholders only.

## 17. Headers

Recommended production headers:

```text
Content-Security-Policy
X-Content-Type-Options: nosniff
Referrer-Policy
Permissions-Policy
Strict-Transport-Security
```

CSP must account for Angular, API calls, camera use, and Web Push without broadly allowing unsafe sources.

## 18. CORS

Prefer same-origin deployment.

Do not enable permissive `*` CORS.

If cross-origin development is needed, allow only explicit local origins.

## 19. Rate Limiting

Apply to:

- login;
- AI analysis;
- push registration;
- potentially expensive statistics.

## 20. Dependencies

- use maintained packages;
- pin compatible versions;
- review major upgrades;
- run dependency audit in CI where practical;
- avoid unnecessary packages.

## 21. Backups

Back up:

- database;
- retained food images;
- server configuration securely.

Do not include secrets in public artifacts.

Test restoration periodically.

## 22. Incident Response

If a secret is exposed:

1. revoke or rotate it;
2. remove it from the repository history where necessary;
3. inspect logs;
4. invalidate affected sessions or subscriptions;
5. document the incident.

## 23. Security Review Triggers

Perform explicit review before:

- public registration;
- password recovery;
- sharing data;
- third-party health integrations;
- native app packaging;
- cloud storage;
- multi-user launch;
- clinician access.
