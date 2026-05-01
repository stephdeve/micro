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
        Schema::table('wifi_zones', function (Blueprint $table) {
            // IP de la zone (pour le réseau et les queues)
            $table->string('network_address')->nullable()->after('vlan_id'); // ex: 192.168.20.0/24
            $table->string('gateway')->nullable()->after('network_address'); // ex: 192.168.20.1
            
            // Bande passante par personne (par utilisateur)
            $table->integer('per_user_down')->default(0)->after('bandwidth_up'); // Mbps par personne
            $table->integer('per_user_up')->default(0)->after('per_user_down'); // Mbps par personne
            
            // Pool DHCP pour la zone
            $table->string('dhcp_pool_start')->nullable()->after('gateway'); // ex: 192.168.20.10
            $table->string('dhcp_pool_end')->nullable()->after('dhcp_pool_start'); // ex: 192.168.20.254
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wifi_zones', function (Blueprint $table) {
            $table->dropColumn([
                'network_address',
                'gateway',
                'per_user_down',
                'per_user_up',
                'dhcp_pool_start',
                'dhcp_pool_end'
            ]);
        });
    }
};
