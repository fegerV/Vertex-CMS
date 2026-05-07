<div class="grid gap-5 md:grid-cols-2">
    <label class="block">
        <span class="mb-1 block text-sm font-medium">Имя</span>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
        @error('name') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Email</span>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
        @error('email') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Пароль</span>
        <input type="password" name="password" @required(! $user->exists) class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
        @error('password') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Повтор пароля</span>
        <input type="password" name="password_confirmation" @required(! $user->exists) class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
    </label>

    <label class="block">
        <span class="mb-1 block text-sm font-medium">Статус</span>
        <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
            @foreach (['active', 'blocked'] as $status)
                <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </label>
</div>

<section class="border-t border-slate-100 pt-5">
    <h2 class="text-lg font-semibold">Роли</h2>
    <div class="mt-3 grid gap-2 md:grid-cols-2">
        @foreach ($roles as $role)
            <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm">
                <input
                    type="checkbox"
                    name="roles[]"
                    value="{{ $role->id }}"
                    @checked(in_array($role->id, old('roles', $selectedRoles), true))
                    class="rounded border-slate-300"
                >
                <span>{{ $role->name }}</span>
            </label>
        @endforeach
    </div>
</section>

<div class="flex justify-end border-t border-slate-100 pt-5">
    <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        Сохранить
    </button>
</div>
