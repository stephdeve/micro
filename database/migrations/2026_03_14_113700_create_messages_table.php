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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->text('content');
            $table->enum('priority', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_secure')->default(true);
            $table->string('encryption_method')->default('AES-256-GCM');
            $table->boolean('has_attachments')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->string('folder')->default('inbox'); // inbox, sent, drafts, trash
            $table->json('metadata')->nullable(); // pour stocker des infos supplémentaires
            $table->foreignId('parent_id')->nullable()->constrained('messages')->nullOnDelete(); // pour les réponses
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index(['receiver_id', 'is_read', 'folder']);
            $table->index(['sender_id', 'folder']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};