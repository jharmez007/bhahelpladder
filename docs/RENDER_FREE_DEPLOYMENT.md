# Render Free preview deployment

This guide prepares a client-preview deployment only. Render Free services and databases do not provide the durability, availability, backups or operational controls required for a permanent production launch.

## What this container does

The multi-stage `Dockerfile` installs production Composer packages, compiles Vite assets, installs `pdo_pgsql` and the existing PHP extensions, and serves Laravel's `public` directory with Apache. At runtime `docker/start-render.sh`:

1. validates the required production settings;
2. binds Apache to `0.0.0.0:$PORT`;
3. waits for PostgreSQL with bounded retries;
4. runs `php artisan migrate --force --no-interaction`;
5. builds Laravel's configuration and view caches; and
6. starts Apache in the foreground.

It never drops, resets or seeds data. A migration failure stops startup visibly. Render terminates TLS at its proxy; Laravel trusts the forwarded proxy headers, forces generated production URLs to HTTPS, uses secure cookies, and writes application and web-server logs to standard output/error.

## Create the Free PostgreSQL database

1. In the Render dashboard, select **New > Postgres**.
2. Choose a name, select the region that will also host the web service, and select the **Free** instance type.
3. Create the database. Do not run any reset or seed command against it.
4. On the database **Info** page, copy the **Internal Database URL** for the web service. Store it only as a Render environment variable.

The internal URL is for services in the same Render account and region. It avoids public-network routing. If a local administrative command must connect, use the separate **External Database URL**, restrict external access where available, and set `DB_SSLMODE=require`. The web service uses the internal URL with `DB_SSLMODE=prefer`; credentials remain inside `DATABASE_URL` and are never committed.

## Create the Free Docker Web Service

1. Select **New > Web Service**, connect this Git repository, and choose the intended branch.
2. Set **Language/Runtime** to **Docker**, leave **Root Directory** blank, set **Dockerfile Path** to `./Dockerfile`, and use `.` as the Docker build context.
3. Choose the same region as PostgreSQL and select the **Free** instance type.
4. Do not add a build command or start command; the Dockerfile supplies both.
5. Set **Health Check Path** to `/up`. The endpoint returns only Laravel's health status and no application or database details.
6. Add the environment variables below, then create the service. Use `https://<service-name>.onrender.com` for `APP_URL`; if Render assigns a different hostname, update it and redeploy. Render supplies `PORT`; do not hard-code it.

If a custom domain is added later, update `APP_URL` to its canonical HTTPS URL.

## Environment-variable checklist

Set these on the web service under **Environment**:

