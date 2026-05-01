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
        Schema::create('bandwidth_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained()->onDelete('cascade');
            $table->string('nom'); // Direction, Techniciens, Stagiaires, Invités
            $table->text('description')->nullable();
            $table->integer('download_mbps')->default(10); // Mbps
            $table->integer('upload_mbps')->default(5); // Mbps
            $table->integer('quota_gb')->nullable(); // Go, null = illimité
            $table->string('target_network')->nullable(); // ex: 192.168.10.0/24
            $table->integer('priority')->default(8); // 1-8, 1 = plus prioritaire
            $table->boolean('active')->default(true);
            $table->string('color')->default('blue'); // Pour l'UI: blue, emerald, amber, rose, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bandwidth_profiles');
    }
};
