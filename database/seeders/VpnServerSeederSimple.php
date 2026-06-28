<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VpnServerSeederSimple extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vpn_servers')->insert([
            [
                'nama_sistem' => 'Jaki Dev Docker Swarm 1',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.130',
                'project' => 'Jaki Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Jaki Dev Database MySQL',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.131',
                'project' => 'Jaki Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Jaki Prod Docker Swarm 1',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.132',
                'project' => 'Jaki Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM Dev Docker Swarm',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.133',
                'project' => 'CRM Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Website Corona Prod App',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.134',
                'project' => 'Website Corona Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
