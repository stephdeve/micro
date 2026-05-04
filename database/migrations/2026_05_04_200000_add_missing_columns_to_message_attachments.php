<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('message_attachments', function (Blueprint $table) {
            $table->string('stored_filename')->nullable()->after('original_filename');
            $table->string('mime_type')->nullable()->after('file_type');
            $table->string('file_hash', 64)->nullable()->after('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_attachments', function (Blueprint $table) {
            $table->dropColumn(['stored_filename', 'mime_type', 'file_hash']);
        });
    }
};
