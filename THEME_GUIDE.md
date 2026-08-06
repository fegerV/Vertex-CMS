# Глобальные настройки темы (Design Tokens)

Этот документ описывает систему дизайн-токенов для оформления сайта.

## 📁 Файловая структура

```
resources/css/
├── theme.css          # Основные CSS переменные и глобальные стили
├── app.css            # Главный файл стилей (импортирует theme.css)
├── builder-components.css
└── builder-editor.css

themes/default/
└── theme.json         # JSON конфигурация темы
```

## 🎨 Цветовая палитра

### Светлая тема (Light)

| Переменная | Значение | Описание |
|------------|----------|----------|
| `--vc-bg` | `#f4f7fb` | Основной фон |
| `--vc-bg-accent` | gradient | Акцентный фон с градиентами |
| `--vc-surface` | `rgba(255, 255, 255, 0.88)` | Поверхность |
| `--vc-surface-strong` | `rgba(255, 255, 255, 0.96)` | Плотная поверхность |
| `--vc-surface-muted` | `rgba(241, 245, 249, 0.92)` | Приглушенная поверхность |
| `--vc-border` | `rgba(148, 163, 184, 0.22)` | Граница |
| `--vc-border-strong` | `rgba(100, 116, 139, 0.28)` | Плотная граница |
| `--vc-text` | `#0f172a` | Основной текст |
| `--vc-text-muted` | `#526074` | Приглушенный текст |
| `--vc-text-soft` | `#6b7a90` | Мягкий текст |
| `--vc-primary` | `#0f766e` | Основной цвет бренда |
| `--vc-primary-strong` | `#115e59` | Насыщенный основной |
| `--vc-primary-contrast` | `#f4fffd` | Контрастный для основного |
| `--vc-danger` | `#be123c` | Ошибка/опасность |
| `--vc-success` | `#16a34a` | Успех |
| `--vc-warning` | `#ca8a04` | Предупреждение |
| `--vc-info` | `#0284c7` | Информация |

### Темная тема (Dark)

| Переменная | Значение | Описание |
|------------|----------|----------|
| `--vc-bg` | `#07111f` | Основной фон |
| `--vc-surface` | `rgba(12, 21, 37, 0.86)` | Поверхность |
| `--vc-text` | `#e5eefc` | Основной текст |
| `--vc-primary` | `#2dd4bf` | Основной цвет бренда |
| `--vc-primary-strong` | `#14b8a6` | Насыщенный основной |

## 🔤 Шрифты

| Переменная | Значение |
|------------|----------|
| `--vc-font-sans` | `"Manrope", "Segoe UI", ui-sans-serif, system-ui, sans-serif` |
| `--vc-font-serif` | `"Georgia", "Times New Roman", serif` |
| `--vc-font-mono` | `"Fira Code", "Consolas", monospace` |
| `--vc-font-heading` | `var(--vc-font-sans)` |
| `--vc-font-body` | `var(--vc-font-sans)` |

## 📐 Типографика

### Заголовки

| Элемент | Размер | Вес | Letter Spacing | Margin Bottom |
|---------|--------|-----|----------------|---------------|
| H1 | 2.5rem | 700 | -0.02em | 1.5rem |
| H2 | 2rem | 700 | -0.01em | 1.25rem |
| H3 | 1.75rem | 600 | -0.01em | 1rem |
| H4 | 1.5rem | 600 | 0 | 0.875rem |
| H5 | 1.25rem | 600 | 0 | 0.75rem |
| H6 | 1rem | 600 | 0 | 0.5rem |

### Базовые настройки

- **Базовый размер**: 16px
- **Базовая высота строки**: 1.7
- **Высота строки заголовков**: 1.3

## 📏 Отступы и размеры

| Переменная | Значение |
|------------|----------|
| `--vc-section-padding` | 64px |
| `--vc-container-max-width` | 1200px |
| `--vc-gutter` | 20px |

### Шкала отступов (в rem)

`[0, 0.25, 0.5, 0.75, 1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10, 12, 16, 20, 24]`

## 🔘 Радиусы скругления

