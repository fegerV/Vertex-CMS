<?php

use App\Builder\Http\Controllers\AdvancedBuilderController;
use App\Builder\Http\Controllers\PageBuilderController;
use App\Content\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Pages CRUD
Route::get("pages", [PageController::class, "index"])->middleware("vertex.permission:pages.view")->name("pages.index");
Route::get("pages/create", [PageController::class, "create"])->middleware("vertex.permission:pages.create")->name("pages.create");
Route::post("pages", [PageController::class, "store"])->middleware("vertex.permission:pages.create")->name("pages.store");
Route::get("pages/{page}/edit", [PageController::class, "edit"])->middleware("vertex.permission:pages.edit")->name("pages.edit");
Route::get("pages/{page}/ux-preview", [PageController::class, "preview"])->middleware("vertex.permission:pages.edit")->name("pages.preview");
Route::put("pages/{page}", [PageController::class, "update"])->middleware("vertex.permission:pages.edit")->name("pages.update");
Route::delete("pages/{page}", [PageController::class, "destroy"])->middleware("vertex.permission:pages.delete")->name("pages.destroy");

// Page Builder
Route::get("pages/{page}/builder", [AdvancedBuilderController::class, "advanced"])->middleware("vertex.permission:pages.edit")->name("pages.builder");
Route::put("pages/{page}/builder", [PageBuilderController::class, "update"])->middleware("vertex.permission:pages.edit")->name("pages.builder.update");
Route::post("pages/{page}/builder/preview", [PageBuilderController::class, "preview"])->middleware("vertex.permission:pages.edit")->name("pages.builder.preview");
Route::post("pages/{page}/builder/advanced/save", [AdvancedBuilderController::class, "saveAdvanced"])->middleware("vertex.permission:pages.edit")->name("pages.builder.advanced.save");
Route::post("pages/{page}/builder/auto-save", [AdvancedBuilderController::class, "autoSave"])->middleware("vertex.permission:pages.edit")->name("pages.builder.auto-save");
Route::get("pages/{page}/revisions", [AdvancedBuilderController::class, "getRevisions"])->middleware("vertex.permission:pages.edit")->name("pages.revisions.index");
Route::post("pages/{page}/revisions/{revision}/restore", [AdvancedBuilderController::class, "restoreRevision"])->middleware("vertex.permission:pages.edit")->name("pages.revisions.restore");
Route::post("pages/{page}/builder/template", [AdvancedBuilderController::class, "applyTemplate"])->middleware("vertex.permission:pages.edit")->name("pages.builder.template");
Route::post("pages/export-sections", [AdvancedBuilderController::class, "exportSections"])->middleware("vertex.permission:pages.edit")->name("pages.export-sections");
Route::post("pages/import-sections", [AdvancedBuilderController::class, "importSections"])->middleware("vertex.permission:pages.edit")->name("pages.import-sections");
Route::get("pages/builder/presets", [AdvancedBuilderController::class, "getSharedPresets"])->middleware("vertex.permission:pages.edit")->name("pages.builder.presets.index");
Route::post("pages/builder/presets", [AdvancedBuilderController::class, "storeSharedPreset"])->middleware("vertex.permission:pages.edit")->name("pages.builder.presets.store");
Route::put("pages/builder/presets/{presetId}", [AdvancedBuilderController::class, "updateSharedPreset"])->middleware("vertex.permission:pages.edit")->name("pages.builder.presets.update");
Route::delete("pages/builder/presets/{presetId}", [AdvancedBuilderController::class, "destroySharedPreset"])->middleware("vertex.permission:pages.edit")->name("pages.builder.presets.destroy");
Route::get("pages/builder/shared-templates", [AdvancedBuilderController::class, "getSharedTemplates"])->middleware("vertex.permission:pages.edit")->name("pages.builder.shared-templates.index");
Route::get("pages/builder/design-library", [AdvancedBuilderController::class, "getDesignLibrary"])->middleware("vertex.permission:pages.edit")->name("pages.builder.design-library.index");
Route::post("pages/builder/shared-templates", [AdvancedBuilderController::class, "storeSharedTemplate"])->middleware("vertex.permission:pages.edit")->name("pages.builder.shared-templates.store");
Route::put("pages/builder/shared-templates/{templateId}", [AdvancedBuilderController::class, "updateSharedTemplate"])->middleware("vertex.permission:pages.edit")->name("pages.builder.shared-templates.update");
Route::delete("pages/builder/shared-templates/{templateId}", [AdvancedBuilderController::class, "destroySharedTemplate"])->middleware("vertex.permission:pages.edit")->name("pages.builder.shared-templates.destroy");
