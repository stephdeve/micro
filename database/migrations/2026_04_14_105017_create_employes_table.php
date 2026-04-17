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
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained()->onDelete('cascade');
            $table->foreignId('wifi_zone_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Lien vers compte utilisateur
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('matricule')->nullable()->unique();
            $table->string('departement')->nullable();
            $table->string('poste')->nullable();
            $table->string('mac_address')->nullable(); // Adresse MAC de son appareil
            $table->string('ip_address')->nullable(); // IP attribuée
            $table->integer('bandwidth_down')->default(0); // Mbps personnalisé (0 = hérite de la zone)
            $table->integer('bandwidth_up')->default(0); // Mbps personnalisé
            $table->bigInteger('quota_monthly')->default(0); // MB personnalisé
            $table->bigInteger('data_used_this_month')->default(0); // Consommation actuelle
            $table->bigInteger('data_used_total')->default(0); // Consommation totale
            $table->timestamp('last_connected_at')->nullable();
            $table->integer('connection_duration_minutes')->default(0); // Durée totale
            $table->boolean('active')->default(true); // Bloquer/Débloquer
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
