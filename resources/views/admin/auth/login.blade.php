<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в VertexCMS</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-100 text-slate-950">
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6">
        <section class="w-full rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
                <h1 class="mt-2 text-2xl font-semibold">Вход в админ-панель</h1>
            </div>

            <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
                @csrf

                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Email</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
                    >
                    @error('email')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Пароль</span>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
                    >
                    @error('password')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                    <span>Запомнить меня</span>
                </label>

                <button
                    type="submit"
                    class="w-full rounded-md bg-slate-950 px-4 py-2 font-medium text-white hover:bg-slate-800"
                >
                    Войти
                </button>
            </form>
        </section>
    </main>
</body>
</html>

