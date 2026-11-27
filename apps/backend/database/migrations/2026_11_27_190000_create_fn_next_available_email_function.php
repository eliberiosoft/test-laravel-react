<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_next_available_email');

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_next_available_email(
                p_local_part VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                p_domain VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            )
            RETURNS VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            NOT DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_highest_suffix INT UNSIGNED DEFAULT NULL;

                IF NOT EXISTS (SELECT 1 FROM users WHERE email = CONCAT(p_local_part, '@', p_domain)) THEN
                    RETURN CONCAT(p_local_part, '@', p_domain);
                END IF;

                SELECT MAX(CAST(SUBSTRING(
                           email,
                           CHAR_LENGTH(p_local_part) + 1,
                           CHAR_LENGTH(email) - CHAR_LENGTH(p_local_part) - CHAR_LENGTH(p_domain) - 1
                       ) AS UNSIGNED))
                  INTO v_highest_suffix
                  FROM users
                 WHERE email LIKE CONCAT(p_local_part, '%@', p_domain)
                   AND email REGEXP CONCAT('^', p_local_part, '[0-9]+@', REPLACE(p_domain, '.', '\\.'), '$');

                RETURN CONCAT(p_local_part, COALESCE(v_highest_suffix, 0) + 1, '@', p_domain);
            END
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_next_available_email');
    }
};
