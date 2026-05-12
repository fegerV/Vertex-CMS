<!DOCTYPE html>
<html lang="{{ config_value('site.admin_locale', 'ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    @if($googleAnalyticsId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $googleAnalyticsId }}');
        </script>
    @endif

    <style>
        :root {
            --primary-color: {{ $colors['primary'] }};
            --secondary-color: {{ $colors['secondary'] }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Background - Fullscreen with Backstretch-like behavior */
        .bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .theme-minimal .bg-container {
            background: linear-gradient(135deg, #f8fafc 0%, #f3f4f6 100%);
        }

        .theme-dark .bg-container {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        .theme-light .bg-container {
            background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%);
        }

        .theme-gradient .bg-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .theme-geometric .bg-container {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .theme-nature .bg-container {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .theme-city .bg-container {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .theme-abstract .bg-container {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .theme-retro .bg-container {
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
        }

        .theme-tech .bg-container {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .bg-image-backstretch {
            position: absolute;
            top: 0;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translateX(-50%);
            z-index: 0;
            object-fit: cover;
        }

        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .blur-active .bg-overlay {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Content */
        .maintenance-container {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .theme-minimal .maintenance-container,
        .theme-light .maintenance-container {
            color: #1f2937;
        }

        .theme-minimal .login-form input,
        .theme-light .login-form input {
            color: #1f2937;
            border-color: #d1d5db;
        }

        .logo {
            max-width: 150px;
            max-height: 150px;
            margin-bottom: 2rem;
            border-radius: 8px;
        }

        .title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .slogan {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 600px;
        }

        .text {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            max-width: 600px;
            line-height: 1.6;
        }

        .login-form {
            margin-top: 2rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            max-width: 400px;
            width: 100%;
        }

        .form-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .submit-btn:hover {
            filter: brightness(1.1);
        }

        @media (max-width: 768px) {
            .title {
                font-size: 2rem;
            }
            .slogan {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body class="theme-{{ $theme }} @if($backgroundBlur) blur-active @endif">
    <div class="bg-container">
        @if($backgroundImage)
            <img src="{{ asset('storage/' . \App\Models\Media::find($backgroundImage)?->file_path) }}"
                 alt="Background"
                 class="bg-image-backstretch"
                 id="bgImage"
                 onerror="this.style.display='none'">
        @endif
        <div class="bg-overlay"></div>
    </div>

    <div class="maintenance-container">
        @if($logo)
            <img src="{{ asset('storage/' . \App\Models\Media::find($logo)?->file_path) }}"
                 alt="Logo"
                 class="logo"
                 onerror="this.style.display='none'">
        @endif

        <h1 class="title">{{ $title }}</h1>
        @if($slogan)
            <p class="slogan">{{ $slogan }}</p>
        @endif
        @if($text)
            <p class="text">{{ $text }}</p>
        @endif

        @if($loginFormEnabled)
            <div class="login-form">
                <h2 class="form-title">Вход для администраторов</h2>
                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    <button type="submit" class="submit-btn">Войти</button>
                </form>
            </div>
        @endif
    </div>

    @if($backgroundImage)
    <script>
        (function() {
            const img = document.getElementById('bgImage');
            if (!img) return;

            function resizeBackground() {
                const winW = window.innerWidth;
                const winH = window.innerHeight;
                const imgRatio = img.naturalWidth / img.naturalHeight;
                const winRatio = winW / winH;

                if (winRatio > imgRatio) {
                    img.style.width = winW + 'px';
                    img.style.height = (winW / imgRatio) + 'px';
                } else {
                    img.style.height = winH + 'px';
                    img.style.width = (winH * imgRatio) + 'px';
                }
                img.style.top = '50%';
                img.style.left = '50%';
                img.style.transform = 'translate(-50%, -50%)';
            }

            if (img.naturalWidth) {
                resizeBackground();
            } else {
                img.onload = resizeBackground;
            }
            window.addEventListener('resize', resizeBackground);
        })();
    </script>
    @endif
</body>
</html>
