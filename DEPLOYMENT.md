# Deployment guide

This describes a production deployment path; it has **not** been executed or certified. Do not deploy the local `compose.yaml`/`docker/nginx/default.conf` as-is: that Nginx configuration serves HTTP on port 80 and is for local development.

## Runtime requirements

- PHP 8.3 or later within Laravel 13's supported range, Composer 2, and required PHP extensions: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PDO with the chosen database driver, Session, Tokenizer, XML, plus GD/Intl/Zip/PDO MySQL for this project configuration.
- MySQL 8.0+ is the schema target. Confirm exact production version against migration and integration tests before rollout. MariaDB is not assumed equivalent.
- Node.js 20.19+ or 22.12+ compatible runtime and npm for asset compilation. Use the committed lockfiles (`composer.lock`, `package-lock.json`).
- Nginx/Apache or a managed platform, TLS certificate and renewal, persistent private file storage, monitored logs, and a secrets manager.

## Build and configure a release

1. Deploy an immutable release from the approved source revision. Keep `.env`, `.env.docker`, credentials, generated keys, uploads, and database backups outside source control and outside public web root.
2. Install PHP dependencies with `composer install --no-dev --prefer-dist --optimize-autoloader` and build frontend assets with `npm ci && npm run build` in CI/build stage. Do not run package-manager install against a live server unless that is the approved deployment process.
3. Configure `APP_ENV=production`, `APP_DEBUG=false`, canonical `APP_URL=https://…`, the production MySQL host/database/least-privilege application account, mail transport, cache/session/queue drivers, and filesystem storage. Configure independent `DATA_ENCRYPTION_KEY` and `IDENTITY_BLIND_INDEX_KEY` secrets as documented in `docs/IDENTITY_AND_ACCESS_SECURITY.md`. Never reuse `APP_KEY` for these keys.
4. Set `SESSION_SECURE_COOKIE=true` behind HTTPS. Keep HttpOnly, SameSite=Lax, CSRF, and Laravel session regeneration enabled. Configure trusted proxies correctly if TLS terminates upstream; do not trust arbitrary forwarded headers.
5. Store private documents under private storage or an equivalently access-controlled object store. Never expose `storage/app/private` under the web root. Give the app write access only to required storage/cache paths.
6. Terminate TLS at the managed load balancer or configure a reviewed web server with HTTPS, modern TLS, HTTP-to-HTTPS redirect, and HSTS only after all relevant subdomains are HTTPS. Protect `/storage` and internal paths. Do not copy the current local HTTP-only Nginx file as production config.
7. Configure process supervision for PHP-FPM and the web server. If asynchronous queues are selected, run supervised queue workers with retry/backoff and failed-job monitoring. Configure Laravel scheduler once per deployment environment for the existing invoice and Wi-Fi log cleanup schedules.
8. Configure centralized log collection with access restrictions and retention; never log secrets, resident plaintext, payment credentials, or session tokens. Alert on elevated 5xx, failed jobs, DB errors, disk exhaustion, and backup failures.
9. Before traffic, verify TLS, response headers, health endpoint, email verification/reset, least-privilege DB connection, private file authorization, scheduled jobs, and an isolated restoration drill.

## Email verification with Gmail SMTP

Laravel's built-in SMTP mailer is used; no Gmail package is required. For local testing, set these values in the ignored local `.env` file:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-full-address@gmail.com
MAIL_PASSWORD="your-16-character-google-app-password"
MAIL_FROM_ADDRESS=your-full-address@gmail.com
MAIL_FROM_NAME="Desa Digital"
```

Enable Google 2-Step Verification and create an App Password for the application. Use that generated password, not the normal Google account password. Keep it out of Git, screenshots, logs, and chat. Gmail SMTP uses TLS/STARTTLS; after editing `.env`, run `php artisan config:clear`, register a test account, and check that verification email arrives. For production, use a dedicated sender account and rotate/revoke its App Password if exposed.

## Xendit marketplace payments

The marketplace keeps COD and offers Xendit hosted checkout when payment configuration is ready. Each checkout uses one Payment Session without XenPlatform split routes or seller Business IDs. Seller earnings are credited to an immutable ledger only after the order is completed and its payment is confirmed. Sellers can request a Xendit payout against that available ledger balance; the request reserves the amount before a queued payout job is sent. See `PAYMENT_INTEGRATION.md` for setup and activation requirements.

Set these only in the private `.env` (or the secret store for a deployed environment):

```dotenv
MARKETPLACE_PAYMENT_MODE=test
XENDIT_SECRET_KEY=xnd_development_replace_with_test_secret
XENDIT_WEBHOOK_TOKEN=replace_with_test_webhook_verification_token
XENDIT_PAYOUTS_ENABLED=true
XENDIT_PAYOUT_WEBHOOK_TOKEN=replace_with_payout_webhook_verification_token
XENDIT_PAYOUT_SOURCE_OF_FUND=BUSINESS_REVENUE
XENDIT_PAYOUT_PURPOSE_CODE=TRADES
XENDIT_BASE_URL=https://api.xendit.co
XENDIT_TIMEOUT=10
```

Use the **test/development** secret API key with Money In and Money Out permissions, plus callback tokens from the Xendit Dashboard. Configure payment and payout webhook destinations at `/api/marketplace/xendit/webhook` and `/api/marketplace/xendit/payout-webhook`. Sellers enter their bank SWIFT/BIC, account number, name and address; a BUMDes Manager verifies the destination before approving withdrawals. Use the database queue on the same database connection as the app and keep a queue worker running so withdrawal requests and reserved-balance ledger entries commit atomically before transfer processing. Run a test checkout, complete payment, complete an order, then request a withdrawal using Xendit's test methods. A local app at `localhost` is not reachable by Xendit; use a temporary HTTPS tunnel only for sandbox work, or a secured staging host. Never use live credentials or expose port 3306 to receive webhooks.

After editing `.env`, clear cached configuration with `php artisan config:clear`. Do not put API credentials in Vite variables, Blade, source control, or the database. To enable live mode later, separately review Xendit account activation, Money Out permission, live credentials, production webhook URLs, approved source-of-funds and purpose codes, legal/payment terms, refunds and reconciliation procedures, and then set `MARKETPLACE_PAYMENT_MODE=live` with the live secret/tokens. No live payment or payout has been configured or tested by this implementation.

## Migration and cache sequence

Take and verify a database backup and private-file backup first. Review each forward migration and run it against the exact target engine/version in staging. Production migration command (operator-run only after approval):

```sh
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not use `migrate:fresh`, `db:wipe`, or seed demo data in production. This audit did not run any production command. Confirm new release compatibility with old code/schema before applying migrations; prepare a forward-compatible rollback plan because data migrations may not be safely reversible.

## Rollback

Keep the previous immutable application release and asset build. If the new release fails, stop traffic or put the app in maintenance mode, restore the previous code/assets, clear/rebuild Laravel caches, and verify health/workflows. Do not automatically roll back schema changes or restore a database over production. Use a reviewed forward fix unless a separately approved, verified recovery plan requires restore. Preserve newly created records and files.

## Current limitations

Production TLS/web-server config, secrets-manager integration, monitored queue configuration, Gmail delivery with real credentials, live Xendit configuration, MySQL migration verification, and deployment rehearsal remain unverified. Current Compose setup is a local team test environment, not a production deployment.
