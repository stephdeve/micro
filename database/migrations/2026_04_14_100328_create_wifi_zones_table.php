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
        Schema::create('wifi_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('ssid');
            $table->string('password')->nullable();
            $table->string('security_profile')->default('default');
            $table->integer('bandwidth_down')->default(0); // Mbps
            $table->integer('bandwidth_up')->default(0); // Mbps
            $table->bigInteger('quota_monthly')->default(0); // MB, 0 = illimité
            $table->integer('vlan_id')->nullable();
            $table->string('schedule_start')->nullable(); // HH:MM
            $table->string('schedule_end')->nullable(); // HH:MM
            $table->json('schedule_days')->nullable(); // [1,2,3,4,5] = lun-ven
            $table->boolean('client_isolation')->default(true);
            $table->integer('max_clients')->default(50);
            $table->string('frequency_band')->default('2.4ghz-g'); // 2.4ghz-g, 5ghz-a
            $table->string('wifi_interface_name')->nullable(); // Référence interface MikroTik
            $table->boolean('active')->default(true);
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifi_zones');
    }
};
