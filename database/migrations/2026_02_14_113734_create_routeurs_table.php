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
        Schema::create('routeurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('modele')->nullable();
            $table->string('adresse_ip')->unique();
            $table->string('adresse_mac')->nullable()->unique();
            $table->string('version_ros')->nullable(); // RouterOS version
            $table->string('firmware')->nullable();
            $table->string('numero_serie')->nullable()->unique();
            $table->enum('statut', ['en_ligne', 'hors_ligne', 'maintenance'])->default('hors_ligne');
            $table->integer('uptime')->nullable(); // en secondes
            $table->float('cpu_usage')->nullable(); // pourcentage
            $table->float('memory_usage')->nullable(); // pourcentage
            $table->float('temperature')->nullable(); // en degrés
            $table->string('emplacement')->nullable();
            $table->text('description')->nullable();
            $table->json('configuration')->nullable(); // pour stocker la config JSON
            $table->datetime('derniere_connexion')->nullable();
            $table->datetime('derniere_sync')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // utilisateur responsable
            $table->timestamps();
            $table->softDeletes(); // pour archivage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routeurs');
    }
};