<?php

use App\Http\Controllers\AberturaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ObraController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/obras');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/obras', [ObraController::class, 'index'])->name('obras.index');
    Route::get('/obras/crear', [ObraController::class, 'create'])->name('obras.create');
    Route::post('/obras', [ObraController::class, 'store'])->name('obras.store');
    Route::get('/obras/{obra}', [ObraController::class, 'show'])->name('obras.show');
    Route::get('/obras/{obra}/presupuesto.csv', [ObraController::class, 'exportarCsv'])->name('obras.csv');
    Route::get('/obras/{obra}/presupuesto.pdf', [ObraController::class, 'exportarPdf'])->name('obras.pdf');
    Route::get('/obras/{obra}/presupuesto.xlsx', [ObraController::class, 'exportarXlsx'])->name('obras.xlsx');

    Route::post('/obras/{obra}/aberturas', [AberturaController::class, 'store'])->name('aberturas.store');
    Route::get('/aberturas/opciones/{tipologia}/{serie}', [AberturaController::class, 'opciones'])->name('aberturas.opciones');
    Route::post('/aberturas/{abertura}/recalcular', [AberturaController::class, 'recalcular'])->name('aberturas.recalcular');
    Route::delete('/aberturas/{abertura}', [AberturaController::class, 'destroy'])->name('aberturas.destroy');

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
});
