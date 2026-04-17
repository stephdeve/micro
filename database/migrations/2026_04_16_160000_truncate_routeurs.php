<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('routeurs')->delete();
        DB::table('interface_models')->delete();
    }

    public function down(): void
    {
        // Cannot restore deleted data
    }
};
