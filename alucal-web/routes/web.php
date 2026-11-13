<?php

use App\Http\Controllers\AberturaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VanoController;
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
    Route::get('/obras/{obra}/editar', [ObraController::class, 'edit'])->name('obras.edit');
    Route::patch('/obras/{obra}', [ObraController::class, 'update'])->name('obras.update');
    Route::delete('/obras/{obra}', [ObraController::class, 'destroy'])->name('obras.destroy');
    Route::get('/obras/{obra}/presupuesto.csv', [ObraController::class, 'exportarCsv'])->name('obras.csv');
    Route::get('/obras/{obra}/presupuesto.pdf', [ObraController::class, 'exportarPdf'])->name('obras.pdf');
    Route::get('/obras/{obra}/presupuesto.xlsx', [ObraController::class, 'exportarXlsx'])->name('obras.xlsx');

    Route::post('/obras/{obra}/vanos', [VanoController::class, 'store'])->name('vanos.store');
    Route::delete('/obras/{obra}/vanos/{vano}', [VanoController::class, 'destroy'])->name('vanos.destroy');

    Route::post('/obras/{obra}/aberturas', [AberturaController::class, 'store'])->name('aberturas.store');
    Route::get('/aberturas/opciones/{tipologia}/{serie}', [AberturaController::class, 'opciones'])->name('aberturas.opciones');
    Route::get('/aberturas/{abertura}/presupuesto.pdf', [AberturaController::class, 'presupuestoAbertura'])->name('aberturas.pdf');
    Route::post('/aberturas/{abertura}/recalcular', [AberturaController::class, 'recalcular'])->name('aberturas.recalcular');
    Route::delete('/aberturas/{abertura}', [AberturaController::class, 'destroy'])->name('aberturas.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        Route::get('/catalogos/{recurso}', [CatalogoController::class, 'index'])->name('catalogos.index');
        Route::post('/catalogos/{recurso}', [CatalogoController::class, 'update'])->name('catalogos.update');
        Route::post('/catalogos/{recurso}/nueva', [CatalogoController::class, 'store'])->name('catalogos.store');
        Route::delete('/catalogos/{recurso}/{identificador}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy');
    });

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
});
