# Identity and access security — Phase 2

## Authentication

- Laravel's session guard authenticates by username or email. Passwords use Laravel's `hashed` cast and the configured bcrypt driver.
- Login, registration, and password reset have per-identity/IP rate limits. Login failures use one generic response.
- Login regenerates the session ID. Logout invalidates the session and regenerates the CSRF token.
- New public accounts are Residents with `pending` status. Email verification uses Laravel's expiring signed URL; activation remains a separate Super Admin account operation. Unverified or inactive accounts cannot use active-account routes.
- Password reset links are only delivered to active, email-verified accounts. The response does not confirm whether an email is registered. Reset rotates the remember token.
- Local `.env.example` uses the log mailer. A real verified-email delivery channel must be configured before deployment.

## Authorization and ownership

- Routes are protected by authentication, active-account checks, role middleware, and policies. No client-supplied role or assignment is accepted by public registration/profile forms.
- Resident profiles are one-to-one with users. Profile writes derive `user_id` from the authenticated session. Residents cannot change their user link, account status, role, or verification state.
- Residents propose an RT region. Village Admin verification is required before RT/RW list queries include that profile. The verification UI exposes only username and region.
- RT/RW scope derives from active `region_officers` assignments. RT assignments must match the exact RT. RW scope follows child RT rows whose parent RW is assigned and belongs to the same village. A removed assignment stops working immediately.
- BUMDes staff editing changes only staff profile fields. It neither assigns account roles nor grants unrelated service permissions.
- Super Admin is treated as technical administration. It receives no official signing authority. The Village Head role remains separate and cannot be assigned through the Phase 2 UI.
- Super Admin can maintain village/RW/RT master data so residents can select a valid RT. Creating a region does not assign an officer or expose resident data; assignments and resident-region verification remain separate checks.

## Sensitive identity values

- Resident full name, NIK, phone, address, birth date, and gender use AES-256-GCM encryption at rest. Existing user names and `users.phone` values are migrated to encrypted columns; legacy plaintext compatibility fields are replaced with non-personal placeholders or cleared. NIK lookup uses a separate HMAC-SHA-256 blind-index key.
- `DATA_ENCRYPTION_KEY` and `IDENTITY_BLIND_INDEX_KEY` must each be independent base64-encoded 32-byte random keys. Keep them outside source control and use a managed secret store with restricted access in deployment.
- Decryption is performed only for the resident's own profile or the resident name shown to an assigned RT/RW officer. Officer screens do not expose NIK, phone, address, or birth date.
- Key rotation and recovery procedures are operational requirements and are not automated by Phase 2.

## Audit events

- `AuditLogger` records authentication outcomes, registration, email verification, password reset, account/role changes, region assignment/revocation, resident profile views/changes, region verification, and BUMDes staff changes.
- Audit metadata is filtered for credentials and identity fields. Request bodies and passwords are never recorded. IP addresses are stored in binary form; user agents are capped at 500 characters.
- The audit model rejects normal Eloquent update/delete operations and the UI is read-only for Super Admin. This is application-level append-only protection; production database credentials should deny `UPDATE` and `DELETE` on `audit_logs` to obtain database-enforced immutability.
- Retention duration and export policy require a project/legal decision and are not set here.

## Operational controls and known limits

- Use HTTPS in deployment and set `SESSION_SECURE_COOKIE=true`; keep HttpOnly, SameSite=Lax, and CSRF protection enabled.
- MySQL migration and integration behavior are not verified until the dedicated disposable MySQL 8 test database is reachable. Never aim the test suite at a persistent application database because feature tests use `RefreshDatabase`.
- Region verification is a workflow foundation, not evidence collection or a legal finding. Define the village's verification procedure before operational use.
- The current schema retains legacy Laravel `users.name` and email length for forward compatibility. Existing database migration history has not been inspected.
