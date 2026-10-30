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
    Schema::table('events', function (Blueprint $table) {
        $table->json('target_courses')->nullable();
        $table->json('target_year_levels')->nullable();
        $table->json('target_sections')->nullable();
        $table->boolean('requires_checkout')->default(false);
        $table->unsignedSmallInteger('grace_minutes')->default(15);
    });
}

public function down(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->dropColumn([
            'target_courses', 'target_year_levels', 'target_sections',
            'requires_checkout', 'grace_minutes',
        ]);
    });
}
};
