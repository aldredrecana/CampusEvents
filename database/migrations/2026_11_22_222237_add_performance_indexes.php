<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Indexes make the most common searches fast. PostgreSQL does not index foreign keys automatically.
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index('starts_at');           // sorting events, upcoming lists
            $table->index('ends_at');             // "happening now", missed events
            $table->index('created_by');          // an organizer's own events
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');                               // counting students
            $table->index(['course', 'year_level', 'section']);  // event audience (expected / absent lists)
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('user_id');             // a student's My Attendance page
        });

        Schema::table('event_staff', function (Blueprint $table) {
            $table->index('user_id');             // "You are gate staff for" on the dashboard
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['ends_at']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['course', 'year_level', 'section']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('event_staff', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
