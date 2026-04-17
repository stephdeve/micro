<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Rendre receiver_id nullable pour les messages de groupe
            $table->dropForeign(['receiver_id']);
            $table->foreignId('receiver_id')->nullable()->change();
            $table->foreign('receiver_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['receiver_id']);
            $table->foreignId('receiver_id')->change();
            $table->foreign('receiver_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
