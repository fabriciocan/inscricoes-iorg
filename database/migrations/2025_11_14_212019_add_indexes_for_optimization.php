<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // email already has unique index, but ensuring it's optimized for lookups
            // No additional index needed as unique() already creates an index
        });

        Schema::table('packages', function (Blueprint $table) {
            // package_number already has unique index
            // user_id already has foreign key index
            // Adding composite index for common query patterns
            $table->index(['user_id', 'status'], 'packages_user_status_index');
            $table->index('status', 'packages_status_index');
        });

        Schema::table('registrations', function (Blueprint $table) {
            // package_id and event_id already have foreign key indexes
            // Adding composite index for common query patterns
            $table->index(['event_id', 'package_id'], 'registrations_event_package_index');
            $table->index('participant_email', 'registrations_participant_email_index');
        });

        Schema::table('payment_batches', function (Blueprint $table) {
            // event_id already has foreign key index
            // Adding composite index for date range queries
            $table->index(['event_id', 'start_date', 'end_date'], 'payment_batches_event_dates_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // No indexes to drop
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('packages_user_status_index');
            $table->dropIndex('packages_status_index');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('registrations_event_package_index');
            $table->dropIndex('registrations_participant_email_index');
        });

        Schema::table('payment_batches', function (Blueprint $table) {
            $table->dropIndex('payment_batches_event_dates_index');
        });
    }
};
