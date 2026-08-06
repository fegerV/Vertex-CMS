# План доработки и рефакторинга Page Builder (VertexCMS)

## 📊 Текущий статус готовности: ~65-70%

---

## ✅ ЧТО УЖЕ РЕАЛИЗОВАНО

### 1. Бэкенд архитектура (90% готово)
- **BlockRegistry.php** — реестр блоков с регистрацией и категоризацией
- **PageBuilderService.php** — сервис управления (валидация, экспорт/импорт, версионирование)
- **PageRenderer.php** — рендеринг блоков (только 5 из 28 типов)
- **BlockDefinitionService.php** — заготовка для определений
- **3 контроллера**: PageBuilderController, AdvancedBuilderController, BuilderApiController

### 2. Конфигурация блоков (100% готово)
**28 типов блоков по 8 категориям:**

| Категория | Блоки | Статус |
|-----------|-------|--------|
| **Content** (4) | heading, text, button, icon | ✅ Конфиг |
| **Media** (3) | image, video, gallery | ✅ Конфиг |
| **Layout** (4) | columns, container, spacer, divider | ✅ Конфиг |
| **Dynamic** (4) | news-feed, testimonials, counter, pricing-table, form | ✅ Конфиг |
| **SEO** (1) | seo-meta | ✅ Конфиг |
| **Interactive** (4) | accordion, tabs, modal, tooltip | ✅ Конфиг |
| **E-commerce** (3) | product-card, product-list, cart | ✅ Конфиг |
| **Utility** (4) | alert, progress-bar, breadcrumbs, collapse | ✅ Конфиг |

### 3. UI редактора (40% готово)
- ✅ Панель библиотеки блоков (sidebar)
- ✅ Canvas для предпросмотра
- ✅ Панель настроек (только для heading, text, button, faq, html)
- ✅ Базовые операции: добавить, удалить, переместить, сохранить
- ❌ Нет drag-and-drop (только click-to-add)
- ❌ Нет настроек для 23 из 28 блоков
- ❌ Нет превью для большинства блоков

### 4. Frontend рендеринг (15% готово)
- ✅ PageRenderer рендерит: heading, text, button, divider, faq, html
- ❌ Не рендерит: image, video, gallery, icon, columns, container, spacer, news-feed, testimonials, counter, pricing-table, form, seo-meta, accordion, tabs, modal, tooltip, product-card, product-list, cart, alert, progress-bar, breadcrumbs, collapse (22 блока)
- ❌ Нет CSS стилей для большинства блоков
- ❌ Нет JavaScript для интерактивных элементов

---

## 🔴 КРИТИЧЕСКИЕ ПРОБЛЕМЫ

### 1. PageRenderer не полный
**Файл:** `/workspace/app/Builder/Services/PageRenderer.php`
- Реализовано только 6 методов рендеринга из 28
- Отсутствуют методы для media, layout, dynamic, interactive, e-commerce блоков

### 2. Отсутствуют Blade-шаблоны блоков
**Директория:** `/workspace/resources/views/builder/blocks/` — не существует
- Нет отдельных шаблонов для каждого типа блока
- Вся логика в PageRenderer (нарушение SRP)

### 3. Нет JavaScript для интерактивности
**Директория:** `/workspace/resources/js/builder/` — не существует
- Accordion, tabs, modal, tooltip требуют JS
- Counter требует анимацию
- News-feed может требовать AJAX
- Cart требует динамики

### 4. CSS стилизация минимальна
**Файл:** `/workspace/resources/css/app.css` (60 строк)
- Есть стили только для: section, container, heading, text, button, divider, faq
- Отсутствуют стили для 22 блоков

### 5. UI редактора неполный
**Файл:** `/workspace/resources/views/admin/builder/edit.blade.php`
- Настройки только для 4 типов блоков
- Нет превью для image, video, gallery, columns, container
- Нет drag-and-drop сортировки
- Нет nested blocks support (для columns, container)

---

## 📋 ПЛАН ДОРАБОТКИ (по приоритетам)

### 🔥 ПРИОРИТЕТ 1: Завершить базовый рендеринг (2-3 дня)

#### 1.1 Дополнить PageRenderer.php
**Файл:** `app/Builder/Services/PageRenderer.php`

