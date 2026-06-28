<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VpnServerSeederUnique extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vpn_servers')->delete(); // Clear existing data
        
        // Only use first 10 unique entries from original seeder
        DB::table('vpn_servers')->insert([
            [
                'nama_sistem' => 'CRM Dev Docker Swarm',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.130',
                'project' => 'CRM v1 Dev',
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
                'nama_sistem' => 'CRM v1 Staging Rancher Cluster 1',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.127',
                'project' => 'CRM v1 Staging',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Staging Rancher Cluster 2',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.141',
                'project' => 'CRM v1 Staging',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Staging Database',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.106',
                'project' => 'CRM v1 Staging',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Staging Minio',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.105',
                'project' => 'CRM v1 Staging',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Staging Redis',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.118',
                'project' => 'CRM v1 Staging',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod Rancher Cluster 1',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.224',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod Rancher Cluster 2',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.142',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
