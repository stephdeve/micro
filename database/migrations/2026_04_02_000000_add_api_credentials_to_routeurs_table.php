<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routeurs', function (Blueprint $table) {
            $table->string('api_user')->nullable()->after('adresse_ip');
            $table->string('api_password')->nullable()->after('api_user');
        });
    }

    public function down(): void
    {
        Schema::table('routeurs', function (Blueprint $table) {
            $table->dropColumn(['api_user', 'api_password']);
        });
    }
};
