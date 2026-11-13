<?php

return [
    'timezone' => 'Africa/Lagos',
    'slot_interval_minutes' => (int) env('BOOKING_SLOT_INTERVAL_MINUTES', 30),
    'booking_horizon_days' => (int) env('BOOKING_HORIZON_DAYS', 90),
    'minimum_notice_hours' => (int) env('BOOKING_MINIMUM_NOTICE_HOURS', 4),
    'cancellation_notice_hours' => (int) env('BOOKING_CANCELLATION_NOTICE_HOURS', 24),
    'reschedule_notice_hours' => (int) env('BOOKING_RESCHEDULE_NOTICE_HOURS', 24),
    'reminder_lead_hours' => (int) env('BOOKING_REMINDER_LEAD_HOURS', 24),
];
