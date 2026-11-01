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
    Schema::table('attendances', function (Blueprint $table) {
        $table->string('status')->default('present'); // present | late
        $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
        $table->string('source')->default('qr');      // qr | manual
        $table->text('note')->nullable();
    });
}

public function down(): void
{
    Schema::table('attendances', function (Blueprint $table) {
        $table->dropConstrainedForeignId('scanned_by');
        $table->dropColumn(['status', 'source', 'note']);
    });
}
};
