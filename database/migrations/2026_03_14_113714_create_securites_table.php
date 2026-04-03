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
        Schema::create('securites', function (Blueprint $table) {
            $table->id();
            $table->string('nom_evenement');
            $table->enum('type', ['intrusion', 'tentative_connexion', 'alerte_firewall', 'mise_a_jour', 'scan_port', 'ddos', 'autre']);
            $table->enum('severite', ['info', 'faible', 'moyenne', 'haute', 'critique'])->default('info');
            $table->enum('statut', ['nouveau', 'en_cours', 'resolu', 'ignore'])->default('nouveau');
            $table->string('source_ip')->nullable();
            $table->string('destination_ip')->nullable();
            $table->integer('port_source')->nullable();
            $table->integer('port_destination')->nullable();
            $table->string('protocole')->nullable(); // TCP, UDP, ICMP, etc.
            $table->foreignId('routeur_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('interface_id')->nullable()->constrained('interface_models')->nullOnDelete();
            $table->text('description');
            $table->text('action_entreprise')->nullable();
            $table->json('donnees_brutes')->nullable(); // pour stocker les logs complets
            $table->integer('compteur')->default(1); // nombre d'occurrences
            $table->boolean('est_bloque')->default(false);
            $table->timestamp('resolu_a')->nullable();
            $table->foreignId('resolu_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index(['type', 'severite', 'statut']);
            $table->index('source_ip');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('securites');
    }
};