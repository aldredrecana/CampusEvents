<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Official student list from the registrar, imported by an admin from CSV
    public function up(): void
    {
        Schema::create('masterlist_students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('course');
            $table->unsignedTinyInteger('year_level');
            $table->string('section', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masterlist_students');
    }
};
