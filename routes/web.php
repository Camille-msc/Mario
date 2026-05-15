<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RentalController;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    // /films/create avant /films/{id} pour éviter que Laravel interprète "create" comme un id
    Route::get('/films', [FilmController::class, 'index'])->name('films.index');
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');
    Route::get('/films/{id}', [FilmController::class, 'show'])->name('films.show');
    Route::get('/films/{id}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{id}', [FilmController::class, 'update'])->name('films.update');
    Route::delete('/films/{id}', [FilmController::class, 'destroy'])->name('films.destroy');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    // POST pour la pagination ajax - retourne du JSON sans recharger la page
    Route::post('/customers/data', [CustomerController::class, 'getData'])->name('customers.data');
    Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::put('/rentals/{id}/status', [RentalController::class, 'updateStatus'])->name('rentals.updateStatus');
    Route::delete('/rentals/{id}', [RentalController::class, 'destroy'])->name('rentals.destroy');

    Route::get('/dvds', [InventoryController::class, 'index'])->name('dvds.index');
    Route::get('/dvds/film/{filmId}', [InventoryController::class, 'show'])->name('dvds.show');
    Route::post('/dvds', [InventoryController::class, 'store'])->name('dvds.store');
    Route::get('/dvds/{inventoryId}/edit', [InventoryController::class, 'edit'])->name('dvds.edit');
    Route::put('/dvds/{inventoryId}', [InventoryController::class, 'update'])->name('dvds.update');
    Route::delete('/dvds/{inventoryId}', [InventoryController::class, 'destroy'])->name('dvds.destroy');
});