Добавить методы рендеринга для всех недостающих блоков:

```php
// Media blocks
private function image(array $settings): string
private function video(array $settings): string
private function gallery(array $settings): string
private function icon(array $settings): string

// Layout blocks
private function columns(array $settings): string
private function container(array $settings): string
private function spacer(array $settings): string

// Dynamic blocks
private function newsFeed(array $settings): string
private function testimonials(array $settings): string
private function counter(array $settings): string
private function pricingTable(array $settings): string
private function form(array $settings): string

// Interactive blocks
private function accordion(array $settings): string
private function tabs(array $settings): string
private function modal(array $settings): string
private function tooltip(array $settings): string

// E-commerce blocks
private function productCard(array $settings): string
private function productList(array $settings): string
private function cart(array $settings): string

// Utility blocks
private function alert(array $settings): string
private function progressBar(array $settings): string
private function breadcrumbs(array $settings): string
private function collapse(array $settings): string
```

#### 1.2 Расширить CSS стили
**Файл:** `resources/css/app.css`

Добавить стили для всех блоков (~300-400 строк):
```css
/* Media */
.vc-image { }
.vc-image--responsive { }
.vc-video { }
.vc-video__wrapper { }
.vc-gallery { }
.vc-gallery__grid { }
.vc-icon { }

/* Layout */
.vc-columns { }
.vc-column { }
.vc-container { }
.vc-spacer { }
.vc-divider { }

/* Dynamic */
.vc-news-feed { }
.vc-news-card { }
.vc-testimonials { }
.vc-testimonial-item { }
.vc-counter { }
.vc-pricing-table { }
.vc-pricing-plan { }
.vc-form { }
.vc-form__field { }

/* Interactive */
.vc-accordion { }
.vc-accordion__item { }
.vc-tabs { }
.vc-tab__list { }
.vc-tab__content { }
.vc-modal { }
.vc-tooltip { }

/* E-commerce */
.vc-product-card { }
.vc-product-list { }
.vc-cart { }

/* Utility */
.vc-alert { }
.vc-progress-bar { }
.vc-breadcrumbs { }
.vc-collapse { }
```

#### 1.3 Создать Blade-шаблоны блоков
**Директория:** `resources/views/builder/blocks/`

Создать 28 файлов:
```
heading.blade.php
text.blade.php
button.blade.php
image.blade.php
video.blade.php
gallery.blade.php
icon.blade.php
columns.blade.php
container.blade.php
spacer.blade.php
divider.blade.php
news-feed.blade.php
testimonials.blade.php
counter.blade.php
pricing-table.blade.php
form.blade.php
seo-meta.blade.php
accordion.blade.php
tabs.blade.php
modal.blade.php
tooltip.blade.php
product-card.blade.php
product-list.blade.php
cart.blade.php
alert.blade.php
progress-bar.blade.php
breadcrumbs.blade.php
collapse.blade.php
```

Пример `heading.blade.php`:
```blade
@php
    $level = in_array($level ?? 'h2', ['h1','h2','h3','h4','h5','h6']) ? $level : 'h2';
    $style = collect([
        'color' => $color ?? null,
        'text-align' => $align ?? null,
        'font-size' => $font_size ?? null,
        'font-weight' => $font_weight ?? null,
    ])->filter()->implode('; ');
@endphp

<{{ $level }} class="vc-heading" style="{{ $style }}">
    {{ $text }}
</{{ $level }}>
```

---

### 🔥 ПРИОРИТЕТ 2: Добавить JavaScript интерактивность (2-3 дня)

#### 2.1 Создать структуру JS
**Директория:** `resources/js/builder/`

```
resources/js/builder/
├── app.js (точка входа)
├── components/
│   ├── Accordion.js
│   ├── Tabs.js
│   ├── Modal.js
│   ├── Tooltip.js
│   ├── Counter.js
│   ├── Lightbox.js (для gallery)
│   └── Carousel.js (для testimonials)
└── utils/
    ├── animations.js
    └── helpers.js
```

#### 2.2 Реализовать компоненты

