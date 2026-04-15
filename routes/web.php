<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\SourcingController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\InquiryRuleController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SmoSupplierController;
use App\Http\Controllers\SupplierBrandController;

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
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])->name('companies.destroy');


    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::post('/contacts/quick-create', [ContactController::class, 'quickCreate'])->name('contacts.quickCreate');
    Route::get('/contacts/{id}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/create', [OpportunityController::class, 'create'])->name('opportunities.create');
    Route::post('/opportunities', [OpportunityController::class, 'store'])->name('opportunities.store');
    Route::get('/opportunities/{id}/edit', [OpportunityController::class, 'edit'])->name('opportunities.edit');
    Route::post('/opportunities/{id}/update', [OpportunityController::class, 'update'])->name('opportunities.update');
    Route::delete('/opportunities/{id}', [OpportunityController::class, 'destroy'])->name('opportunities.destroy');

    Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
    Route::get('/rfqs/inquiry-number-preview', [RfqController::class, 'inquiryNumberPreview'])->name('rfqs.inquiry_number_preview');
    Route::get('/rfqs/create', [RfqController::class, 'create'])->name('rfqs.create');
    Route::post('/rfqs', [RfqController::class, 'store'])->name('rfqs.store');
    Route::get('/rfqs/{id}/edit', [RfqController::class, 'edit'])->name('rfqs.edit');
    Route::post('/rfqs/{id}/update', [RfqController::class, 'update'])->name('rfqs.update');
    Route::delete('/rfqs/{id}', [RfqController::class, 'destroy'])->name('rfqs.destroy');

    // Sourcing
    Route::get('/rfqs/{id}/source', [SourcingController::class, 'show'])->name('rfqs.source.show');
    Route::post('/rfqs/{id}/source/run', [SourcingController::class, 'run'])->name('rfqs.source.run');
    Route::get('/rfqs/{id}/sourcing-status', [SourcingController::class, 'status'])->name('rfqs.source.status');

    Route::get('/manufacturers', [ManufacturerController::class, 'index'])->name('manufacturers.index');
    Route::get('/manufacturers/create', [ManufacturerController::class, 'create'])->name('manufacturers.create');
    Route::post('/manufacturers', [ManufacturerController::class, 'store'])->name('manufacturers.store');
    Route::get('/manufacturers/{id}/edit', [ManufacturerController::class, 'edit'])->name('manufacturers.edit');
    Route::post('/manufacturers/{id}/update', [ManufacturerController::class, 'update'])->name('manufacturers.update');
    Route::delete('/manufacturers/{id}', [ManufacturerController::class, 'destroy'])->name('manufacturers.destroy');

    // Regions
    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
    Route::get('/regions/{id}/edit', [RegionController::class, 'edit'])->name('regions.edit');
    Route::put('/regions/{id}', [RegionController::class, 'update'])->name('regions.update');
    Route::delete('/regions/{id}', [RegionController::class, 'destroy'])->name('regions.destroy');

    // Countries
    Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
    Route::post('/countries', [CountryController::class, 'store'])->name('countries.store');
    Route::get('/countries/{id}/edit', [CountryController::class, 'edit'])->name('countries.edit');
    Route::put('/countries/{id}', [CountryController::class, 'update'])->name('countries.update');
    Route::delete('/countries/{id}', [CountryController::class, 'destroy'])->name('countries.destroy');

    // Purchasing
    Route::get('/purchasing', [PurchasingController::class, 'index'])->name('purchasing.index');

    // SMO Suppliers
    Route::get('/smo-suppliers', [SmoSupplierController::class, 'index'])->name('smo_suppliers.index');
    Route::post('/smo-suppliers/upload', [SmoSupplierController::class, 'upload'])->name('smo_suppliers.upload');

    // Supplier Brands
    Route::get('/supplier-brands', [SupplierBrandController::class, 'index'])->name('supplier_brands.index');
    Route::post('/supplier-brands/upload', [SupplierBrandController::class, 'upload'])->name('supplier_brands.upload');

    // Inquiry Number Rules
    Route::get('/inquiry-rules', [InquiryRuleController::class, 'index'])->name('inquiry_rules.index');
    Route::post('/inquiry-rules', [InquiryRuleController::class, 'store'])->name('inquiry_rules.store');
    Route::put('/inquiry-rules/{id}', [InquiryRuleController::class, 'update'])->name('inquiry_rules.update');
    Route::delete('/inquiry-rules/{id}', [InquiryRuleController::class, 'destroy'])->name('inquiry_rules.destroy');
});

Route::get('/countries/by-region/{region}', [CompanyController::class, 'getCountriesByRegion']);

require __DIR__.'/auth.php';

