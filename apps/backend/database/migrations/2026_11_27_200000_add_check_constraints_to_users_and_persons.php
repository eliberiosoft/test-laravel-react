<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_status CHECK (status IN ('active', 'blocked', 'inactive'))");
        DB::statement("ALTER TABLE persons ADD CONSTRAINT chk_persons_identification CHECK (identification REGEXP '^[0-9]{10}$')");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE persons DROP CHECK chk_persons_identification');
        DB::statement('ALTER TABLE users DROP CHECK chk_users_status');
    }
};
