<?php

use Vertex\Forms\Controllers\FormController;
use Vertex\Forms\Controllers\FormApiController;
use Vertex\Forms\Controllers\FormBuilderController;
use Vertex\Forms\Controllers\FormSubmissionController;
use Vertex\Forms\Controllers\FormAnalyticsController;
use Vertex\Forms\Controllers\FormVersionController;
use Vertex\Forms\Controllers\FormSubmissionFileController;
use Illuminate\Support\Facades\Route;

// These routes are loaded within the main admin group (prefix: admin, middleware: auth, vertex.permission:admin.access)
// They inherit the admin prefix and name prefix "admin."

Route::middleware(["vertex.permission:forms.view"])->prefix("forms")->name("forms.")->group(function () {
    Route::get("/", [FormController::class, "index"])->name("index");
    Route::get("{form}/submissions", [FormSubmissionController::class, "index"])->name("submissions.index");
    Route::get("{form}/submissions/{submission}", [FormSubmissionController::class, "show"])->name("submissions.show");
    Route::get("{form}/submissions/{submission}/files/{value}/{fileIndex?}", [FormSubmissionFileController::class, "download"])
        ->middleware("vertex.permission:forms.view_submissions")
        ->whereNumber('fileIndex')
        ->name("submissions.files.download");
    Route::delete("{form}/submissions/{submission}", [FormSubmissionController::class, "destroy"])->name("submissions.destroy");
    Route::delete("{form}/clear-submissions", [FormSubmissionController::class, "clear"])->name("submissions.clear");
    Route::post("{form}/export-submissions", [FormSubmissionController::class, "export"])->name("submissions.export");

    // Analytics
    Route::get("{form}/analytics", [FormAnalyticsController::class, "show"])->name("analytics");
    Route::get("{form}/analytics/data", [FormAnalyticsController::class, "data"])->name("analytics.data");

    // Versions
    Route::get("{form}/versions", [FormVersionController::class, "index"])->name("versions.index");
    Route::post("{form}/versions", [FormVersionController::class, "store"])->name("versions.store");
    Route::post("{form}/restore/{version}", [FormVersionController::class, "restore"])->name("versions.restore");

    // JSON Import/Export
    Route::get("{form}/export-json", [FormController::class, "exportJson"])->name("export.json");
    Route::post("{form}/import-json", [FormController::class, "importJson"])->name("import.json");
});

Route::middleware(["vertex.permission:forms.create"])->prefix("forms")->name("forms.")->group(function () {
    Route::get("/create", [FormController::class, "create"])->name("create");
    Route::post("/", [FormController::class, "store"])->name("store");
});

Route::middleware(["vertex.permission:forms.edit"])->prefix("forms")->name("forms.")->group(function () {
    Route::get("{form}/edit", [FormController::class, "edit"])->name("edit");
    Route::put("{form}", [FormController::class, "update"])->name("update");
    Route::post("{form}/duplicate", [FormController::class, "duplicate"])->name("duplicate");
    Route::get("{form}/preview", [FormController::class, "preview"])->name("preview");
    Route::get("{form}/builder", [FormBuilderController::class, "show"])->name("builder"); // Vue SPA
});

Route::middleware(["vertex.permission:forms.delete"])->prefix("forms")->name("forms.")->group(function () {
    Route::delete("{form}", [FormController::class, "destroy"])->name("destroy");
});
