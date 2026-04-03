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
        Schema::create('interface_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('type'); // ethernet, wifi, bridge, vlan, etc.
            $table->string('adresse_mac')->nullable();
            $table->string('adresse_ip')->nullable();
            $table->string('mask')->nullable();
            $table->string('vlan_id')->nullable();
            $table->string('parent_interface')->nullable(); // pour les VLANs
            $table->enum('statut', ['actif', 'inactif', 'erreur'])->default('inactif');
            $table->boolean('est_active')->default(false);
            $table->bigInteger('rx_bytes')->default(0); // octets reçus
            $table->bigInteger('tx_bytes')->default(0); // octets envoyés
            $table->bigInteger('rx_packets')->default(0); // paquets reçus
            $table->bigInteger('tx_packets')->default(0); // paquets envoyés
            $table->bigInteger('rx_errors')->default(0); // erreurs réception
            $table->bigInteger('tx_errors')->default(0); // erreurs envoi
            $table->bigInteger('rx_drops')->default(0); // paquets perdus
            $table->bigInteger('tx_drops')->default(0); // paquets perdus
            $table->float('debit_entrant')->nullable(); // en Mbps
            $table->float('debit_sortant')->nullable(); // en Mbps
            $table->string('ssid')->nullable(); // pour WiFi
            $table->string('bande')->nullable(); // 2.4GHz, 5GHz
            $table->integer('canal')->nullable(); // canal WiFi
            $table->integer('puissance_signal')->nullable(); // dBm
            $table->integer('clients_connectes')->nullable(); // pour WiFi
            $table->text('description')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index(['routeur_id', 'statut']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interface_models');
    }
};