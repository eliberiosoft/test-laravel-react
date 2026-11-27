<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->char('file_hash', 64);
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('created_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->string('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('line');
            $table->string('status', 20);
            $table->string('identification', 50)->nullable();
            $table->string('email', 120)->nullable();
            $table->json('errors')->nullable();

            $table->index(['user_import_id', 'line']);
            $table->index(['user_import_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_import_rows');
        Schema::dropIfExists('user_imports');
    }
};
