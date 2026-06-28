<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VpnServerSeederClean extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vpn_servers')->delete(); // Clear existing data
        
        DB::table('vpn_servers')->insert([
            [
                'nama_sistem' => 'CRM Dev Docker Swarm',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.140',
                'project' => 'CRM v1 Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Jaki Dev Database MySQL',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.141',
                'project' => 'Jaki Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Staging Rancher Cluster 1',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.142',
                'project' => 'CRM v1 Staging',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod Rancher Cluster 1',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.143',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'File Management Alfresco',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.144',
                'project' => 'File Management',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Ticketing OSTicket',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.145',
                'project' => 'Ticketing',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CCTV Production',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.146',
                'project' => 'CCTV Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Puspeka Development',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.147',
                'project' => 'Puspeka Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Puspeka Production',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.148',
                'project' => 'Puspeka Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
