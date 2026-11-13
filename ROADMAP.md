# BHA Help Ladder roadmap

Phases 1 and 2 are implemented in this repository: the public site and account foundation plus a database-backed, role-isolated appointment workflow.

## Phase 2 — Working appointments (implemented)

- [x] Database-backed services, counsellor profiles, durations, optional fees, modes, working hours, breaks, buffers and blocked dates.
- [x] Client selection, review, booking history, cancellation and rescheduling.
- [x] UTC timestamp storage with `Africa/Lagos` portal display and visible WAT labels.
- [x] Atomic overlap prevention using per-counsellor database row locks and transactions for create, reassign and reschedule; pending and confirmed statuses reserve availability.
- [x] Server-authorized transitions for pending, confirmed, cancelled, completed and no-show appointments.
- [x] Configurable notice, cancellation, rescheduling, booking-horizon and reminder rules.
- [x] Counsellor availability/assigned-booking tools and administrator service, assignment and booking tools.
- [x] Encrypted online meeting details shown only in authorized portal views.
- [x] Queued, discreet request/status/reminder emails with database-enforced reminder deduplication.
- [x] Focused PostgreSQL workflow tests plus a separate two-process PostgreSQL concurrency test for booking and rescheduling races.

## PostgreSQL and Render readiness

- [x] PostgreSQL is the documented/default application driver; Render's `DATABASE_URL` and configurable `DB_SSLMODE` are supported without committed credentials.
- [x] Portable migrations retain indexes, foreign keys, UTC timestamp conventions and validation without MySQL-only column positioning.
- [x] Create, reassignment and rescheduling writes use transactions and deterministic, ascending counsellor-profile row locks before availability checks and schedule writes.
- [x] The Render Docker runtime installs `pdo_pgsql` and the application's existing required PHP extensions, compiles Vite assets, serves `public` through Apache on `0.0.0.0:$PORT`, and runs bounded database readiness plus forward-only production migrations before startup.
- [x] Production proxy/HTTPS handling, secure cookie settings, stderr logging, database sessions/cache and a non-confidential `/up` health check are documented and configured.
- [x] Render Free appointment email uses synchronous Resend delivery over HTTPS. Delivery occurs after booking transactions and failures are logged without private content, so provider failure does not undo or disguise a successful booking.
- [x] Render Free deployment instructions cover exact dashboard settings, internal versus external PostgreSQL URLs, environment secrets and secure one-time administrator provisioning without a public setup route.
- [x] Automatic reminders are explicitly unavailable on the Free preview without a separately configured trusted scheduler; no unauthenticated scheduling endpoint was added.
- [x] CI is configured with an ephemeral PostgreSQL service and an `_test` database safety guard. The guard executes before Laravel's database-refresh traits and was verified to reject `bha_help_ladder` without running a test.
- [x] Local PostgreSQL verification completed on 13 November 2026 against `bha_help_ladder_test`: all migrations were exercised successfully; 11 focused booking/authorization/notification/reminder tests passed with 48 assertions; both real two-process concurrency tests passed with 9 assertions. Competing bookings produced one appointment and one conflict; competing reschedules moved one appointment while preserving the loser's original time.
- [x] Render-preparation verification on 13 November 2026: 19 focused PostgreSQL tests passed with 90 assertions, including both two-process races, health response privacy and mail-failure isolation. Composer validation, PHP syntax checks, command/route discovery, Laravel config/view cache generation and the production Vite build also passed.
- [ ] Build the Render Docker image and verify its installed PHP modules, including `pdo_pgsql`, in a Docker-capable environment.
- [ ] Perform a manual Render Free preview smoke test after the owner configures `APP_KEY`, the internal `DATABASE_URL`, the public `APP_URL` and a verified Resend sender. No Render resources have been created by this repository work.
- [x] Read-only MySQL inventory completed on 13 November 2026: application tables contain no records requiring transfer; the source was not changed. Repeat this check immediately before cutover.

## Phase 3 — Administration and insights

- Extend operational administration with audit history and bulk-safe workflows.
- Add clearly defined, date-filtered metrics from real queries: bookings, completed sessions, cancellations, no-shows, service demand and counsellor utilisation, including honest empty states.
- Add authorized CSV export with spreadsheet-formula injection protection.
- Audit important administrative and booking changes without recording sensitive enquiry text.
- Do not infer revenue from unpaid booking fees or show invented chart data.

## Phase 4 — Launch preparation

- Review authorization, accessibility, responsiveness, query performance and booking concurrency.
- Document production deployment, HTTPS, queue workers, scheduler, backups and restore verification.
- Add draft privacy, terms and cancellation-policy pages clearly marked for client/legal review.
- Close the remaining items in `docs/CLIENT_CONTENT_CHECKLIST.md` and record launch blockers.

## Optional future work (outside current phases)

Evaluate payments, WhatsApp/SMS, embedded video calls and calendar synchronization only after the core workflow, privacy and safeguarding decisions are approved. Do not add clinical notes, diagnoses, session recording, an AI therapist or sensitive file uploads to this product.
