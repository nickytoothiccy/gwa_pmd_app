<?php

use App\Http\Controllers\PmdCnetFfuController;

Route::get('/', [PmdCnetFfuController::class, 'index']);
Route::get('/get-networks', [PmdCnetFfuController::class, 'getNetworks']);
Route::get('/get-ports', [PmdCnetFfuController::class, 'getPorts']);
Route::get('/get-equipment', [PmdCnetFfuController::class, 'getEquipment']);

// New routes for equipment search
Route::get('/equipment-search', [PmdCnetFfuController::class, 'equipmentSearch'])->name('equipment.search');
Route::get('/equipment-search-results', [PmdCnetFfuController::class, 'equipmentSearchResults'])->name('equipment.search.results');