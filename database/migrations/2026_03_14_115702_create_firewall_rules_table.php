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
        Schema::create('firewall_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained()->cascadeOnDelete();
            $table->integer('numero_ordre');
            $table->string('nom');
            $table->enum('action', ['accept', 'drop', 'reject', 'jump', 'log'])->default('accept');
            $table->enum('chain', ['input', 'output', 'forward', 'prerouting', 'postrouting'])->default('forward');
            $table->string('protocole')->nullable(); // tcp, udp, icmp, etc. ou null pour tous
            $table->string('src_address')->nullable();
            $table->string('dst_address')->nullable();
            $table->integer('src_port')->nullable();
            $table->integer('dst_port')->nullable();
            $table->string('in_interface')->nullable();
            $table->string('out_interface')->nullable();
            $table->string('connection_state')->nullable(); // new, established, related, invalid
            $table->boolean('est_active')->default(true);
            $table->text('description')->nullable();
            $table->json('configuration_complete')->nullable(); // pour stocker la règle complète
            $table->bigInteger('compteur_paquets')->default(0);
            $table->bigInteger('compteur_octets')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['routeur_id', 'est_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firewall_rules');
    }
};