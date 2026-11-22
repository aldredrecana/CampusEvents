<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            return; // already added by the earlier add_role_to_users_table migration
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('email');
        });
    }

    public function down(): void
    {
        // Intentionally empty: the earlier migration owns the role column.
    }
};
