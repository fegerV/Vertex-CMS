<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в VertexCMS</title>
    <?php if(! app()->runningUnitTests()): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>
</head>
<body class="min-h-screen bg-slate-100 text-slate-950">
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6">
        <section class="w-full rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
                <h1 class="mt-2 text-2xl font-semibold">Вход в админ-панель</h1>
            </div>

            <form method="POST" action="<?php echo e(route('admin.login')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Email</span>
                    <input
                        type="email"
                        name="email"
                        value="<?php echo e(old('email')); ?>"
                        required
                        autofocus
                        class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
                    >
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="mt-1 block text-sm text-red-600"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Пароль</span>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
                    >
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="mt-1 block text-sm text-red-600"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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

<?php /**PATH /workspace/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>