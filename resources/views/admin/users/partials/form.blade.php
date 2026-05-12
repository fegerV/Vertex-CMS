<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Основные данные</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Эти поля определяют аккаунт для входа в админку и отображение пользователя в системе.</p>
    </div>

    <div class="vc-form-grid vc-form-grid-2">
        <label class="vc-field">
            <span class="vc-field-label">Имя</span>
            <span class="vc-field-help">Отображаемое имя пользователя в админке и в журналах действий.</span>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="vc-input">
            @error('name') <span class="vc-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Email</span>
            <span class="vc-field-help">Используется как логин для входа в админку.</span>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="vc-input">
            @error('email') <span class="vc-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Пароль</span>
            <span class="vc-field-help">{{ $user->exists ? 'Оставьте поле пустым, если не хотите менять пароль.' : 'Минимум один пароль обязателен для нового аккаунта.' }}</span>
            <input type="password" name="password" @required(! $user->exists) class="vc-input">
            @error('password') <span class="vc-field-error">{{ $message }}</span> @enderror
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Повтор пароля</span>
            <span class="vc-field-help">Нужно для подтверждения нового пароля без ошибок ввода.</span>
            <input type="password" name="password_confirmation" @required(! $user->exists) class="vc-input">
        </label>

        <label class="vc-field">
            <span class="vc-field-label">Статус</span>
            <span class="vc-field-help">Заблокированный пользователь не сможет войти в админку.</span>
            <select name="status" class="vc-select">
                @foreach (['active' => 'Активен', 'blocked' => 'Заблокирован'] as $status => $label)
                    <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>
</section>

<section class="vc-panel vc-panel-muted p-5 vc-form-section">
    <div>
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Роли и права</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Выберите наборы прав, которые будут назначены пользователю. Можно назначить несколько ролей сразу.</p>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
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

<div class="flex justify-end">
    <button class="vc-button vc-button-primary" type="submit">
        Сохранить пользователя
    </button>
</div>
