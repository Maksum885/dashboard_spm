<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Move any existing viewer accounts to operator so we don't violate the new constraint
        DB::table('users')->where('role', 'viewer')->update(['role' => 'operator']);

        // SQL Server: find and drop the old check constraint that includes 'viewer', then add a new one
        DB::statement("
            DECLARE @cn NVARCHAR(200);
            SELECT @cn = name
            FROM sys.check_constraints
            WHERE parent_object_id = OBJECT_ID('users')
              AND definition LIKE '%role%';
            IF @cn IS NOT NULL
                EXEC('ALTER TABLE [users] DROP CONSTRAINT [' + @cn + ']');
        ");

        DB::statement("ALTER TABLE [users] ADD CONSTRAINT [users_role_check] CHECK ([role] IN ('admin', 'operator'))");
    }

    public function down(): void
    {
        DB::statement("
            IF EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'users_role_check' AND parent_object_id = OBJECT_ID('users'))
                ALTER TABLE [users] DROP CONSTRAINT [users_role_check];
        ");

        DB::statement("ALTER TABLE [users] ADD CONSTRAINT [users_role_check] CHECK ([role] IN ('admin', 'operator', 'viewer'))");
    }
};
