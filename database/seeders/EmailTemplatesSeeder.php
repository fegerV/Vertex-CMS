<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'welcome' => [
                'name' => 'Добро пожаловать',
                'subject' => 'Добро пожаловать на {{ site_name }}!',
                'category' => 'transactional',
                'is_system' => true,
                'default_vars' => [
                    'site_name' => config_value('site.name', 'VertexCMS'),
                    'site_url' => config_value('site.url', '/'),
                    'user_name' => 'Пользователь',
                    'login_url' => '/admin/login',
                ],
                'body_html' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Добро пожаловать, @{{ user_name }}!</h1>
        <p>Спасибо за регистрацию на сайте <strong>@{{ site_name }}</strong>.</p>
        <p>Ваша учетная запись создана. Вы можете войти в систему:</p>
        <p><a href="@{{ site_url }}@{{ login_url }}" class="btn">Войти в панель</a></p>
        <p>С уважением,<br>Команда @{{ site_name }}</p>
    </div>
</body>
</html>',
                'body_text' => 'Добро пожаловать, @{{ user_name }}!

Спасибо за регистрацию на сайте @{{ site_name }}.

Перейдите по ссылке для входа: @{{ site_url }}@{{ login_url }}

С уважением,
Команда @{{ site_name }}',
            ],
            'password_reset' => [
                'name' => 'Сброс пароля',
                'subject' => 'Восстановление пароля на {{ site_name }}',
                'category' => 'transactional',
                'is_system' => true,
                'default_vars' => [
                    'site_name' => config_value('site.name', 'VertexCMS'),
                    'reset_url' => '#reset-link',
                    'expires_in' => '60 минут',
                ],
                'body_html' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; }
        .warning { color: #ef4444; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Восстановление пароля</h1>
        <p>Вы запросили сброс пароля на сайте <strong>@{{ site_name }}</strong>.</p>
        <p><a href="@{{ reset_url }}" class="btn">Сбросить пароль</a></p>
        <p class="warning">Ссылка действительна в течение @{{ expires_in }}.</p>
        <p>Если вы не запрашивали сброс, проигнорируйте это письмо.</p>
    </div>
</body>
</html>',
                'body_text' => 'Восстановление пароля

Вы запросили сброс пароля на сайте @{{ site_name }}.

Ссылка: @{{ reset_url }}
Ссылка действительна в течение @{{ expires_in }}.

Если вы не запрашивали сброс, проигнорируйте это письмо.',
            ],
            'notification' => [
                'name' => 'Уведомление (общее)',
                'subject' => 'Уведомление от {{ site_name }}',
                'category' => 'notification',
                'is_system' => true,
                'default_vars' => [
                    'site_name' => config_value('site.name', 'VertexCMS'),
                    'message' => 'У вас новое уведомление.',
                ],
                'body_html' => '
<div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Уведомление</h2>
    <p>@{{ message }}</p>
    <p>— <a href="@{{ site_url }}">@{{ site_name }}</a></p>
</div>',
                'body_text' => 'Уведомление от @{{ site_name }}:

@{{ message }}

—
@{{ site_name }}',
            ],
        ];

        foreach ($templates as $key => $data) {
            EmailTemplate::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name' => $data['name'],
                    'subject' => $data['subject'],
                    'body_html' => $data['body_html'],
                    'body_text' => $data['body_text'] ?? null,
                    'default_vars' => $data['default_vars'],
                    'category' => $data['category'],
                    'is_active' => true,
                    'is_system' => $data['is_system'],
                ]
            );
        }
    }
}
