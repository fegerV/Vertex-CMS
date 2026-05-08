<div class="grid gap-5 md:grid-cols-2">
    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Имя</span>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="vc-input">
        @error('name') <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span> @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Email</span>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="vc-input">
        @error('email') <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span> @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Пароль</span>
        <input type="password" name="password" @required(! $user->exists) class="vc-input">
        @error('password') <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span> @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Повтор пароля</span>
        <input type="password" name="password_confirmation" @required(! $user->exists) class="vc-input">
    </label>

    <label class="block">
        <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Статус</span>
        <select name="status" class="vc-select">
            @foreach (['active', 'blocked'] as $status)
                <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </label>
</div>

<section class="border-t border-[var(--vc-border)] pt-5">
    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Роли</h2>
    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Выберите наборы прав, которые будут назначены пользователю.</p>
    <div class="mt-3 grid gap-2 md:grid-cols-2">
        @foreach ($roles as $role)
            <label class="vc-checkbox-row text-sm">
                <input
                    type="checkbox"
                    name="roles[]"
                    value="{{ $role->id }}"
                    @checked(in_array($role->id, old('roles', $selectedRoles), true))
                    class="rounded border-slate-300"
                >
                <span class="text-[var(--vc-text)]">{{ $role->name }}</span>
            </label>
        @endforeach
    </div>
</section>

<div class="flex justify-end border-t border-[var(--vc-border)] pt-5">
    <button class="vc-button vc-button-primary px-4 py-3">
        Сохранить
    </button>
</div>
