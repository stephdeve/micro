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
        Schema::create('statistiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interface_id')->nullable()->constrained('interface_models')->cascadeOnDelete();
            $table->timestamp('timestamp');
            $table->string('type'); // cpu, memory, traffic, etc.
            $table->float('valeur');
            $table->string('unite')->nullable();
            $table->json('donnees_complementaires')->nullable();
            $table->timestamps();

            // Index pour les requêtes de statistiques
            $table->index(['routeur_id', 'type', 'timestamp']);
            $table->index(['interface_id', 'type', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistiques');
    }
};