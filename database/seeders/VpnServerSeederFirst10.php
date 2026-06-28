<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VpnServerSeederFirst10 extends Seeder
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
            [
                'nama_sistem' => 'CRM v1 Prod Rancher Cluster 3',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.143',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod Database',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.107',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod Minio',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.105',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod Redis',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.118',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod RabbitMQ',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.221',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CRM v1 Prod MongoDB',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.119',
                'project' => 'CRM v1 Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'File Management Alfresco',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.65',
                'project' => 'File Management',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Ticketing OSTicket',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.66',
                'project' => 'Ticketing',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'CCTV Production',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.86',
                'project' => 'CCTV Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Puspeka Development',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.182',
                'project' => 'Puspeka Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'Puspeka Production',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.185',
                'project' => 'Puspeka Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'KSBB Dinamis Development',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.236',
                'project' => 'KSBB Dinamis Dev',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_sistem' => 'KSBB Dinamis Production',
                'server_location' => 'Jakarta, Indonesia',
                'ip_address' => '192.168.3.235',
                'project' => 'KSBB Dinamis Prod',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
