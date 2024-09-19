<?php

namespace App\Http\Controllers;

use App\Models\PmdCnetFfu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PmdCnetFfuController extends Controller
{
    public function index(Request $request)
    {
        $parents = PmdCnetFfu::distinct()->pluck('Parent');
        $selectedParent = $request->query('parent');
        $selectedNetwork = $request->query('network');
        $selectedPort = $request->query('port');
        $selectedEquipment = $request->query('equipment');

        Log::info('Index method called', [
            'parents_count' => $parents->count(),
            'selectedParent' => $selectedParent,
            'selectedNetwork' => $selectedNetwork,
            'selectedPort' => $selectedPort,
            'selectedEquipment' => $selectedEquipment
        ]);

        return view('pmd_cnet_ffu.index', compact('parents', 'selectedParent', 'selectedNetwork', 'selectedPort', 'selectedEquipment'));
    }

    public function getNetworks(Request $request)
    {
        Log::info('getNetworks method called', ['parent' => $request->parent]);

        $networks = PmdCnetFfu::where('Parent', $request->parent)
            ->distinct()
            ->pluck('Network');

        Log::info('Networks retrieved', ['count' => $networks->count()]);

        return response()->json($networks);
    }

    public function getPorts(Request $request)
    {
        Log::info('getPorts method called', ['parent' => $request->parent, 'network' => $request->network]);

        $ports = PmdCnetFfu::where('Parent', $request->parent)
            ->where('Network', $request->network)
            ->distinct()
            ->pluck('Port');

        Log::info('Ports retrieved', ['count' => $ports->count()]);

        return response()->json($ports);
    }

    public function getEquipment(Request $request)
    {
        Log::info('getEquipment method called', [
            'parent' => $request->parent,
            'network' => $request->network,
            'port' => $request->port
        ]);

        $equipment = PmdCnetFfu::where('Parent', $request->parent)
            ->where('Network', $request->network)
            ->where('Port', $request->port)
            ->get(['Equipment', 'Equipment', 'Network', 'Port', 'CNX_Sequence'])
            ->sortBy(function ($item) {
                return (int) $item->CNX_Sequence;
            })
            ->values();

        Log::info('Equipment retrieved', ['count' => $equipment->count()]);

        return response()->json($equipment);
    }

    public function equipmentSearch()
    {
        Log::info('equipmentSearch method called');
        return view('pmd_cnet_ffu.equipment_search');
    }

    public function equipmentSearchResults(Request $request)
    {
        $query = $request->input('query');
        Log::info('equipmentSearchResults method called', ['query' => $query]);

        $results = PmdCnetFfu::where('Equipment', 'LIKE', "%{$query}%")
            ->orWhere('Parent', 'LIKE', "%{$query}%")
            ->orWhere('Network', 'LIKE', "%{$query}%")
            ->orWhere('Port', 'LIKE', "%{$query}%")
            ->get(['Equipment', 'Equipment', 'Parent', 'Network', 'Port']);

        Log::info('Search results retrieved', ['count' => $results->count()]);
        
        return response()->json($results);
    }
}