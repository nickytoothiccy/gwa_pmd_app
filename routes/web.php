<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PmdCnetFfuController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\FabViewerController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/pmd_cnet_ffu', [PmdCnetFfuController::class, 'index'])->name('pmd_cnet_ffu.index');
Route::post('/pmd_cnet_ffu/search', [PmdCnetFfuController::class, 'search'])->name('pmd_cnet_ffu.search');
Route::get('/pmd_cnet_ffu/equipment-search', [PmdCnetFfuController::class, 'equipmentSearch'])->name('pmd_cnet_ffu.equipment_search');
Route::get('/pmd_cnet_ffu/equipment-search-results', [PmdCnetFfuController::class, 'equipmentSearchResults'])->name('pmd_cnet_ffu.equipment_search_results');

Route::get('/get-networks', [PmdCnetFfuController::class, 'getNetworks'])->name('pmd_cnet_ffu.get_networks');
Route::get('/get-ports', [PmdCnetFfuController::class, 'getPorts'])->name('pmd_cnet_ffu.get_ports');
Route::get('/get-equipment', [PmdCnetFfuController::class, 'getEquipment'])->name('pmd_cnet_ffu.get_equipment');

Route::get('/pmd_cnet_ffu/edit', [PmdCnetFfuController::class, 'edit'])->name('pmd_cnet_ffu.edit');
Route::put('/pmd_cnet_ffu/{equipment}', [PmdCnetFfuController::class, 'update'])->name('pmd_cnet_ffu.update');

// New routes for JSON file status and email sending
Route::get('/pmd_cnet_ffu/check-json', [PmdCnetFfuController::class, 'checkJsonFile'])->name('pmd_cnet_ffu.check_json_file');
Route::post('/pmd_cnet_ffu/send-email', [PmdCnetFfuController::class, 'sendEmail'])->name('pmd_cnet_ffu.send_email');

Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/reports/search', [ReportsController::class, 'search'])->name('reports.search');
Route::get('/reports/live-search', [ReportsController::class, 'liveSearch'])->name('reports.liveSearch');

Route::get('/fab-viewer', [FabViewerController::class, 'index'])->name('fab_viewer.index');

// Keep any other existing routes