| Variable | Value/purpose |
| --- | --- |
| `APP_NAME` | `BHA Help Ladder` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Public `https://...onrender.com` URL |
| `APP_KEY` | One persistent Laravel key; never regenerate it during deploys |
| `APP_MAINTENANCE_DRIVER` | `cache` |
| `APP_MAINTENANCE_STORE` | `database` |
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | The database's **Internal Database URL** |
| `DB_SSLMODE` | `prefer` for the same-region internal URL |
| `SESSION_DRIVER` | `database` |
| `SESSION_ENCRYPT` | `true` |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_HTTP_ONLY` | `true` |
| `SESSION_SAME_SITE` | `lax` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `info` |
| `MAIL_MAILER` | `resend` |
| `RESEND_API_KEY` | Secret API key from Resend |
| `MAIL_FROM_ADDRESS` | Sender at a domain verified by Resend |
| `MAIL_FROM_NAME` | `BHA Help Ladder` |

Keep the existing `BOOKING_*` values unless the approved policy changes. `DB_WAIT_ATTEMPTS` and `DB_WAIT_DELAY_SECONDS` are optional; defaults are 20 attempts and 3 seconds.

Generate a new Laravel key locally and copy its output directly to Render's secret environment field:

```powershell
php artisan key:generate --show
```

This creates a new secret; it does not reveal any existing application key. Do not put it in `.env.example`, a screenshot, logs or Git. After the first deployment, preserve the same value because Laravel encryption depends on it.

## Email and reminders on Free

Render Free web services block outbound SMTP ports 25, 465 and 587. This deployment therefore uses Laravel's Resend driver over HTTPS. Create a Resend account, verify the sending domain, create an API key, and set the three mail variables above. The Free Resend plan is currently limited, so monitor its daily and monthly quotas.

`QUEUE_CONNECTION=sync` sends appointment notifications during the web request; no paid worker is required. Notification delivery happens after the booking transaction commits. Provider failures are recorded without recipient addresses, provider response text or appointment details, and do not roll back or misrepresent the saved booking.

Automated reminders are **unavailable in this Free deployment** because a Free web service does not run a reliable scheduler and paid Render cron/worker resources are intentionally excluded. Do not expose `schedule:run` or an administrator action as a public endpoint. A future scheduler must run `php artisan schedule:run` from trusted infrastructure with protected credentials; until one is explicitly configured and monitored, do not promise automatic reminders.

## Provision the first administrator securely

Free web services do not provide Render Shell or SSH. Provision the administrator once from a trusted local checkout using the database's temporary external connection details. The command prompts for the password without placing it in shell history and refuses to overwrite an existing account.

```powershell
$env:APP_ENV='production'
$env:APP_KEY='<the same persistent Render APP_KEY>'
$env:DB_CONNECTION='pgsql'
$env:DATABASE_URL='<Render External Database URL>'
$env:DB_SSLMODE='require'
php artisan config:clear
php artisan admin:provision admin@example.org
```

Remove the temporary environment variables from the terminal session afterward and restrict/disable public database access when finished. Never add a public setup route, hard-code a password, seed the administrator, or run this command during container startup. Public registration remains client-only.

## Verify the preview

1. Open the web service **Logs**. Confirm `Database connection is ready`, successful migrations, cached configuration/views, and Apache startup. A migration error must stop the deploy.
2. Open `/up`, then the home, services, contact, registration and login pages over HTTPS. Confirm the browser reports a secure connection and cookies are marked Secure and HttpOnly.
3. Log in with the separately provisioned administrator, configure a service/counsellor schedule, register a client, and complete one test booking. Confirm the booking remains after a service restart and that both parties receive discreet email.
4. Cancel or reschedule the test booking and verify the portal history and email. Inspect logs for safe delivery warnings without private appointment content.
5. Restart/redeploy the web service and confirm database-backed login sessions/cache and appointment data remain in PostgreSQL. Files written only inside the container are not persistent.

## Free-tier limits

As of 13 November 2026, a Free web service spins down after 15 minutes without inbound traffic and can take about a minute to wake. Free workspace usage includes 750 web-service instance hours per month. Its filesystem is ephemeral and is lost on redeploy, restart or spin-down; do not store uploads or durable application state there. Free services do not include persistent disks, Shell/SSH, one-off jobs, background workers or cron jobs.

A workspace can have one active Free Render PostgreSQL database. It is limited to 1 GB, has no backups or connection pooling, and expires 30 days after creation. Render currently provides a 14-day grace period to upgrade after expiry before deletion. Treat the entire deployment as a temporary client preview and export or upgrade data before that deadline.

Official references: [Render Free services](https://render.com/docs/free), [Render's Free SMTP restriction](https://render.com/changelog/free-web-services-will-no-longer-allow-outbound-traffic-to-smtp-ports), [Render web-service port and TLS behavior](https://render.com/docs/web-services), [Render health checks](https://render.com/docs/health-checks), [Render's Laravel Docker guide](https://render.com/docs/deploy-php-laravel-docker), [Render Docker deployments](https://render.com/docs/docker), [Render Shell/SSH availability](https://render.com/docs/ssh), [Laravel Resend transport](https://laravel.com/docs/12.x/mail#resend-driver), and [Resend pricing](https://resend.com/pricing).
