<?php

namespace App\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\System\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->withCount('permissions')->orderBy('name')->get(),
        ]);
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::query()->orderBy('group_name')->orderBy('slug')->get()->groupBy('group_name'),
            'selectedPermissions' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $role->forceFill(['name' => $payload['name']])->save();

        $permissions = $role->slug === 'super-admin'
            ? Permission::query()->pluck('id')->all()
            : ($payload['permissions'] ?? []);

        $role->permissions()->sync($permissions);

        $this->activityLog->record('roles.edit', 'role', $role->id, "Role \"{$role->slug}\" updated.");

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Роль сохранена.');
    }
}
