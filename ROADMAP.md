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
- [x] Focused SQLite workflow tests plus a separate two-process MySQL/InnoDB concurrency test.

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
