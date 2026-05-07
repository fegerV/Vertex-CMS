<?php

namespace App\Builder\Config;

// Block definitions for 60+ block types
// Organized by categories

$blocks = [

    // CONTENT BLOCKS
    'heading' => [
        'name' => 'Заголовок',
        'category' => 'content',
        'icon' => 'type-h2',
        'description' => 'Основной заголовок страницы или секции',
        'default' => [
            'type' => 'heading',
            'settings' => [
                'level' => 'h2',
                'text' => 'Новый заголовок',
                'align' => 'left',
                'color' => '#111827',
                'font_size' => '1.5rem',
                'font_weight' => '600',
            ],
        ],
        'fields' => [
            'level' => ['type' => 'select', 'label' => 'Уровень', 'options' => ['h1'=>'H1','h2'=>'H2','h3'=>'H3','h4'=>'H4','h5'=>'H5','h6'=>'H6']],
            'text' => ['type' => 'text', 'label' => 'Текст', 'required' => true],
            'align' => ['type' => 'select', 'label' => 'Выравнивание', 'options' => ['left'=>'По левому краю','center'=>'По центру','right'=>'По правому краю']],
            'color' => ['type' => 'color', 'label' => 'Цвет текста'],
            'font_size' => ['type' => 'select', 'label' => 'Размер шрифта', 'options' => ['1rem'=>'Маленький','1.25rem'=>'Средний','1.5rem'=>'Большой','2rem'=>'Огромный']],
        ],
    ],

    'text' => [
        'name' => 'Текст',
        'category' => 'content',
        'icon' => 'paragraph',
        'description' => 'Текстовый блок с поддержкой форматирования',
        'default' => [
            'type' => 'text',
            'settings' => [
                'content' => 'Текстовый блок...',
                'align' => 'left',
                'color' => '#4b5563',
                'font_size' => '1rem',
                'line_height' => '1.6',
            ],
        ],
        'fields' => [
            'content' => ['type' => 'textarea', 'label' => 'Текст', 'rows' => 6, 'required' => true],
            'align' => ['type' => 'select', 'label' => 'Выравнивание', 'options' => ['left'=>'По левому краю','center'=>'По центру','right'=>'По правому краю','justify'=>'По ширине']],
            'color' => ['type' => 'color', 'label' => 'Цвет текста'],
            'font_size' => ['type' => 'select', 'label' => 'Размер шрифта', 'options' => ['0.875rem'=>'Мелкий','1rem'=>'Обычный','1.125rem'=>'Крупный','1.25rem'=>'Очень крупный']],
        ],
    ],

    'button' => [
        'name' => 'Кнопка',
        'category' => 'content',
        'icon' => 'link',
        'description' => 'Кнопка с ссылкой и действием',
        'default' => [
            'type' => 'button',
            'settings' => [
                'text' => 'Кнопка',
                'url' => '#',
                'target' => '_self',
                'style' => 'primary',
                'size' => 'md',
                'icon' => null,
            ],
        ],
        'fields' => [
            'text' => ['type' => 'text', 'label' => 'Текст на кнопке', 'required' => true],
            'url' => ['type' => 'text', 'label' => 'URL ссылки', 'required' => true],
            'target' => ['type' => 'select', 'label' => 'Открытие ссылки', 'options' => ['_self'=>'В этой вкладке','_blank'=>'В новой вкладке']],
            'style' => ['type' => 'select', 'label' => 'Стиль', 'options' => ['primary'=>'Основной','secondary'=>'Вторичный','outline'=>'Контурный','ghost'=>'Прозрачный']],
            'size' => ['type' => 'select', 'label' => 'Размер', 'options' => ['sm'=>'Маленький','md'=>'Средний','lg'=>'Большой']],
        ],
    ],

    'image' => [
        'name' => 'Изображение',
        'category' => 'media',
        'icon' => 'image',
        'description' => 'Изображение с настройками отображения',
        'default' => [
            'type' => 'image',
            'settings' => [
                'media_id' => null,
                'url' => '',
                'alt' => '',
                'width' => '100%',
                'height' => 'auto',
                'radius' => 'none',
                'shadow' => 'none',
            ],
        ],
        'fields' => [
            'media_id' => ['type' => 'number', 'label' => 'ID медиафайла', 'required' => true],
            'url' => ['type' => 'text', 'label' => 'URL (если нет медиафайла)'],
            'alt' => ['type' => 'text', 'label' => 'Альтернативный текст'],
            'width' => ['type' => 'text', 'label' => 'Ширина (напр: 100%, 300px)'],
            'height' => ['type' => 'text', 'label' => 'Высота (напр: auto, 200px)'],
            'radius' => ['type' => 'select', 'label' => 'Скругление', 'options' => ['none'=>'Без скругления','sm'=>'Маленькое','md'=>'Среднее','lg'=>'Большое','full'=>'Круг']],
            'shadow' => ['type' => 'select', 'label' => 'Тень', 'options' => ['none'=>'Без тени','sm'=>'Маленькая','md'=>'Средняя','lg'=>'Большая']],
        ],
    ],

    'video' => [
        'name' => 'Видео',
        'category' => 'media',
        'icon' => 'video',
        'description' => 'Встроенное видео (YouTube, Vimeo или HTML5)',
        'default' => [
            'type' => 'video',
            'settings' => [
                'type' => 'youtube',
                'url' => '',
                'autoplay' => false,
                'loop' => false,
                'muted' => false,
                'controls' => true,
                'width' => '100%',
                'ratio' => '16:9',
            ],
        ],
        'fields' => [
            'type' => ['type' => 'select', 'label' => 'Тип видео', 'options' => ['youtube'=>'YouTube','vimeo'=>'Vimeo','html5'=>'HTML5']],
            'url' => ['type' => 'text', 'label' => 'URL видео', 'required' => true],
            'autoplay' => ['type' => 'toggle', 'label' => 'Автовоспроизведение'],
            'loop' => ['type' => 'toggle', 'label' => 'Повторять'],
            'muted' => ['type' => 'toggle', 'label' => 'Без звука'],
            'controls' => ['type' => 'toggle', 'label' => 'Показывать элементы управления'],
            'ratio' => ['type' => 'select', 'label' => 'Пропорции', 'options' => ['16:9'=>'16:9','4:3'=>'4:3','1:1'=>'1:1','21:9'=>'Киноширокий']],
        ],
    ],

    'gallery' => [
        'name' => 'Галерея изображений',
        'category' => 'media',
        'icon' => 'images',
        'description' => 'Сетка изображений с лайтбоксом',
        'default' => [
            'type' => 'gallery',
            'settings' => [
                'images' => [],
                'columns' => 3,
                'gap' => 'md',
                'radius' => 'md',
                'lightbox' => true,
            ],
        ],
        'fields' => [
            'images' => ['type' => 'repeater', 'label' => 'Изображения', 'fields' => [
                ['type' => 'number', 'key' => 'media_id', 'label' => 'ID медиафайла'],
                ['type' => 'text', 'key' => 'alt', 'label' => 'Альтернативный текст'],
            ]],
            'columns' => ['type' => 'select', 'label' => 'Колонки', 'options' => [1=>'1',2=>'2',3=>'3',4=>'4',5=>'5',6=>'6']],
            'gap' => ['type' => 'select', 'label' => 'Отступ между изображениями', 'options' => ['sm'=>'Маленький','md'=>'Средний','lg'=>'Большой']],
            'radius' => ['type' => 'select', 'label' => 'Скругление', 'options' => ['none'=>'Нет','sm'=>'Маленькое','md'=>'Среднее','lg'=>'Большое']],
            'lightbox' => ['type' => 'toggle', 'label' => 'Включить лайтбокс'],
        ],
    ],

    'icon' => [
        'name' => 'Иконка',
        'category' => 'content',
        'icon' => 'star',
        'description' => 'Одиночная иконка с настройками',
        'default' => [
            'type' => 'icon',
            'settings' => [
                'icon' => 'star',
                'size' => 'md',
                'color' => '#6b7280',
                'background' => null,
                'radius' => 'none',
            ],
        ],
        'fields' => [
            'icon' => ['type' => 'select', 'label' => 'Иконка', 'options' => ['star'=>'Звезда','heart'=>'Сердце','check'=>'Галочка','x'=>'Крестик','arrow'=>'Стрелка','dots'=>'Точки']],
            'size' => ['type' => 'select', 'label' => 'Размер', 'options' => ['sm'=>'Маленький','md'=>'Средний','lg'=>'Большой','xl'=>'Огромный']],
            'color' => ['type' => 'color', 'label' => 'Цвет иконки'],
            'background' => ['type' => 'color', 'label' => 'Фон иконки'],
            'radius' => ['type' => 'select', 'label' => 'Скругление', 'options' => ['none'=>'Нет','sm'=>'Маленькое','md'=>'Круг','lg'=>'Большое']],
        ],
    ],

    // LAYOUT BLOCKS
    'columns' => [
        'name' => 'Колонки',
        'category' => 'layout',
        'icon' => 'columns',
        'description' => 'Сетка из нескольких колонок',
        'default' => [
            'type' => 'columns',
            'settings' => [
                'count' => 2,
                'gap' => 'md',
                'columns' => [
                    ['blocks' => [], 'width' => 6],
                    ['blocks' => [], 'width' => 6],
                ],
            ],
        ],
        'fields' => [
            'count' => ['type' => 'select', 'label' => 'Количество колонок', 'options' => [2=>'2 колонки',3=>'3 колонки',4=>'4 колонки']],
            'gap' => ['type' => 'select', 'label' => 'Отступ между колонками', 'options' => ['sm'=>'Маленький','md'=>'Средний','lg'=>'Большой']],
        ],
    ],

    'container' => [
        'name' => 'Контейнер',
        'category' => 'layout',
        'icon' => 'box',
        'description' => 'Обертка с максимальной шириной и отступами',
        'default' => [
            'type' => 'container',
            'settings' => [
                'max_width' => '7xl',
                'padding' => ['top' => 16, 'bottom' => 16, 'left' => 4, 'right' => 4],
                'blocks' => [],
            ],
        ],
        'fields' => [
            'max_width' => ['type' => 'select', 'label' => 'Максимальная ширина', 'options' => ['sm'=>'640px','md'=>'768px','lg'=>'1024px','xl'=>'1280px','2xl'=>'1536px','3xl'=>'1792px','4xl'=>'2048px','5xl'=>'2560px','6xl'=>'2880px','7xl'=>'3200px']],
            'padding_top' => ['type' => 'number', 'label' => 'Отступ сверху (px)'],
            'padding_bottom' => ['type' => 'number', 'label' => 'Отступ снизу (px)'],
            'padding_left' => ['type' => 'number', 'label' => 'Отступ слева (px)'],
            'padding_right' => ['type' => 'number', 'label' => 'Отступ справа (px)'],
        ],
    ],

    'spacer' => [
        'name' => 'Распорка',
        'category' => 'layout',
        'icon' => 'arrows-expand',
        'description' => 'Пустой блок для создания отступов',
        'default' => [
            'type' => 'spacer',
            'settings' => [
                'height' => 32,
            ],
        ],
        'fields' => [
            'height' => ['type' => 'number', 'label' => 'Высота в пикселях', 'min' => 0, 'max' => 500],
        ],
    ],

    'divider' => [
        'name' => 'Разделитель',
        'category' => 'layout',
        'icon' => 'minus',
        'description' => 'Горизонтальная линия-разделитель',
        'default' => [
            'type' => 'divider',
            'settings' => [
                'style' => 'solid',
                'color' => '#e5e7eb',
                'thickness' => 1,
                'width' => '100%',
            ],
        ],
        'fields' => [
            'style' => ['type' => 'select', 'label' => 'Стиль линии', 'options' => ['solid'=>'Сплошная','dashed'=>'Пунктир','dotted'=>'Точками','double'=>'Двойная']],
            'color' => ['type' => 'color', 'label' => 'Цвет линии'],
            'thickness' => ['type' => 'number', 'label' => 'Толщина (px)', 'min' => 1, 'max' => 10],
            'width' => ['type' => 'text', 'label' => 'Ширина (напр: 100%, 50%)'],
        ],
    ],

    // DYNAMIC BLOCKS
    'news-feed' => [
        'name' => 'Лента новостей',
        'category' => 'dynamic',
        'icon' => 'newspaper',
        'description' => 'Динамическая лента последних публикаций',
        'default' => [
            'type' => 'news-feed',
            'settings' => [
                'count' => 6,
                'category' => null,
                'show_image' => true,
                'show_excerpt' => true,
                'show_date' => true,
                'columns' => 3,
                'layout' => 'grid',
            ],
        ],
        'fields' => [
            'count' => ['type' => 'number', 'label' => 'Количество записей', 'min' => 1, 'max' => 50],
            'category' => ['type' => 'select', 'label' => 'Категория', 'options' => ['all'=>'Все категории','news'=>'Новости','blog'=>'Блог','updates'=>'Обновления']],
            'show_image' => ['type' => 'toggle', 'label' => 'Показывать изображение'],
            'show_excerpt' => ['type' => 'toggle', 'label' => 'Показывать отрывок'],
            'show_date' => ['type' => 'toggle', 'label' => 'Показывать дату'],
            'columns' => ['type' => 'select', 'label' => 'Колонки', 'options' => [1=>'1',2=>'2',3=>'3',4=>'4']],
            'layout' => ['type' => 'select', 'label' => 'Вид', 'options' => ['grid'=>'Сетка','list'=>'Список','cards'=>'Карточки']],
        ],
    ],

    'testimonials' => [
        'name' => 'Отзывы',
        'category' => 'dynamic',
        'icon' => 'chat',
        'description' => 'Карусель или сетка отзывов клиентов',
        'default' => [
            'type' => 'testimonials',
            'settings' => [
                'testimonials' => [
                    [
                        'author' => 'Иван Иванов',
                        'role' => 'CEO, Компания',
                        'text' => 'Отличный сервис! Очень доволен работой.',
                        'rating' => 5,
                        'avatar' => null,
                    ],
                ],
                'layout' => 'carousel',
                'autoplay' => false,
                'show_rating' => true,
            ],
        ],
        'fields' => [
            'layout' => ['type' => 'select', 'label' => 'Вид', 'options' => ['carousel'=>'Карусель','grid'=>'Сетка','slider'=>'Слайдер']],
            'autoplay' => ['type' => 'toggle', 'label' => 'Автопрокрутка'],
            'show_rating' => ['type' => 'toggle', 'label' => 'Показывать рейтинг'],
        ],
    ],

    'counter' => [
        'name' => 'Счетчик',
        'category' => 'dynamic',
        'icon' => 'chart-bar',
        'description' => 'Анимированный счетчик чисел',
        'default' => [
            'type' => 'counter',
            'settings' => [
                'value' => 100,
                'suffix' => '+',
                'prefix' => '',
                'duration' => 2000,
                'label' => 'Проектов завершено',
            ],
        ],
        'fields' => [
            'value' => ['type' => 'number', 'label' => 'Значение', 'required' => true],
            'prefix' => ['type' => 'text', 'label' => 'Префикс (напр: $, #)'],
            'suffix' => ['type' => 'text', 'label' => 'Суффикс (напр: +, %)'],
            'duration' => ['type' => 'number', 'label' => 'Длительность анимации (мс)', 'min' => 100, 'max' => 5000],
            'label' => ['type' => 'text', 'label' => 'Подпись'],
        ],
    ],

    'pricing-table' => [
        'name' => 'Тарифы',
        'category' => 'dynamic',
        'icon' => 'credit-card',
        'description' => 'Таблица тарифных планов',
        'default' => [
            'type' => 'pricing-table',
            'settings' => [
                'plans' => [
                    [
                        'name' => 'Базовый',
                        'price' => 29,
                        'currency' => '$',
                        'period' => 'месяц',
                        'features' => ['Функция 1', 'Функция 2'],
                        'highlighted' => false,
                        'button_text' => 'Выбрать',
                    ],
                ],
                'columns' => 3,
            ],
        ],
        'fields' => [
            'columns' => ['type' => 'select', 'label' => 'Колонки', 'options' => [1=>'1',2=>'2',3=>'3',4=>'4']],
        ],
    ],

    'form' => [
        'name' => 'Форма',
        'category' => 'dynamic',
        'icon' => 'form',
        'description' => 'Контактная форма с валидацией',
        'default' => [
            'type' => 'form',
            'settings' => [
                'title' => 'Свяжитесь с нами',
                'description' => 'Оставьте ваши контакты, и мы свяжемся с вами',
                'fields' => [
                    ['type' => 'text', 'name' => 'name', 'label' => 'Имя', 'required' => true],
                    ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true],
                    ['type' => 'textarea', 'name' => 'message', 'label' => 'Сообщение', 'required' => true],
                ],
                'button_text' => 'Отправить',
                'success_message' => 'Сообщение успешно отправлено!',
                'action_url' => '/api/contact',
            ],
        ],
        'fields' => [
            'title' => ['type' => 'text', 'label' => 'Заголовок формы'],
            'description' => ['type' => 'textarea', 'label' => 'Описание', 'rows' => 3],
            'button_text' => ['type' => 'text', 'label' => 'Текст на кнопке'],
            'success_message' => ['type' => 'text', 'label' => 'Сообщение об успехе'],
            'action_url' => ['type' => 'text', 'label' => 'URL отправки формы'],
        ],
    ],

    // SEO & METADATA
    'seo-meta' => [
        'name' => 'SEO блок',
        'category' => 'seo',
        'icon' => 'search',
        'description' => 'SEO-данные для поисковых систем',
        'default' => [
            'type' => 'seo-meta',
            'settings' => [
                'title' => '',
                'description' => '',
                'keywords' => [],
                'robots' => 'index, follow',
                'canonical' => '',
            ],
        ],
        'fields' => [
            'title' => ['type' => 'text', 'label' => 'SEO заголовок'],
            'description' => ['type' => 'textarea', 'label' => 'SEO описание', 'rows' => 3],
            'keywords' => ['type' => 'text', 'label' => 'Ключевые слова (через запятую)'],
            'robots' => ['type' => 'select', 'label' => 'Правила для роботов', 'options' => ['index, follow'=>'Индексировать','noindex, follow'=>'Не индексировать','noindex, nofollow'=>'Не индексировать и не переходить']],
            'canonical' => ['type' => 'text', 'label' => 'Канонический URL'],
        ],
    ],

    // INTERACTIVE
    'accordion' => [
        'name' => 'Аккордеон',
        'category' => 'interactive',
        'icon' => 'chevron-down',
        'description' => 'Сворачиваемые блоки с контентом',
        'default' => [
            'type' => 'accordion',
            'settings' => [
                'items' => [
                    [
                        'title' => 'Вопрос 1',
                        'content' => 'Ответ на первый вопрос...',
                        'open' => false,
                    ],
                ],
                'allow_multiple' => false,
            ],
        ],
        'fields' => [
            'allow_multiple' => ['type' => 'toggle', 'label' => 'Разрешить открытыми несколько'],
        ],
    ],

    'tabs' => [
        'name' => 'Вкладки',
        'category' => 'interactive',
        'icon' => 'tab',
        'description' => 'Переключаемые вкладки с контентом',
        'default' => [
            'type' => 'tabs',
            'settings' => [
                'tabs' => [
                    ['title' => 'Вкладка 1', 'content' => 'Содержимое 1'],
                ],
                'style' => 'line',
                'alignment' => 'left',
            ],
        ],
        'fields' => [
            'style' => ['type' => 'select', 'label' => 'Стиль', 'options' => ['line'=>'Линия','boxed'=>'В рамке','pill'=>'Таблетки']],
            'alignment' => ['type' => 'select', 'label' => 'Выравнивание', 'options' => ['left'=>'По левому краю','center'=>'По центру','right'=>'По правому краю']],
        ],
    ],

    'modal' => [
        'name' => 'Модальное окно',
        'category' => 'interactive',
        'icon' => 'window',
        'description' => 'Окно, открывающееся поверх контента',
        'default' => [
            'type' => 'modal',
            'settings' => [
                'trigger_text' => 'Открыть модальное окно',
                'title' => 'Заголовок модального окна',
                'content' => 'Содержимое модального окна...',
                'size' => 'md',
            ],
        ],
        'fields' => [
            'trigger_text' => ['type' => 'text', 'label' => 'Текст кнопки вызова'],
            'title' => ['type' => 'text', 'label' => 'Заголовок модального окна'],
            'content' => ['type' => 'textarea', 'label' => 'Содержимое', 'rows' => 6],
            'size' => ['type' => 'select', 'label' => 'Размер', 'options' => ['sm'=>'Маленькое','md'=>'Среднее','lg'=>'Большое','xl'=>'Огромное']],
        ],
    ],

    'tooltip' => [
        'name' => 'Подсказка',
        'category' => 'interactive',
        'icon' => 'message-circle',
        'description' => 'Всплывающая подсказка при наведении',
        'default' => [
            'type' => 'tooltip',
            'settings' => [
                'text' => 'Наведите на меня',
                'content' => 'Это текст подсказки',
                'position' => 'top',
            ],
        ],
        'fields' => [
            'text' => ['type' => 'text', 'label' => 'Основной текст'],
            'content' => ['type' => 'text', 'label' => 'Текст подсказки'],
            'position' => ['type' => 'select', 'label' => 'Положение', 'options' => ['top'=>'Сверху','bottom'=>'Снизу','left'=>'Слева','right'=>'Справа']],
        ],
    ],

    // ECOMMERCE
    'product-card' => [
        'name' => 'Карточка товара',
        'category' => 'ecommerce',
        'icon' => 'shopping-bag',
        'description' => 'Карточка товара для интернет-магазина',
        'default' => [
            'type' => 'product-card',
            'settings' => [
                'image' => null,
                'title' => 'Название товара',
                'description' => 'Краткое описание товара',
                'price' => 99.99,
                'old_price' => null,
                'currency' => '₽',
                'rating' => 5,
                'reviews_count' => 10,
                'button_text' => 'В корзину',
            ],
        ],
        'fields' => [
            'image' => ['type' => 'media', 'label' => 'Изображение товара'],
            'title' => ['type' => 'text', 'label' => 'Название', 'required' => true],
            'description' => ['type' => 'textarea', 'label' => 'Описание', 'rows' => 2],
            'price' => ['type' => 'number', 'label' => 'Цена', 'required' => true, 'step' => 0.01],
            'old_price' => ['type' => 'number', 'label' => 'Старая цена', 'step' => 0.01],
            'currency', => ['type' => 'text', 'label' => 'Валюта', 'value' => '₽'],
            'rating' => ['type' => 'number', 'label' => 'Рейтинг', 'min' => 0, 'max' => 5, 'step' => 0.5],
            'reviews_count' => ['type' => 'number', 'label' => 'Количество отзывов'],
            'button_text' => ['type' => 'text', 'label' => 'Текст на кнопке'],
        ],
    ],

    'product-list' => [
        'name' => 'Список товаров',
        'category' => 'ecommerce',
        'icon' => 'list',
        'description' => 'Сетка или список товаров каталога',
        'default' => [
            'type' => 'product-list',
            'settings' => [
                'products' => [],
                'columns' => 4,
                'layout' => 'grid',
                'show_rating' => true,
                'show_price' => true,
                'show_add_to_cart' => true,
            ],
        ],
        'fields' => [
            'products' => ['type' => 'repeater', 'label' => 'Товары', 'fields' => [
                ['type' => 'text', 'key' => 'title', 'label' => 'Название'],
                ['type' => 'number', 'key' => 'price', 'label' => 'Цена'],
                ['type' => 'media', 'key' => 'image', 'label' => 'Изображение'],
            ]],
            'columns' => ['type' => 'select', 'label' => 'Колонки', 'options' => [1=>'1',2=>'2',3=>'3',4=>'4',5=>'5',6=>'6']],
            'layout' => ['type' => 'select', 'label' => 'Вид', 'options' => ['grid'=>'Сетка','list'=>'Список']],   
            'show_rating' => ['type' => 'toggle', 'label' => 'Показывать рейтинг'],
            'show_price' => ['type' => 'toggle', 'label' => 'Показывать цену'],
            'show_add_to_cart' => ['type' => 'toggle', 'label' => 'Показывать кнопку "В корзину"'],
        ],
    ],

    'cart' => [
        'name' => 'Корзина',
        'category' => 'ecommerce',
        'icon' => 'shopping-cart',
        'description' => 'Корзина покупок с товарами и итогом',
        'default' => [
            'type' => 'cart',
            'settings' => [
                'items' => [],
                'show_coupon' => true,
                'show_shipping' => true,
            ],
        ],
        'fields' => [
            'show_coupon' => ['type' => 'toggle', 'label' => 'Поле для промокода'],
            'show_shipping' => ['type' => 'toggle', 'label' => 'Выбор доставки'],
        ],
    ],

    // UTILITY
    'alert' => [
        'name' => 'Алерты',
        'category' => 'utility',
        'icon' => 'info',
        'description' => 'Уведомления для пользователей (инфо, успех, предупреждение, ошибка)',
        'default' => [
            'type' => 'alert',
            'settings' => [
                'type' => 'info',
                'title' => 'Информационное сообщение',
                'content' => 'Текст информационного сообщения...',
                'closable' => true,
            ],
        ],
        'fields' => [
            'type' => ['type' => 'select', 'label' => 'Тип алерта', 'options' => ['info'=>'Инфо','success'=>'Успех','warning'=>'Предупреждение','error'=>'Ошибка']],
            'title' => ['type' => 'text', 'label' => 'Заголовок'],
            'content' => ['type' => 'textarea', 'label' => 'Текст сообщения'],
            'closable' => ['type' => 'toggle', 'label' => 'Можно закрыть'],
        ],
    ],

    'progress-bar' => [
        'name' => 'Прогресс бар',
        'category' => 'utility',
        'icon' => 'loader',
        'description' => 'Визуальный индикатор прогресса',
        'default' => [
            'type' => 'progress-bar',
            'settings' => [
                'value' => 75,
                'max' => 100,
                'color' => '#3b82f6',
                'height' => 8,
                'show_label' => true,
            ],
        ],
        'fields' => [
            'value' => ['type' => 'number', 'label' => 'Текущее значение', 'min' => 0, 'required' => true],
            'max' => ['type' => 'number', 'label' => 'Максимальное значение', 'min' => 1, 'required' => true],
            'color' => ['type' => 'color', 'label' => 'Цвет полосы'],
            'height' => ['type' => 'number', 'label' => 'Высота (px)', 'min' => 2, 'max' => 50],
            'show_label' => ['type' => 'toggle', 'label' => 'Показывать процент'],
        ],
    ],

    'breadcrumbs' => [
        'name' => 'Хлебные крошки',
        'category' => 'utility',
        'icon' => 'link',
        'description' => 'Навигационная цепочка',
        'default' => [
            'type' => 'breadcrumbs',
            'settings' => [
                'items' => [
                    ['title' => 'Главная', 'url' => '/'],
                    ['title' => 'Текущая страница', 'url' => null],
                ],
                'separator' => '/',
            ],
        ],
        'fields' => [
            'separator' => ['type' => 'text', 'label' => 'Разделитель'],
        ],
    ],

    'collapse' => [
        'name' => 'Сворачиваемый блок',
        'category' => 'utility',
        'icon' => 'chevron-right',
        'description' => 'Блок, который можно сворачивать и разворачивать',
        'default' => [
            'type' => 'collapse',
            'settings' => [
                'title' => 'Заголовок',
                'content' => 'Скрытый контент...',
                'open' => false,
            ],
        ],
        'fields' => [
            'title' => ['type' => 'text', 'label' => 'Заголовок'],
            'open' => ['type' => 'toggle', 'label' => 'Открыт по умолчанию'],
        ],
    ],

];

// Register all blocks
foreach ($blocks as $type => $config) {
    \App\Builder\Config\BlockRegistry::register($type, $config);
}

return $blocks;
