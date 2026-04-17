<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'encrypted_content')) {
                $table->text('encrypted_content')->nullable()->after('content');
            }
            if (!Schema::hasColumn('messages', 'encryption_iv')) {
                $table->string('encryption_iv', 255)->nullable()->after('encrypted_content');
            }
            if (!Schema::hasColumn('messages', 'encryption_key_hash')) {
                $table->string('encryption_key_hash', 255)->nullable()->after('encryption_iv');
            }
            if (!Schema::hasColumn('messages', 'is_group_message')) {
                $table->boolean('is_group_message')->default(false)->after('encryption_key_hash');
            }
            if (!Schema::hasColumn('messages', 'is_encrypted')) {
                $table->boolean('is_encrypted')->default(true)->after('is_group_message');
            }
            if (!Schema::hasColumn('messages', 'message_type')) {
                $table->string('message_type', 20)->default('text')->after('is_encrypted');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['encrypted_content', 'encryption_iv', 'encryption_key_hash', 'is_group_message', 'is_encrypted', 'message_type']);
        });
    }
};