**Accordion.js:**
```javascript
export class Accordion {
    constructor(element) {
        this.element = element;
        this.items = element.querySelectorAll('.vc-accordion__item');
        this.init();
    }
    
    init() {
        this.items.forEach(item => {
            const trigger = item.querySelector('.vc-accordion__trigger');
            trigger.addEventListener('click', () => this.toggle(item));
        });
    }
    
    toggle(item) {
        // логика переключения
    }
}
```

**Tabs.js:**
```javascript
export class Tabs {
    constructor(element) {
        this.element = element;
        this.tabList = element.querySelector('.vc-tab__list');
        this.contents = element.querySelectorAll('.vc-tab__content');
        this.init();
    }
    
    init() {
        // переключение табов
    }
}
```

**Counter.js:**
```javascript
export class Counter {
    constructor(element) {
        this.element = element;
        this.value = parseInt(element.dataset.value);
        this.duration = parseInt(element.dataset.duration) || 2000;
        this.init();
    }
    
    init() {
        // анимация счетчика при скролле
    }
}
```

#### 2.3 Точка входа `builder/app.js`
```javascript
import { Accordion } from './components/Accordion';
import { Tabs } from './components/Tabs';
import { Modal } from './components/Modal';
import { Tooltip } from './components/Tooltip';
import { Counter } from './components/Counter';
import { Lightbox } from './components/Lightbox';
import { Carousel } from './components/Carousel';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.vc-accordion').forEach(el => new Accordion(el));
    document.querySelectorAll('.vc-tabs').forEach(el => new Tabs(el));
    document.querySelectorAll('.vc-modal').forEach(el => new Modal(el));
    document.querySelectorAll('[data-tooltip]').forEach(el => new Tooltip(el));
    document.querySelectorAll('.vc-counter').forEach(el => new Counter(el));
    document.querySelectorAll('.vc-gallery').forEach(el => new Lightbox(el));
    document.querySelectorAll('.vc-testimonials--carousel').forEach(el => new Carousel(el));
});
```

---

### 🔥 ПРИОРИТЕТ 3: Доработать UI редактора (3-4 дня)

#### 3.1 Добавить настройки для всех блоков
**Файл:** `resources/views/admin/builder/edit.blade.php`

Добавить секции настроек для каждого типа блока (сейчас только 4):
- Image settings (media picker, alt, width, height, radius, shadow)
- Video settings (type, url, autoplay, loop, controls, ratio)
- Gallery settings (images repeater, columns, gap, lightbox toggle)
- Columns settings (count, gap, nested blocks editor)
- Container settings (max_width, padding, nested blocks)
- И так далее для всех 28 блоков

#### 3.2 Реализовать drag-and-drop
Заменить текущий click-to-add на полноценный drag-and-drop:

```javascript
// Использовать SortableJS или HTML5 Drag&Drop API
import Sortable from 'sortablejs';

const canvas = document.querySelector('#blocks-canvas');
new Sortable(canvas, {
    animation: 150,
    ghostClass: 'block-ghost',
    onEnd: (evt) => {
        // обновить порядок в content массиве
    }
});
```

#### 3.3 Добавить поддержку вложенных блоков
Для `columns` и `container` блоков:
- Визуальное отображение колонок/контейнера
- Возможность добавлять блоки внутрь
- Редактирование вложенных блоков

#### 3.4 Улучшить превью блоков
- Image: реальное отображение изображения из media library
- Video: embed preview (YouTube/Vimeo iframe)
- Gallery: сетка миниатюр
- News-feed: моковые данные для превью
- Product-card: карточка товара с изображением

---

### 🔥 ПРИОРИТЕТ 4: Рефакторинг архитектуры (2-3 дня)

#### 4.1 Разделить ответственность PageRenderer
**Текущая проблема:** PageRenderer содержит всю логику рендеринга (2000+ строк потенциально)

**Решение:** Создать отдельные renderer классы:
```
app/Builder/Renderers/
├── BlockRendererInterface.php
├── BaseBlockRenderer.php
├── ContentBlockRenderer.php (heading, text, button, icon)
├── MediaBlockRenderer.php (image, video, gallery)
├── LayoutBlockRenderer.php (columns, container, spacer, divider)
├── DynamicBlockRenderer.php (news-feed, testimonials, counter, pricing, form)
├── InteractiveBlockRenderer.php (accordion, tabs, modal, tooltip)
├── EcommerceBlockRenderer.php (product-card, product-list, cart)
└── UtilityBlockRenderer.php (alert, progress, breadcrumbs, collapse)
```

