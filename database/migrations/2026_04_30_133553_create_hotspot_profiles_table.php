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
        Schema::create('hotspot_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routeur_id')->constrained('routeurs')->onDelete('cascade');
            $table->string('nom');
            $table->integer('shared_users')->default(1);
            $table->string('rate_limit')->nullable();
            $table->string('session_timeout')->nullable();
            $table->string('idle_timeout')->nullable()->default('00:05:00');
            $table->string('keepalive_timeout')->nullable()->default('00:02:00');
            $table->string('status_autorefresh')->nullable()->default('00:01:00');
            $table->string('mac_cookie_timeout')->nullable()->default('3d');
            $table->boolean('transparent_proxy')->default(false);
            $table->boolean('radius_accounting')->default(false);
            $table->boolean('open_status_page')->default(true);
            $table->boolean('advertise')->default(false);
            $table->string('advertise_timeout')->nullable();
            $table->text('advertise_url')->nullable();
            $table->boolean('active')->default(true);
            $table->text('commentaire')->nullable();
            $table->string('mikrotik_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_profiles');
    }
};
