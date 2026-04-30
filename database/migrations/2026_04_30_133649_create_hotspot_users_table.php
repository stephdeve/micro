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
        Schema::create('hotspot_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained('routeurs')->onDelete('cascade');
            $table->foreignId('profile_id')->nullable()->constrained('hotspot_profiles')->onDelete('set null');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('mac_address')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('nom_complet')->nullable();
            $table->enum('type', ['voucher', 'employe', 'invite', 'permanent'])->default('employe');
            $table->integer('data_limit')->nullable(); // en MB
            $table->string('time_limit')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->text('commentaire')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamp('last_login')->nullable();
            $table->string('total_uptime')->nullable();
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->string('mikrotik_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_users');
    }
};