**PageRenderer.php** становится диспетчером:
```php
class PageRenderer
{
    public function __construct(
        private ContentBlockRenderer $contentRenderer,
        private MediaBlockRenderer $mediaRenderer,
        private LayoutBlockRenderer $layoutRenderer,
        // ...
    ) {}
    
    public function renderBlock(array $block): string
    {
        return match ($block['category']) {
            'content' => $this->contentRenderer->render($block),
            'media' => $this->mediaRenderer->render($block),
            'layout' => $this->layoutRenderer->render($block),
            // ...
        };
    }
}
```

#### 4.2 Внедрить Blade рендеринг вместо string concatenation
**Текущая проблема:** PageRenderer использует конкатенацию строк (сложно поддерживать)

**Решение:** Использовать Blade::render():
```php
public function renderBlock(array $block): string
{
    $view = 'builder.blocks.' . $block['type'];
    
    if (!view()->exists($view)) {
        return '<!-- Unknown block: ' . e($block['type']) . ' -->';
    }
    
    return view($view, $block['settings'])->render();
}
```

#### 4.3 Создать фабрику блоков
**Файл:** `app/Builder/Factories/BlockFactory.php`
```php
class BlockFactory
{
    public static function create(string $type, array $settings = []): array
    {
        $config = BlockRegistry::get($type);
        
        if (!$config) {
            throw new \InvalidArgumentException("Unknown block type: {$type}");
        }
        
        return array_merge_recursive(
            $config['default'],
            ['settings' => $settings]
        );
    }
}
```

---

### 🔥 ПРИОРИТЕТ 5: Дополнительные улучшения (2-3 дня)

#### 5.1 Добавить пресеты и шаблоны
**Файл:** `resources/views/admin/builder/templates/`

Создать предустановленные комбинации блоков:
- Hero section (heading + text + button + image)
- Features (3 колонки с icon + heading + text)
- CTA section (heading + text + button)
- Testimonials carousel
- Pricing table
- Contact form section

#### 5.2 Улучшить работу с медиа
- Интеграция с Media Library через modal picker
- Cropping и оптимизация изображений
- Lazy loading для изображений и видео
- Responsive images (srcset)

#### 5.3 Добавить SEO-оптимизацию
- Автоматическая генерация alt для изображений
- Schema.org микроразметка для блоков
- Оптимизация заголовков (иерархия h1-h6)

#### 5.4 Производительность
- Ленивая загрузка тяжелых блоков
- Минификация CSS/JS для production
- Кэширование рендеринга блоков
- Critical CSS extraction

---

## 📅 ОБЩАЯ ОЦЕНКА ВРЕМЕНИ

| Приоритет | Задачи | Время |
|-----------|--------|-------|
| 1 | Базовый рендеринг (22 метода + CSS + Blade templates) | 2-3 дня |
| 2 | JavaScript интерактивность (7 компонентов) | 2-3 дня |
| 3 | UI редактора (настройки + drag-n-drop + nested) | 3-4 дня |
| 4 | Рефакторинг архитектуры (рендереры + фабрика) | 2-3 дня |
| 5 | Дополнительные улучшения | 2-3 дня |
| **Итого** | | **11-16 дней** |

---

## 🎯 ИТОГОВЫЙ REZULTAT

После выполнения плана:
- ✅ 100% блоков имеют рендеринг
- ✅ Все интерактивные элементы работают
- ✅ Полный UI редактора с drag-and-drop
- ✅ Чистая архитектура с разделением ответственности
- ✅ Готовность к production использованию
- ✅ Аналог Breakdance WP по функциональности

---

## 📝 ЗАМЕЧАНИЯ

1. **Не ломать существующий функционал** — все изменения должны быть обратно совместимы
2. **Покрыть тестами** — критические компоненты рендеринга
3. **Документировать** — каждый новый блок и компонент
4. **Оптимизировать** — производительность важна для frontend
