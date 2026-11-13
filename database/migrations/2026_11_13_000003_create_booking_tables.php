<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('buffer_before_minutes')->default(0);
            $table->unsignedSmallInteger('buffer_after_minutes')->default(0);
            $table->unsignedInteger('fee_minor')->nullable();
            $table->char('currency', 3)->default('NGN');
            $table->json('modes');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('counsellor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('counsellor_profile_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counsellor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['counsellor_profile_id', 'service_id'], 'counsellor_service_unique');
        });

        Schema::create('availability_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counsellor_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();
            $table->index(['counsellor_profile_id', 'weekday'], 'availability_day_index');
        });

        Schema::create('availability_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counsellor_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();
            $table->index(['counsellor_profile_id', 'weekday'], 'break_day_index');
        });

        Schema::create('blocked_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counsellor_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at_utc');
            $table->timestamp('ends_at_utc');
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['counsellor_profile_id', 'starts_at_utc', 'ends_at_utc'], 'blocked_period_index');
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('counsellor_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('status', 24)->index();
            $table->string('session_mode', 24);
            $table->timestamp('starts_at_utc');
            $table->timestamp('ends_at_utc');
            $table->timestamp('buffer_starts_at_utc');
            $table->timestamp('buffer_ends_at_utc');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('buffer_before_minutes')->default(0);
            $table->unsignedSmallInteger('buffer_after_minutes')->default(0);
            $table->unsignedInteger('fee_minor')->nullable();
            $table->char('currency', 3)->default('NGN');
            $table->text('online_meeting_details')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rescheduled_at')->nullable();
            $table->timestamps();

            $table->index(
                ['counsellor_profile_id', 'status', 'buffer_starts_at_utc', 'buffer_ends_at_utc'],
                'appointment_overlap_index'
            );
            $table->index(['client_id', 'starts_at_utc'], 'client_appointment_index');
        });

        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 32);
            $table->timestamp('queued_at');
            $table->timestamps();
            $table->unique(['appointment_id', 'kind'], 'appointment_reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('blocked_periods');
        Schema::dropIfExists('availability_breaks');
        Schema::dropIfExists('availability_rules');
        Schema::dropIfExists('counsellor_profile_service');
        Schema::dropIfExists('counsellor_profiles');
        Schema::dropIfExists('services');
    }
};