| Класс | Переменная | Значение |
|-------|------------|----------|
| `.rounded-vc-sm` | `--vc-radius-sm` | 8px |
| `.rounded-vc-md` | `--vc-radius-md` | 14px |
| `.rounded-vc-lg` | `--vc-radius-lg` | 18px |
| `.rounded-vc-xl` | `--vc-radius-xl` | 24px |
| `.rounded-vc-full` | `--vc-radius-full` | 9999px |

## 🌑 Тени

| Класс | Переменная | Значение |
|-------|------------|----------|
| `.shadow-vc` | `--vc-shadow` | `0 24px 80px rgba(15, 23, 42, 0.08)` |
| `.shadow-vc-soft` | `--vc-shadow-soft` | `0 12px 32px rgba(15, 23, 42, 0.08)` |

## 📱 Breakpoints (контрольные точки)

| Название | Ширина |
|----------|--------|
| xs | 480px |
| sm | 640px |
| md | 768px |
| lg | 1024px |
| xl | 1280px |
| 2xl | 1536px |

## ⚡ Переходы (Transitions)

| Класс | Переменная | Значение |
|-------|------------|----------|
| `.transition-vc-fast` | `--vc-transition-fast` | 150ms ease |
| `.transition-vc-base` | `--vc-transition-base` | 180ms ease |
| `.transition-vc-slow` | `--vc-transition-slow` | 300ms ease |

## 📊 Z-Index система

| Переменная | Значение | Использование |
|------------|----------|---------------|
| `--vc-z-base` | 1 | Базовый уровень |
| `--vc-z-dropdown` | 1000 | Выпадающие меню |
| `--vc-z-sticky` | 1020 | Липкие элементы |
| `--vc-z-fixed` | 1030 | Фиксированные элементы |
| `--vc-z-modal-backdrop` | 1040 | Фон модального окна |
| `--vc-z-modal` | 1050 | Модальные окна |
| `--vc-z-popover` | 1060 | Popover |
| `--vc-z-tooltip` | 1070 | Подсказки |

## 🎯 Полезные классы

### Текст
- `.text-primary` - основной цвет бренда
- `.text-danger` - цвет ошибки
- `.text-success` - цвет успеха
- `.text-warning` - цвет предупреждения
- `.text-info` - цвет информации
- `.text-muted` - приглушенный текст
- `.text-soft` - мягкий текст

### Фон
- `.bg-surface` - фон поверхности
- `.bg-surface-strong` - фон плотной поверхности
- `.bg-surface-muted` - фон приглушенной поверхности

### Границы
- `.border-default` - стандартная граница
- `.border-strong` - плотная граница

## 💡 Как использовать

### В CSS
```css
.element {
    color: var(--vc-text);
    background-color: var(--vc-surface);
    border-radius: var(--vc-radius-lg);
    box-shadow: var(--vc-shadow-soft);
}
```

### В HTML
```html
<div class="bg-surface rounded-vc-lg shadow-vc-soft p-6">
    <h2 class="text-primary">Заголовок</h2>
    <p class="text-muted">Описание</p>
</div>
```

### Переключение темы
```javascript
// Переключение на темную тему
document.documentElement.setAttribute('data-theme', 'dark');

// Переключение на светлую тему
document.documentElement.setAttribute('data-theme', 'light');
```

## 🔄 Интеграция с Tailwind CSS

Для использования переменных темы в Tailwind, добавьте их в `tailwind.config.js`:

```javascript
export default {
    theme: {
        extend: {
            colors: {
                vc: {
                    bg: 'var(--vc-bg)',
                    surface: 'var(--vc-surface)',
                    text: 'var(--vc-text)',
                    primary: 'var(--vc-primary)',
                    // ... другие цвета
                }
            },
            fontFamily: {
                sans: ['var(--vc-font-sans)'],
                heading: ['var(--vc-font-heading)'],
            },
            borderRadius: {
                'vc-sm': 'var(--vc-radius-sm)',
                'vc-md': 'var(--vc-radius-md)',
                'vc-lg': 'var(--vc-radius-lg)',
                'vc-xl': 'var(--vc-radius-xl)',
            }
        }
    }
}
```
