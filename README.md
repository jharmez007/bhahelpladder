# BHA Help Ladder

The BHA Help Ladder website and support portal uses Laravel 12, Livewire 4, Blade, Tailwind CSS 4 and PostgreSQL. Phases 1 and 2 include the public site, built-in authentication and email verification, role-isolated workspaces, persisted enquiries, counsellor availability, conflict-safe appointment booking, cancellation/rescheduling, staff assignment and discreet queued reminders.

## Requirements

- PHP 8.2 or newer with `pdo_pgsql`, `mbstring`, `openssl`, `curl`, `dom`, `fileinfo`, `intl`, `gd` and `zip`
- Composer 2
- Node.js 20 or newer with npm
- PostgreSQL 14 or newer

## Local setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

On Windows, enable `extension=pdo_pgsql` and `extension=pgsql` in the active `php.ini`, restart the terminal, and confirm `pgsql` appears in `PDO::getAvailableDrivers()`:

```powershell
php -r "var_export(PDO::getAvailableDrivers());"
```

Create an empty PostgreSQL database named `bha_help_ladder` (or choose another name), then keep its credentials only in `.env`:

```dotenv
DB_CONNECTION=pgsql
DATABASE_URL=
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bha_help_ladder
DB_USERNAME=postgres
DB_PASSWORD=your-local-password
DB_SSLMODE=prefer
```

Run migrations only after confirming that `DB_DATABASE` names the new PostgreSQL database:

```bash
php artisan migrate
npm install
npm run build
composer run dev
```

On Windows PowerShell where script execution blocks `npm.ps1`, use `npm.cmd install` and `npm.cmd run build` instead.

The development site is available at `http://127.0.0.1:8000`. Laravel's `composer run dev` also starts the queue listener, log viewer and Vite development server.

Appointment timestamps are stored in UTC and displayed in `Africa/Lagos`. The booking window, minimum notice, client cancellation/rescheduling deadlines and reminder lead time are configured with the `BOOKING_*` values documented in `.env.example`.

## Create staff accounts

Run the interactive command below to create the first administrator. It securely prompts for a name and password, refuses to overwrite an existing account, and does not place a password in shell history.

```bash
php artisan admin:provision admin@example.org
```

Create counsellors with the same secure, interactive pattern:

```bash
php artisan counsellor:provision counsellor@example.org
```

Public registration always creates a client account; counsellor and administrator roles cannot be chosen through the website. An administrator can activate services and assign them to counsellors under **Booking setup**. Counsellors must also publish working hours before clients see slots.

## Queues and scheduled reminders

Booking, status and reminder emails implement Laravel's queued notification contract. A conventional production environment can use a queue worker:

```bash
php artisan queue:work --tries=3
```

Run Laravel's scheduler every minute in production. It invokes `appointments:queue-reminders` every ten minutes with overlap protection. For a local foreground scheduler:

```bash
php artisan schedule:work
```

Reminder rows have a unique appointment/kind key, so repeated scheduler runs do not queue duplicate upcoming reminders.

The Render Free preview intentionally uses `QUEUE_CONNECTION=sync` because Free instances do not include a worker. Appointment notifications are delivered only after the database transaction commits; mail-provider failures are safely logged and never roll back a successful booking. Automated reminders are unavailable there until a trusted external scheduler is explicitly configured.

## PostgreSQL test database

Tests refuse to start unless the default connection is PostgreSQL and the database name ends in `_test`. Create a separate database without dropping or recreating any existing database:

```powershell
$env:DB_CONNECTION='pgsql'
$env:DB_HOST='127.0.0.1'
$env:DB_PORT='5432'
$env:DB_DATABASE='bha_help_ladder_test'
$env:DB_USERNAME='postgres'
$env:DB_PASSWORD='<local PostgreSQL password>'
$env:DB_SSLMODE='prefer'
php -d extension=pdo_pgsql tests\Fixtures\CreatePostgreSqlTestDatabase.php
```

The creation helper only creates a missing database whose name ends in `_test`; it never drops or resets one. Apply the schema and run the focused PostgreSQL tests with the same environment variables:

```powershell
php -d extension=pdo_pgsql artisan migrate:fresh --force --no-interaction
php -d extension=pdo_pgsql vendor\bin\phpunit tests\Feature\Appointments\BookingWorkflowPhpUnitTest.php tests\Feature\Authorization\RoleAuthorizationTest.php tests\Feature\Appointments\PostgreSqlConcurrentBookingTest.php
```

`migrate:fresh` is appropriate only for this isolated `_test` database. The two-process concurrency tests prove that PostgreSQL serializes simultaneous booking and rescheduling attempts; one request must conflict, and the losing reschedule must retain its original appointment.

## Existing MySQL source and safe cutover

The MySQL source remains configured only in the uncommitted local `.env` and has not been migrated, reset or modified. A read-only inventory on 13 November 2026 found zero application rows in users, enquiries, services, counsellor configuration, appointments, reminders, queue and session tables; only six migration-history rows exist. No data-transfer job is required for that snapshot.

Immediately before a future cutover, repeat a read-only inventory:

```powershell
php artisan db:show --database=mysql --counts --no-interaction
```

If any application records have appeared, stop the cutover and prepare/test a separate ID-preserving transfer before changing the application connection. That transfer must copy parent tables before children, preserve password hashes, enums/statuses and UTC timestamp values, reset every PostgreSQL identity sequence to the maximum imported ID, compare per-table row counts, validate foreign keys, and leave MySQL intact. Do not point either migration commands or automated tests at the MySQL source.

## Render Free preview deployment

The production `Dockerfile` builds Composer dependencies and Vite assets, installs `pdo_pgsql` plus the existing required extensions, serves only `public` through Apache on Render's dynamic `PORT`, and performs a bounded database wait followed by `php artisan migrate --force`. Startup never resets or seeds data.

The exact dashboard settings, environment-variable checklist, HTTPS/proxy behavior, Resend HTTPS mail setup, secure administrator provisioning, log checks and current Free-tier limitations are documented in [docs/RENDER_FREE_DEPLOYMENT.md](docs/RENDER_FREE_DEPLOYMENT.md).

Use Render's internal PostgreSQL URL for the same-region web service and `DB_SSLMODE=prefer`. Use the external URL only for an explicit trusted local administration task, with `DB_SSLMODE=require`. Keep `APP_KEY`, database and mail credentials solely in Render environment settings.

## Email and client content

For local development, email is written to the application log using the non-deliverable `no-reply@localhost` sender. The Render Free preview uses Laravel's Resend transport over HTTPS because Free web services block outbound SMTP ports. Delivery requires a Resend API key and verified sender domain; these remain external integrations to configure. Set the `BHA_*` values in `.env` only after the client supplies and approves them. See [docs/CLIENT_CONTENT_CHECKLIST.md](docs/CLIENT_CONTENT_CHECKLIST.md).

## Verification

The standard command is `php artisan test`, after configuring the isolated PostgreSQL test database above. SQLite and MySQL results are not substitutes for the real PostgreSQL concurrency suite.

Build production assets with `npm run build`. Dependency lockfiles are committed for repeatable installs.

## Phase boundary

This release includes live appointment scheduling but not clinical records or payment collection. It does not include therapy notes, direct minor registration, SMS/WhatsApp, embedded video, calendar sync, analytics or sensitive uploads. See [ROADMAP.md](ROADMAP.md) for the approved later phases.
