<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Rendre tous les champs legacy nullable (car on utilise encrypted_content maintenant)
            if (Schema::hasColumn('messages', 'content')) {
                $table->text('content')->nullable()->change();
            }
            if (Schema::hasColumn('messages', 'subject')) {
                $table->string('subject')->nullable()->change();
            }
            if (Schema::hasColumn('messages', 'receiver_id')) {
                $table->dropForeign(['receiver_id']);
                $table->foreignId('receiver_id')->nullable()->change();
                $table->foreign('receiver_id')->references('id')->on('users')->nullOnDelete();
            }
            // Champs pour l'encryption
            if (Schema::hasColumn('messages', 'encrypted_content')) {
                $table->text('encrypted_content')->nullable()->change();
            }
            if (Schema::hasColumn('messages', 'encryption_iv')) {
                $table->string('encryption_iv', 255)->nullable()->change();
            }
            if (Schema::hasColumn('messages', 'encryption_key_hash')) {
                $table->string('encryption_key_hash', 255)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->text('content')->change();
            $table->string('subject')->change();
            $table->dropForeign(['receiver_id']);
            $table->foreignId('receiver_id')->change();
            $table->foreign('receiver_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
