<?php

namespace App\Http\Controllers;

use App\Models\PmdCnetFfu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\FfuJsonEmailService;

class PmdCnetFfuController extends Controller
{
    protected $ffuJsonEmailService;

    public function __construct(FfuJsonEmailService $ffuJsonEmailService)
    {
        $this->ffuJsonEmailService = $ffuJsonEmailService;
    }

    public function index(Request $request)
    {
        $parents = PmdCnetFfu::distinct()->pluck('Parent');
        $selectedParent = $request->query('Parent');
        $selectedNetwork = $request->query('Network');
        $selectedPort = $request->query('Port');
        $selectedEquipment = $request->query('Equipment');

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

        if (!$request->parent) {
            return response()->json(['error' => 'Parent is required'], 400);
        }

        $networks = PmdCnetFfu::where('Parent', $request->parent)
            ->distinct()
            ->pluck('Network');

        Log::info('Networks retrieved', ['count' => $networks->count()]);

        return response()->json($networks);
    }

    public function getPorts(Request $request)
    {
        Log::info('getPorts method called', ['parent' => $request->parent, 'network' => $request->network]);

        if (!$request->parent || !$request->network) {
            return response()->json(['error' => 'Parent and Network are required'], 400);
        }

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

        if (!$request->parent || !$request->network || !$request->port) {
            return response()->json(['error' => 'Parent, Network, and Port are required'], 400);
        }

        $equipment = PmdCnetFfu::where('Parent', $request->parent)
            ->where('Network', $request->network)
            ->where('Port', $request->port)
            ->get(['Equipment', 'Network', 'Port', 'CNX_Sequence'])
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

        if (!$query) {
            return response()->json(['error' => 'Search query is required'], 400);
        }

        $results = PmdCnetFfu::where('Equipment', 'LIKE', "%{$query}%")
            ->orWhere('Parent', 'LIKE', "%{$query}%")
            ->orWhere('Network', 'LIKE', "%{$query}%")
            ->orWhere('Port', 'LIKE', "%{$query}%")
            ->get(['Equipment', 'Parent', 'Network', 'Port']);

        Log::info('Search results retrieved', ['count' => $results->count()]);
        
        return response()->json($results);
    }

    public function edit(Request $request)
    {
        $parent = $request->query('parent');
        $network = $request->query('network');
        $port = $request->query('port');
        $equipment = $request->query('equipment');

        Log::info('Edit method called', [
            'parent' => $parent,
            'network' => $network,
            'port' => $port,
            'equipment' => $equipment
        ]);

        if (!$parent || !$network || !$port || !$equipment) {
            return redirect()->route('pmd_cnet_ffu.index')->with('error', 'Missing required parameters');
        }

        $equipmentItem = PmdCnetFfu::where('Equipment', $equipment)
            ->where('Parent', $parent)
            ->where('Network', $network)
            ->where('Port', $port)
            ->firstOrFail();

        return view('pmd_cnet_ffu.edit', compact('equipmentItem'));
    }

    public function update(Request $request, $equipment)
    {
        Log::info('Update method called', ['equipment' => $equipment]);

        $equipmentItem = PmdCnetFfu::findOrFail($equipment);

        $validatedData = $request->validate([
            'Equipment' => 'required|string',
            'Parent' => 'required|string',
            'Network' => 'required|string',
            'Port' => 'required|string',
            'CNX_Sequence' => 'required|integer',
            'Comment' => 'nullable|string',
        ]);

        $equipmentItem->update($validatedData);

        Log::info('Equipment updated', ['equipment' => $equipment]);

        // Save all attributes to JSON file
        $this->ffuJsonEmailService->saveToJson($equipmentItem->toArray());

        return redirect()->route('pmd_cnet_ffu.index')->with('success', 'Equipment updated successfully');
    }

    public function checkJsonFile()
    {
        $hasData = $this->ffuJsonEmailService->jsonFileHasData();
        return response()->json(['hasData' => $hasData]);
    }

    public function sendEmail()
    {
        $result = $this->ffuJsonEmailService->sendEmailAndClearJson();
        return response()->json(['success' => $result]);
    }
}
