# Production readiness assessment

**Assessment date:** 2026-11-10
**Decision: NOT READY**

This is an evidence-based status for the current local repository state, not a statement that the product can never be deployed. The test suite and asset build pass, but essential production controls were not verified.

## Readiness checklist

| Area | Status | Evidence / condition |
|---|---|---|
| Authentication and baseline role/ownership tests | Partially verified | Full SQLite suite passes: 99 tests / 758 assertions. This does not replace adversarial or production-engine testing. |
| Sensitive identity encryption | Source reviewed | AES-256-GCM and separate keyed blind index are configured. Key rotation/recovery is manual and untested. |
| Response headers | Implemented and tested | Middleware and feature test cover baseline headers. HSTS is deployment-layer; CSP awaits nonce/inline behavior review. |
| Database schema/migrations | PARTIALLY VERIFIED | User reports the isolated database suite passes on MariaDB 13.0.2 (99 tests / 758 assertions); persistent DB migration status is pending and MySQL 8 target remains untested. |
| MySQL row locking and atomic marketplace checkout | NOT VERIFIED | MariaDB suite pass is user-reported; no explicitly concurrent MySQL 8 test evidence was provided. |
| HTTPS and production web server | NOT READY | Available Nginx file serves HTTP only and is local; no production TLS configuration reviewed. |
| Private documents and upload handling | Source reviewed | Private disk and authorized download handlers exist; production storage permissions and browser access checks still need verification. |
| Real payment integration | Not configured | Manual invoice review and simulated/test marketplace provider; no real gateway. |
| Real public Wi-Fi enforcement | Not configured | Default is simulated; no verified router/captive portal enforcement. |
| Audit retention/immutability | Partial | Application rejects ordinary Eloquent edits, but DB grants, retention, and protected export are not verified. |
| Backups and recovery | NOT VERIFIED | Procedure documented; no restore drill, RPO/RTO, or retention approval. |
| Dependency vulnerabilities | NOT VERIFIED | Composer/npm registries unreachable during audit. License metadata is present, not legal clearance. |
| Frontend production build | PASS | `npm run build` succeeded. |
| Responsive/accessibility browser QA | NOT VERIFIED | No browser automation was available. |
| Production deployment rehearsal | NOT RUN | No staging/production environment was available. |

## Conditions before reconsidering readiness

1. Run and record migration status locally. Confirm the production database target. If it is MySQL 8, verify the exact version, migration history, foreign keys/checks, payment invariant, and concurrent stock reservation on a dedicated disposable MySQL 8 database. If MariaDB is the target, confirm and test MariaDB separately.
2. Review every role/resource path in staging with direct URL and manipulated-ID tests; run independent SAST/dependency/secret scanning and remediate findings.
3. Configure production TLS/reverse proxy, secure cookies, environment secrets, mail, private storage, least-privilege DB accounts, safe logs, and monitoring. Verify actual headers and error pages over HTTPS.
4. Complete an encrypted database + private-file restore drill using recovered independent encryption keys. Approve retention and RPO/RTO.
5. Run browser keyboard, focus, responsive, contrast, and user-flow checks at mobile/laptop/wide viewports.
6. Re-run dependency audits with working registries and review package notices.
7. Clearly accept or replace simulated payment/Wi-Fi behavior before any real-money or connectivity claim.

See `SECURITY_AUDIT.md`, `TEST_REPORT.md`, `DEPLOYMENT.md`, and `BACKUP_AND_RECOVERY.md` for evidence and runbooks. No deployment operation was performed.
