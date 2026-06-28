<?php

namespace App\Http\Controllers;

use App\Models\VpnServer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VpnServerController extends Controller
{
    /**
     * Display a listing of VPN servers.
     */
    public function index(): JsonResponse
    {
        try {
            $servers = VpnServer::orderBy('nama_sistem')->get();
            
            return response()->json([
                'success' => true,
                'data' => $servers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching VPN servers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created VPN server.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nama_sistem' => 'required|string|max:255',
                'server_location' => 'required|string|max:255',
                'ip_address' => 'required|string|max:45|unique:vpn_servers,ip_address',
                'project' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            $server = VpnServer::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'VPN server created successfully',
                'data' => $server
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating VPN server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified VPN server.
     */
    public function show($id): JsonResponse
    {
        try {
            $server = VpnServer::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $server
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching VPN server: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified VPN server.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $server = VpnServer::findOrFail($id);
            
            $validated = $request->validate([
                'nama_sistem' => 'required|string|max:255',
                'server_location' => 'required|string|max:255',
                'ip_address' => 'required|string|max:45|unique:vpn_servers,ip_address,' . $id,
                'project' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            $server->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'VPN server updated successfully',
                'data' => $server
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating VPN server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified VPN server.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $server = VpnServer::findOrFail($id);
            $server->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'VPN server deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting VPN server: ' . $e->getMessage()
            ], 500);
        }
    }
}
