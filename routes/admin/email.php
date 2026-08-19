<?php

use App\System\Http\Controllers\EmailLogController;
use App\System\Http\Controllers\EmailTemplateController;
use Illuminate\Support\Facades\Route;

// Email Templates
Route::get("email-templates", [EmailTemplateController::class, "index"])->middleware("vertex.permission:mail.view")->name("email-templates.index");
Route::get("email-templates/create", [EmailTemplateController::class, "create"])->middleware("vertex.permission:mail.edit")->name("email-templates.create");
Route::post("email-templates", [EmailTemplateController::class, "store"])->middleware("vertex.permission:mail.edit")->name("email-templates.store");
Route::get("email-templates/{template}/edit", [EmailTemplateController::class, "edit"])->middleware("vertex.permission:mail.edit")->name("email-templates.edit");
Route::put("email-templates/{template}", [EmailTemplateController::class, "update"])->middleware("vertex.permission:mail.edit")->name("email-templates.update");
Route::delete("email-templates/{template}", [EmailTemplateController::class, "destroy"])->middleware("vertex.permission:mail.edit")->name("email-templates.destroy");
Route::post("email-templates/{template}/send-test", [EmailTemplateController::class, "sendTest"])->middleware("vertex.permission:mail.edit")->name("email-templates.send-test");
Route::get("email-templates/{template}/preview", [EmailTemplateController::class, "preview"])->middleware("vertex.permission:mail.view")->name("email-templates.preview");

// Email Logs
Route::get("email-logs", [EmailLogController::class, "index"])->middleware("vertex.permission:mail.view")->name("email-logs.index");
Route::get("email-logs/{log}", [EmailLogController::class, "show"])->middleware("vertex.permission:mail.view")->name("email-logs.show");
Route::delete("email-logs/{log}", [EmailLogController::class, "destroy"])->middleware("vertex.permission:mail.delete")->name("email-logs.destroy");
Route::post("email-logs/{log}/resend", [EmailLogController::class, "resend"])->middleware("vertex.permission:mail.edit")->name("email-logs.resend");