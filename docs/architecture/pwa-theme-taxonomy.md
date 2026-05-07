# PWA, Theme, Taxonomy

## Адаптивный шаблон

Публичный шаблон VertexCMS должен быть responsive-first.

Базовые требования:

- mobile-first CSS;
- корректные breakpoints для mobile, tablet, desktop;
- lazy loading изображений;
- responsive images через `srcset`, когда thumbnails готовы;
- CSS без зависимости от админской SPA;
- минимальный публичный JavaScript;
- доступные heading levels;
- корректные `alt` для изображений;
- server-rendered HTML через Blade.

Page Builder должен хранить настройки адаптивности в JSON:

```json
{
  "columns": [
    {
      "width_desktop": 6,
      "width_tablet": 12,
      "width_mobile": 12
    }
  ]
}
```

Acceptance criteria:

- Простая страница выглядит корректно на 360px, 768px, 1024px и 1440px.
- Публичная страница работает без JavaScript, если на ней нет интерактивных блоков.
- Изображения не ломают layout на мобильных.

## PWA

PWA стоит заложить сразу как архитектурную возможность, но не перегружать MVP v0.1.

MVP v0.1:

- подготовить настройки сайта для иконок, цветов и URL;
- не блокировать будущий service worker;
- держать публичный frontend лёгким.

v0.4:

- manifest generator;
- service worker;
- offline fallback;
- настройки cache strategy;
- PWA icons;
- installable site checks.

Настройки:

```txt
pwa.enabled
pwa.name
pwa.short_name
pwa.theme_color
pwa.background_color
pwa.display
pwa.start_url
pwa.offline_page_id
pwa.icon_192
pwa.icon_512
```

Routes:

```txt
GET /manifest.webmanifest
GET /offline
GET /service-worker.js
```

Acceptance criteria для PWA:

- Manifest генерируется из настроек.
- Service worker кеширует shell и offline page.
- При отключении сети открывается offline fallback.
- PWA можно установить в браузере, если включены настройки.

## Theme system

Theme system должен появиться до полноценного marketplace, но после MVP Foundation.

Базовая структура:

```txt
themes/
└── default/
    ├── theme.json
    ├── views/
    ├── assets/
    └── blocks/
```

`theme.json`:

```json
{
  "name": "Default",
  "slug": "default",
  "version": "0.1.0",
  "core_constraint": "^0.1",
  "supports": {
    "pwa": true,
    "builder": true,
    "responsive": true
  }
}
```

Renderer должен искать partials в таком порядке:

1. активная тема;
2. default theme;
3. core fallback partial.

## Нужна ли таксономия

Короткий ответ: да, таксономию нужно заложить архитектурно, но не обязательно делать полноценной частью MVP v0.1.

Почему таксономия нужна:

- CMS быстро выйдет за пределы обычных страниц;
- блоги, новости, кейсы, услуги, база знаний и каталоги требуют категорий и тегов;
- мобильное API будет удобнее, если контент можно фильтровать по terms;
- SEO-архивы категорий дают отдельные посадочные страницы;
- будущие content types смогут использовать общий механизм классификации.

Почему не стоит перегружать v0.1:

- MVP фокусируется на установке, админке, страницах, builder, SEO и media;
- таксономия добавит UI, API, SEO, sitemap и migration scope;
- без content types полноценная taxonomy может быть преждевременной.

Решение:

- В v0.1 не реализовывать полный UI таксономии.
- В v0.1 не блокировать архитектуру страниц и API.
- В v0.5 добавить полноценный модуль taxonomy.
- Если нужен минимальный компромисс раньше, добавить только `categories` и `tags` для pages в v0.2/v0.3.

## Предлагаемая модель taxonomy

```txt
taxonomies
- id
- name
- slug
- entity_type
- hierarchical
- settings_json
- created_at
- updated_at

terms
- id
- taxonomy_id
- parent_id
- name
- slug
- description
- sort_order
- seo_json
- created_at
- updated_at

termables
- term_id
- termable_type
- termable_id
```

Примеры:

- taxonomy `category`, hierarchical `true`;
- taxonomy `tag`, hierarchical `false`;
- taxonomy `service_type`, hierarchical `true`;
- taxonomy `case_industry`, hierarchical `false`.

Acceptance criteria для будущей taxonomy:

- Admin может создать taxonomy.
- Admin может создать term.
- Term можно привязать к странице.
- Публичный API возвращает страницы по term.
- Sitemap может включать архивы terms.
- SEO meta можно задать для term archive.
