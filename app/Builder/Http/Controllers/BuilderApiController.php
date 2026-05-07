<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Services\PageRenderer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuilderApiController extends Controller
{
    public function __construct(private readonly PageRenderer $renderer) {}

    public function blocks(): JsonResponse
    {
        return response()->json([
            'blocks' => [
                'heading' => [
                    'name' => 'Заголовок',
                    'icon' => 'type-h2',
                    'default' => ['type' => 'heading', 'level' => 'h2', 'text' => 'Заголовок', 'align' => 'left', 'color' => '#111827'],
                ],
                'text' => [
                    'name' => 'Текст',
                    'icon' => 'paragraph',
                    'default' => ['type' => 'text', 'text' => 'Текстовый блок...'],
                ],
                'button' => [
                    'name' => 'Кнопка',
                    'icon' => 'link',
                    'default' => ['type' => 'button', 'text' => 'Подробнее', 'url' => '#', 'target' => '_self', 'style' => 'primary'],
                ],
                'divider' => [
                    'name' => 'Разделитель',
                    'icon' => 'minus',
                    'default' => ['type' => 'divider'],
                ],
                'faq' => [
                    'name' => 'FAQ',
                    'icon' => 'help-circle',
                    'default' => ['type' => 'faq', 'items' => [['question' => 'Вопрос?', 'answer' => 'Ответ']]],
                ],
                'html' => [
                    'name' => 'HTML',
                    'icon' => 'code',
                    'default' => ['type' => 'html', 'html' => '<p>HTML код</p>'],
                ],
                'image' => [
                    'name' => 'Изображение',
                    'icon' => 'image',
                    'default' => ['type' => 'image', 'media_id' => null, 'alt' => '', 'width' => '100%'],
                ],
            ],
        ]);
    }

    public function renderPreview(Request $request): JsonResponse
    {
        $content = $request->validate([
            'content' => ['required', 'array'],
        ])['content'];

        $layout = $request->input('layout', 'default');
        $sections = is_array($content) ? $content : [];

        $html = $this->renderer->render(['version' => '1.0', 'layout' => $layout, 'sections' => $sections]);

        return response()->json([
            'html' => (string) $html,
            'sections_count' => count($sections),
        ]);
    }
}

