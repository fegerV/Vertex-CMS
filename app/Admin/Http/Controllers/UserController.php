<?php

namespace App\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\System\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with('roles')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['status' => 'active']),
            'roles' => Role::query()->orderBy('name')->get(),
            'selectedRoles' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validated($request);

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'status' => $payload['status'],
        ]);

        $user->roles()->sync($payload['roles'] ?? []);

        $this->activityLog->record('users.create', 'user', $user->id, "User \"{$user->email}\" created.");

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Пользователь создан.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
            'selectedRoles' => $user->roles->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $payload = $this->validated($request, $user);

        $attributes = [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'status' => $payload['status'],
        ];

        if (filled($payload['password'] ?? null)) {
            $attributes['password'] = Hash::make($payload['password']);
        }

        $user->forceFill($attributes)->save();
        $user->roles()->sync($payload['roles'] ?? []);

        $this->activityLog->record('users.edit', 'user', $user->id, "User \"{$user->email}\" updated.");

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Пользователь сохранён.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');

        $this->activityLog->record('users.delete', 'user', $user->id, "User \"{$user->email}\" deleted.");
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Пользователь удалён.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'blocked'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);
    }
}
