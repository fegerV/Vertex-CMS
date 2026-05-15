<?php

use App\Admin\Http\Controllers\UserController;
use App\Admin\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// Users CRUD
Route::get("users", [UserController::class, "index"])->middleware("vertex.permission:users.view")->name("users.index");
Route::get("users/create", [UserController::class, "create"])->middleware("vertex.permission:users.create")->name("users.create");
Route::post("users", [UserController::class, "store"])->middleware("vertex.permission:users.create")->name("users.store");
Route::get("users/{user}/edit", [UserController::class, "edit"])->middleware("vertex.permission:users.edit")->name("users.edit");
Route::put("users/{user}", [UserController::class, "update"])->middleware("vertex.permission:users.edit")->name("users.update");
Route::delete("users/{user}", [UserController::class, "destroy"])->middleware("vertex.permission:users.delete")->name("users.destroy");

// Roles (edit only - roles are seeded)
Route::get("roles", [RoleController::class, "index"])->middleware("vertex.permission:roles.view")->name("roles.index");
Route::get("roles/{role}/edit", [RoleController::class, "edit"])->middleware("vertex.permission:roles.edit")->name("roles.edit");
Route::put("roles/{role}", [RoleController::class, "update"])->middleware("vertex.permission:roles.edit")->name("roles.update");