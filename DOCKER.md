# Local team environment with Docker

This Compose setup builds the Laravel app and its Vite production assets into Docker images. Each developer has a separate local MySQL database and private storage volumes; the database is not shared with other team members or production.

## Requirements

- Docker Engine
- Docker Compose v2 (`docker compose`)
- Git

## First run

1. Clone the same project branch or commit as the team and open a terminal in `Ekosistem-Digital-Desa`.
2. Create a private Docker environment file:

   ```sh
   cp .env.docker.example .env.docker
   ```

3. Generate two different local database passwords:

   ```sh
   openssl rand -hex 24
   openssl rand -hex 24
   ```

   Put the first result in `DB_PASSWORD` and the second in `DB_ROOT_PASSWORD` in `.env.docker`. Keep `.env.docker` private; it is ignored by Git. Do not reuse a production password.

   Each developer uses their own `.env.docker`, Docker database volume, generated encryption keys, and uploads. The Compose database is MySQL 8.4 inside Docker; do not point it at a developer's host MariaDB.

   Email and payments are safe local defaults: email is logged and marketplace payments are simulated. Queue jobs use the database queue and a separate Compose worker. To test Gmail or Xendit, each developer must add their own credentials to `.env.docker` using the examples in `.env.docker.example`. For Xendit, use sandbox credentials, set `MARKETPLACE_PAYMENT_MODE=test`, and enable Payouts only after the sandbox account has Money Out permission. Never commit credentials or share a real payment secret.

4. Build and start the application:

   ```sh
   docker compose --env-file .env.docker up --build -d
   ```

   `--env-file` belongs after `docker compose`, not directly after `docker`.

5. Apply Laravel migrations to this new local Docker database:

   ```sh
   docker compose --env-file .env.docker exec app php artisan migrate
   ```

6. Seed the platform roles, then create the first local Super Admin if this is a new database:

   ```sh
   docker compose --env-file .env.docker exec app php artisan db:seed --class=RolesSeeder
   docker compose --env-file .env.docker exec app php artisan app:bootstrap-administrator
   ```

   The administrator command prompts for account details. This initial account is technical Super Admin; it does not grant Village Head signing authority.

7. Open the `APP_URL` address from `.env.docker` (default `http://localhost:8000`). Check the app health endpoint with:

   ```sh
   curl -i http://localhost:8000/up
   ```

Laravel and data-encryption keys are generated once and kept in a per-developer Docker volume. The MySQL data and uploaded files also persist in local Docker volumes.

## Updating to a teammate's changes

Pull the approved source revision, then rebuild and restart the images:

```sh
git pull
docker compose --env-file .env.docker up --build -d
docker compose --env-file .env.docker exec app php artisan migrate
```

The image contains PHP 8.5, required PHP extensions, Composer production dependencies, Node 24-built Vite assets, and the matching Nginx configuration. MySQL runs as a separate MySQL 8.4 service. The build uses `composer.lock` and `package-lock.json` so teammates install the same locked dependencies.

## Useful commands

```sh
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker logs -f app worker web database
docker compose --env-file .env.docker down
```

`down` stops and removes the containers but preserves local database, upload, and generated-key volumes. Avoid `docker compose down --volumes` unless you intentionally want to erase that developer's local database, uploads, and generated keys.

This is a local test environment. It does not configure production TLS, real email delivery, Xendit credentials, or live network-gateway enforcement. Never use production data or production credentials in a developer's local containers.
