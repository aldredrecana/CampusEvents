<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'event_id')) {
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('attendances', 'user_id')) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('attendances', 'checked_in_at')) {
                $table->dateTime('checked_in_at')->nullable();
            }
            if (! Schema::hasColumn('attendances', 'checked_out_at')) {
                $table->dateTime('checked_out_at')->nullable();
            }
            if (! Schema::hasColumn('attendances', 'status')) {
                $table->string('status')->default('present');
            }
            if (! Schema::hasColumn('attendances', 'scanned_by')) {
                $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('attendances', 'source')) {
                $table->string('source')->default('qr');
            }
            if (! Schema::hasColumn('attendances', 'note')) {
                $table->text('note')->nullable();
            }
        });

        if (! Schema::hasIndex('attendances', ['event_id', 'user_id'], 'unique')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unique(['event_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: this migration only repairs a broken table.
    }
};