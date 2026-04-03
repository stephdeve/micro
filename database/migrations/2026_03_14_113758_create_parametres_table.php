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
        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->text('valeur')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, etc.
            $table->string('groupe')->default('general'); // general, reseau, securite, notifications, api
            $table->string('libelle')->nullable();
            $table->text('description')->nullable();
            $table->boolean('est_modifiable')->default(true);
            $table->boolean('est_visible')->default(true);
            $table->json('options')->nullable(); // pour les valeurs possibles (select)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};