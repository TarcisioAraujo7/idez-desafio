<?php

use App\Http\Controllers\MunicipalityController;
use Illuminate\Support\Facades\Route;

Route::get('/municipalities/{uf}', [MunicipalityController::class, 'index'])->name('municipalities.index');
