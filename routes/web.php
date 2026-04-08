<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\SourcingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::post('/companies/quick-create', [CompanyController::class, 'quickCreate'])->name('companies.quickCreate');
    Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('companies.update');


    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::post('/contacts/quick-create', [ContactController::class, 'quickCreate'])->name('contacts.quickCreate');
    Route::get('/contacts/{id}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update');

    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/create', [OpportunityController::class, 'create'])->name('opportunities.create');
    Route::post('/opportunities', [OpportunityController::class, 'store'])->name('opportunities.store');
    Route::get('/opportunities/{id}/edit', [OpportunityController::class, 'edit'])->name('opportunities.edit');
    Route::post('/opportunities/{id}/update', [OpportunityController::class, 'update'])->name('opportunities.update');
    Route::delete('/opportunities/{id}', [OpportunityController::class, 'destroy'])->name('opportunities.destroy');

    Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
    Route::get('/rfqs/create', [RfqController::class, 'create'])->name('rfqs.create');
    Route::post('/rfqs', [RfqController::class, 'store'])->name('rfqs.store');
    Route::get('/rfqs/{id}/edit', [RfqController::class, 'edit'])->name('rfqs.edit');
    Route::post('/rfqs/{id}/update', [RfqController::class, 'update'])->name('rfqs.update');
    Route::delete('/rfqs/{id}', [RfqController::class, 'destroy'])->name('rfqs.destroy');

    // Sourcing
    Route::get('/rfqs/{id}/source', [SourcingController::class, 'show'])->name('rfqs.source.show');
    Route::post('/rfqs/{id}/source/run', [SourcingController::class, 'run'])->name('rfqs.source.run');

    Route::get('/manufacturers', [ManufacturerController::class, 'index'])->name('manufacturers.index');
    Route::get('/manufacturers/create', [ManufacturerController::class, 'create'])->name('manufacturers.create');
    Route::post('/manufacturers', [ManufacturerController::class, 'store'])->name('manufacturers.store');
    Route::get('/manufacturers/{id}/edit', [ManufacturerController::class, 'edit'])->name('manufacturers.edit');
    Route::post('/manufacturers/{id}/update', [ManufacturerController::class, 'update'])->name('manufacturers.update');
    Route::delete('/manufacturers/{id}', [ManufacturerController::class, 'destroy'])->name('manufacturers.destroy');
});

Route::get('/countries/by-region/{region}', [CompanyController::class, 'getCountriesByRegion']);

require __DIR__.'/auth.php';

