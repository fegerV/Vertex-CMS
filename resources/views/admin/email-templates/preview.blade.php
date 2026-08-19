<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр: {{ $template->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .email-container {
            max-width: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .email-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .email-header h1 {
            font-size: 1.25rem;
            color: #1f2937;
        }
        .email-body {
            padding: 2rem;
            color: #374151;
            line-height: 1.6;
        }
        .email-footer {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{{ $template->subject }}</h1>
        </div>
        <div class="email-body">
            {!! $html !!}
        </div>
        <div class="email-footer">
            <p>Это тестовое письмо из шаблона <strong>{{ $template->name }}</strong> ({{ $template->key }})</p>
            <p>© {{ date('Y') }} {{ config_value('site.name', 'VertexCMS') }}</p>
        </div>
    </div>
</body>
</html>
