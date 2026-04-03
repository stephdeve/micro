<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('messages', 'tag')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('tag')->nullable()->default('reseau')->after('folder');
                $table->index('tag');
            });
        }
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['tag']);
            $table->dropColumn('tag');
        });
    }
};
