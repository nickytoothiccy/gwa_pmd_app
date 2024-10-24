<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function liveSearch(Request $request)
    {
        $query = $request->input('query');

        $equipmentList = DB::table('PMD_Tags')
            ->where('Equipment', 'LIKE', "%{$query}%")
            ->distinct()
            ->pluck('Equipment');

        return response()->json($equipmentList);
    }

    public function search(Request $request)
    {
        $equipment = $request->input('equipment');

        $results = DB::table('PMD_Tags')
            ->where('Equipment', $equipment)
            ->select(
                'Tag_ID', 'Description', 'Equipment', 'Drawing', 'Source', 'Edit', 'Parent PLC', 'Panel Name',
                'ISA Type', 'IO Type', 'Drop', 'Slot', 'Channel', 'Address', 'Register', 'Min', 'Max',
                'Eng Unit', 'Wire Type', 'Power Required', 'Process Tap Required', 'Device Tag Required',
                'Building', 'Location (F/G/C)', 'Data Term Cabinet', 'Data Term Cabinet Location (BF/C/G)',
                'Valve Size', 'Valve CV', 'Index Sheet', 'WM Submittal', 'Client Submittal', 'Submittal Status',
                'Index Specific 1', 'Index Specific 2', 'Index Specific 3', 'Index Specific 4', 'Index Specific 5',
                'Index Specific Note', 'Serial Number', 'PO Number', 'PO Line Number', 'Quantity', 'Supplied By',
                'Dock Date', 'Dock_Status', 'Transfer Party', 'Transfer Date', 'Transfer Number', 'Design Notes',
                'QAQC Notes', 'Construction Notes', 'Commissioning Notes', 'Mech Mount', 'Mech Inst Air',
                'Elec Mounted', 'Elec Wired', 'Elec Term', 'Const Complete', 'Const Date', 'Const User',
                'QAQC Complete', 'QAQC Date', 'QAQC User', 'OAT Complete', 'OAT Key', 'OAT Date', 'OAT User',
                'Revision Date', 'Revision User'
            )
            ->paginate(20);

        return view('reports.results', compact('results', 'equipment'));
    }
}
