<?php

namespace Database\Seeders;

use App\Models\FirewallRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FirewallRuleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        FirewallRule::factory(15)->create(); // 15 règles firewall fictives
    }
}