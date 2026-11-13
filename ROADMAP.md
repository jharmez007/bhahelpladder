# BHA Help Ladder roadmap

Phase 1 is implemented in this repository: public pages, built-in authentication and verification, role-isolated dashboard shells, secure administrator provisioning, and persisted rate-limited general enquiries.

## Phase 2 — Working appointments

- Add database-backed services, counsellor profiles, durations, fees, modes, working hours, breaks, buffers and blocked dates.
- Let clients choose a service, in-person/online mode, counsellor and available time, then review and submit.
- Store timestamps in UTC and display them in `Africa/Lagos` with a visible timezone label.
- Prevent overlaps atomically in database transactions under simultaneous requests; define which statuses reserve availability.
- Implement authorized transitions for pending, confirmed, cancelled, completed and no-show appointments.
- Add client self-service cancellation/rescheduling rules, counsellor availability and assigned-booking tools, and administrator assignment/booking tools.
- Show staff-entered online meeting details only to the relevant client and counsellor.
- Queue discreet confirmation, status and reminder emails; prevent duplicate reminders.

## Phase 3 — Administration and insights

- Manage services, counsellors, enquiries and appointments.
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
