<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Двухфакторная авторизация — VertexCMS</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-100 text-slate-950">
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6">
        <section class="w-full rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
                <h1 class="mt-2 text-2xl font-semibold">Двухфакторная авторизация</h1>
                <p class="mt-1 text-sm text-slate-600">Введите 6-значный код из приложения-аутентификатора</p>
            </div>

            <form method="POST" action="{{ route('admin.2fa.verify.submit') }}" class="space-y-4">
                @csrf

                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Код из приложения</span>
                    <input
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-center text-lg font-mono outline-none focus:border-slate-900"
                        placeholder="000000"
                    >
                    @error('code')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <button
                    type="submit"
                    class="w-full rounded-md bg-slate-950 px-4 py-2 font-medium text-white hover:bg-slate-800"
                >
                    Подтвердить
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('admin.logout') }}" class="text-sm text-slate-500 hover:underline">
                    Войти под другим аккаунтом
                </a>
            </div>
        </section>
    </main>
</body>
</html>