<?php

use App\Taxonomy\Http\Controllers\AdminTaxonomyController;
use App\Taxonomy\Http\Controllers\AdminTermController;
use Illuminate\Support\Facades\Route;

// Taxonomies CRUD
Route::get("taxonomies", [AdminTaxonomyController::class, "index"])->middleware("vertex.permission:taxonomy.view")->name("taxonomies.index");
Route::get("taxonomies/create", [AdminTaxonomyController::class, "create"])->middleware("vertex.permission:taxonomy.create")->name("taxonomies.create");
Route::post("taxonomies", [AdminTaxonomyController::class, "store"])->middleware("vertex.permission:taxonomy.create")->name("taxonomies.store");
Route::get("taxonomies/{taxonomy}/edit", [AdminTaxonomyController::class, "edit"])->middleware("vertex.permission:taxonomy.edit")->name("taxonomies.edit");
Route::put("taxonomies/{taxonomy}", [AdminTaxonomyController::class, "update"])->middleware("vertex.permission:taxonomy.edit")->name("taxonomies.update");
Route::delete("taxonomies/{taxonomy}", [AdminTaxonomyController::class, "destroy"])->middleware("vertex.permission:taxonomy.delete")->name("taxonomies.destroy");

// Terms (nested under taxonomies)
Route::get("taxonomies/{taxonomy}/terms/create", [AdminTermController::class, "create"])->middleware("vertex.permission:taxonomy.create")->name("taxonomies.terms.create");
Route::post("taxonomies/{taxonomy}/terms", [AdminTermController::class, "store"])->middleware("vertex.permission:taxonomy.create")->name("taxonomies.terms.store");
Route::get("taxonomies/{taxonomy}/terms/{term}/edit", [AdminTermController::class, "edit"])->middleware("vertex.permission:taxonomy.edit")->name("taxonomies.terms.edit");
Route::put("taxonomies/{taxonomy}/terms/{term}", [AdminTermController::class, "update"])->middleware("vertex.permission:taxonomy.edit")->name("taxonomies.terms.update");
Route::delete("taxonomies/{taxonomy}/terms/{term}", [AdminTermController::class, "destroy"])->middleware("vertex.permission:taxonomy.delete")->name("taxonomies.terms.destroy");