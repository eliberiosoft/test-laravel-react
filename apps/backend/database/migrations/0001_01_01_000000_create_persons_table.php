<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 60);
            $table->string('middle_name', 60)->nullable();
            $table->string('first_surname', 60);
            $table->string('second_surname', 60)->nullable();
            $table->char('identification', 10)->unique();
            $table->date('birth_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['first_surname', 'second_surname', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
