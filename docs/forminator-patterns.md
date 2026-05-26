# Паттерны Forminator для конструктора форм VertexCMS

> Анализ оригинального плагина Forminator v1.51.0 (WPMU DEV).
> Цель: извлечь проверенные архитектурные решения без копирования WordPress-специфичного кода.

---

## 1. Общая архитектура

### 1.1 Трёхуровневая организация

```
Plugin bootstrap (forminator.php / constants.php)
        │
        ▼
   Core Singleton (Forminator_Core)
    ├── Modules registry
    ├── Fields registry
    ├── Admin router
    └── Upgrade / Protection / Export
        │
        ▼
   Per-module: Form / Poll / Quiz
    ├── Model  (Forminator_Form_Model, Poll_Model, Quiz_Model)
    ├── Front  (Forminator_CForm_Front — frontend renderer)
    ├── Admin  (Forminator_CForm_Admin — admin editor)
    └── Action (Forminator_CForm_Front_Action — submission handler)
```

**Урок для VertexCMS:**
- Сделать `BuilderCore` как синглтон, регистрирующий модули (Forms, Quizzes, Polls и т.д.)
- Каждый модуль имеет отдельную папку с Model / Front / Admin

---

### 1.2 Singleton через `get_instance()`

```php
// Форминатор
Forminator_Core::get_instance();
Forminator_Field::get_instance();

// Модель
Forminator_Form_Model::model()->load($id);
// возвращает инстанс класса

// Рендерер
Forminator_Render_Form::get_instance();
```

**Урок:** Использовать синглтон с кешированием экземпляра (`static $instance`), но для моделей делать также статический фабричный метод `::model()`, который возвращает новый инстанс с готовыми дефолтными значениями.

---

### 1.3 Хранение данных: CPT + Post Meta

WordPress Custom Post Type:
```
post_type: forminator_forms
post_title: "Контактная форма"
post_status: publish | draft
```

Post meta ключ:
```
forminator_form_meta (array)
├── settings   (form settings: colors, emails, conditions)
├── fields     (ordered array of field objects)
├── notifications (email/routing rules)
├── behaviors     (redirect, message on submit)
├── integration_conditions
└── client_id     (UUID для идентификации формы)
```

**Урок для VertexCMS:**
- Использовать отдельную таблицу `builder_pages` вместо CPT.
- Структура колонок: `id | name | type | status | content_json | settings_json | created_at | updated_at`
- `content_json` хранит дерево нод (sections/rows/columns/blocks).
- `settings_json` хранит глобальные настройки страницы.

---

## 2. Модель данных (Models)

### 2.1 Иерархия моделей

```
Forminator_Base_Form_Model  (abstract)
    ├── id, name, status, settings, fields
    ├── save()          — upsert через wp_insert_post / wp_update_post
    ├── load($id)       — загрузка из БД + парсинг меты
    ├── get_fields()    — возвращает Forminator_Form_Field_Model[]
    ├── add_field()     — добавляет поле в массив
    ├── to_array()      — сериализует в массив
    ├── to_json()       — сериализует в JSON
    ├── to_exportable_data() — для импорта/экспорта
    └── create_from_import_data() — импорт из JSON
```

**Урок для VertexCMS:**
- `BasePageModel` с той же схемой: `id`, `name`, `type`, `status`, `content`, `settings`.
- Метод `save()` делает `INSERT ... ON DUPLICATE KEY UPDATE` или Eloquent `updateOrCreate`.
- Метод `load($id)` заполняет модель из БД.
- Метод `to_array()` / `to_json()` для экспорта.

---

### 2.2 Карта свойств (Property Mapping)

```php
public function get_default_maps() {
    return [
        ['type' => 'post',  'property' => 'id',    'field' => 'ID'],
        ['type' => 'post',  'property' => 'name',  'field' => 'post_title'],
        ['type' => 'post',  'property' => 'status','field' => 'post_status'],
        ['type' => 'meta',  'property' => 'settings', 'field' => 'settings'],
        ['type' => 'meta',  'property' => 'fields',   'field' => 'fields'],
    ];
}
```

Children модели могут расширять `get_maps()` для добавления своих свойств.

**Урок:** Сделать массив маппингов для разделения полей, хранящихся в основных колонках, от JSON-полей в `settings_json`.

---

### 2.3 Валидация при сохранении

```php
$validate = forminator_validate_registration_form_settings($meta_data['settings']);
if (is_wp_error($validate)) return $validate;
```

**Урок:** Вызывать валидатор схемы перед любым сохранением. Возвращать `WP_Error` (у нас `JsonResponse` с ошибками) если данные некорректны.

---

## 3. Система полей (Field System)

### 3.1 Базовая абстракция поля

```
Forminator_Field (abstract)
    ├── slug          (уникальный тип: 'text', 'email', 'select'...)
    ├── name          (читаемое имя)
    ├── category      ('standard', 'advanced', 'payment', 'custom')
    ├── icon          (иконка для админки)
    ├── position      (порядок показа в списке)
    ├── is_input      (является ли инпутом)
    ├── is_calculable (поддерживает ли калькуляцию)
    └── defaults()    — возвращает дефолтные настройки поля
```

Методы, которые переопределяют наследники:
- `markup($field, $views_obj)` — HTML фронтенда поля
- `validate($field, $data)` — валидация ввода
- `sanitize($field, $data)` — санитайзер
- `admin_init_field()` — инициализация админских настроек

**Урок для VertexCMS:**
- `FieldRegistry` как замену `Forminator_Fields` — хранит все зарегистрированные типы полей.
- Ключевые методы в базовом `Field`: `render()` → HTML, `validate()` → bool|errors, `getAdminSettings()` → schema.
- Дефолтные значения определяются методом `defaults()`, фильтруемым через хук.

---

### 3.2 Регистрация полей через загрузчик

`class-form-fields.php` использует `Forminator_Loader::load_files()` для автоматического сканирования папки `library/fields/`.

Каждый PHP-файл в этой папке возвращает экземпляр класса поля, который наследует `Forminator_Field`.

**Урок:** Использовать автоматический сканер директории `app/Builder/Fields/` (Laravel `File::allFiles()`) для регистрации типов полей без ручного перечисления.

---

### 3.3 Конфигурация поля (Settings Schema)

У каждого поля есть массив `$settings`, который описывает все админские настройки:

```php
$this->settings = apply_filters("forminator_field_{$this->slug}_general_settings", [
    'label' => [
        'type' => 'text',
        'label' => __('Label', 'forminator'),
        'default' => '',
    ],
    'required' => [
        'type' => 'toggle',
        'label' => __('Required', 'forminator'),
        'default' => false,
    ],
    'placeholder' => [
        'type' => 'text',
        'label' => __('Placeholder', 'forminator'),
    ],
]);
```

**Урок:** Сделать поле `settingsSchema` в `FieldRegistry` — массив JSON Schema для каждого поля, чтобы фронтенд мог динамически генерировать панель настроек без бэкандных изменений.

---

### 3.4 Поле как отдельная сущность — `Forminator_Form_Field_Model`

```php
class Forminator_Form_Field_Model {
    public $slug;          // уникальный ID поля, например "text-1"
    public $form_id;       // ID формы
    public $parent_group;  // для групповых полей
    protected $raw = [];   // все остальные свойства (element_id, required, label...)

    public function import($data) { /* массовое присвоение */ }
    public function to_array()     { /* сериализация */ }
    public function to_formatted_array() { /* с миграцией версий */ }
}
```

Использует `__get` и `__set` для динамического доступа к полям.

**Урок для VertexCMS:**
- `FieldModel` с явными свойствами + `$attributes` массивом для динамических полей.
- `import(array $data)` массово заполняет поля через filtering.
- `toArray()` гарантирует стабильный формат сериализации.

---

### 3.5 Врапперы (Wrappers)

Форминатор группирует поля в "wrappers" (контейнеры):

```
wrapper-1  →  [text-1, email-1, select-1]    (3 поля в одном ряду)
wrapper-2  →  [textarea-1]                   (1 поле на весь ряд)
```

Каждый wrapper имеет:
- `wrapper_id` — уникальный ID
- `position` — порядок отображения
- `parent_group` — для вложенных групп
- `fields[]` — массив `Forminator_Form_Field_Model`

`MAX_CUSTOM_FORM_FIELDS_PER_WRAPPER = 4` — максимальное полей в одном ряду.

**Урок для VertexCMS:**
- Row-объекты с массивом children-нод.
- Перетаскивание между rows — стандартная операция `move_form_field`.
- При превышении максимума полей в row — создавать новый row автоматически.

> **Примечание:** В текущем коде `modules/vertex-forms/` концепция wrapper-ов не реализована.
> Вместо `wrapper_id` используется колонка `sort_order` в таблице `form_fields` для
> определения порядка отображения полей. Группировка полей в ряды через wrapper-ы
> может быть добавлена при переходе на многостолбцовый макет в v0.11.

---

## 4. Условная логика (Conditional Logic)

### 4.1 Структура условий

Каждое поле может иметь массив `conditions`:

```json
{
  "conditions": [
    {
      "element_id": "select-1",
      "rule": "is",
      "value": "Да"
    }
  ],
  "condition_rule": "all",      // или "any"
  "condition_action": "show"    // или "hide"
}
```

### 4.2 Движок условий — `is_hidden()`

```php
public static function is_hidden($field_settings, $extra_conditions = [], $group_suffix = '') {
    $conditions       = $field_settings['conditions'] ?? [];
    $condition_rule   = $field_settings['condition_rule'] ?? 'all';
    $condition_action = $field_settings['condition_action'] ?? 'show';

    // Для каждого условия проверяем is_condition_matched()
    // Считаем, сколько условий выполнилось
    // Если правило "all" — все должны выполниться
    // Если "any" — достаточно одного

    $all_matched = ($condition_fulfilled > 0 && 'any' === $condition_rule)
                || ($conditions_count === $condition_fulfilled && 'all' === $condition_rule);

    return 'show' === $condition_action ? !$all_matched : $all_matched;
}
```

**Урок:** Внести этот же движок в VertexCMS forms как часть `ConditionalLogicService`.

---

### 4.3 Правила сравнения

`is_condition_fulfilled()` поддерживает правила:

| Правило      | Значение            |
|--------------|---------------------|
| `is`         | равно               |
| `is_not`     | не равно            |
| `is_great`   | больше              |
| `is_less`    | меньше              |
| `contains`   | содержит подстроку  |
| `starts`     | начинается с        |
| `ends`       | заканчивается на    |
| `day_is`     | день недели совпадает |
| `month_is`   | месяц совпадает     |
| `is_before`  | дата раньше         |
| `is_after`   | дата позже           |
| `is_correct` | поле заполнено      |
| `is_incorrect`| поле не заполнено  |

**Урок для VertexCMS:** Это готовый набор condition rules — можно переиспользовать почти без изменений для Laravel.

---

## 5. Валидация и санитайзер

### 5.1 Валидация поля

```php
// В абстракте
public function validate($field, $data) {
    // Если поле обязательное и пустое — добавить ошибку
    if ($this->is_required($field) && empty($data)) {
        $this->validation_message[] = __('Это поле обязательно', 'forminator');
    }
}

// Обёртка
public function validate_entry($field_array, $field_data) {
    if ($this->is_available($field_array)) {
        $field_data = $this->maybe_autofill($field_array, $field_data, $settings);
        $this->validate($field_array, $field_data);
    }
    return $field_data;
}
```

**Урок:** Каждое поле само ответствено за свою валидацию. Общий цикл вызывает `validate_entry()` у всех полей, собирает ошибки в общий массив.

---

### 5.2 Регулярные выражения ограничений

Типы ограничений хранятся как строки:
```
maxlength, minlength, min, max, pattern, email, number, url
```

**Урок:** Использовать готовые `Rule::*` Laravel-валидатора, а для runtime JS повторять их subset на фронте.

---

## 6. API класс (Фасад)

`Forminator_API` — единый entry point для всех операций:

```php
// Создание
Forminator_API::add_form($name, $wrappers, $settings, $status);

// Обновление
Forminator_API::update_form($id, $wrappers, $settings, $status, $notifications);

// Поля
Forminator_API::add_form_field($form_id, $type, $data, $wrapper);
Forminator_API::update_form_field($form_id, $id, $data);
Forminator_API::delete_form_field($form_id, $id);
Forminator_API::move_form_field($form_id, $id, $new_position, $new_wrapper_id);

// Записи
Forminator_API::get_entries($form_id, $per_page, $current_page);
Forminator_API::add_form_entry($form_id, $entry_meta);
Forminator_API::delete_form_entry($form_id, $entry_id);
```

Все методы возвращают либо результат, либо `WP_Error`.

**Урок для VertexCMS:**
- Сделать `BuilderApi` фасад с методами: `createPage()`, `updatePage()`, `addNode()`, `moveNode()`, `deleteNode()`, `getEntries()`.
- Все ошибки возвращать в виде `JsonResponse` с кодом ошибки и сообщением.

---

## 7. Рендеринг на фронте

### 7.1 Абстрактный Render Form

`Forminator_Render_Form` — абстрактный класс, который:
- генерирует уникальный `render_id` для поддержки нескольких одинаковых форм на странице
- рендерит структуру `<form>`, поля, скрытые поля (nonce, form_id, action)
- собирает кастомные CSS и подключает скрипты
- поддерживает AJAX-загрузку формы (`ajax_load_module`, `ajax_display`)

Ключевая последовательность рендера:
```
get_form()                   — обёртка в <form>
  ├── render_fields()        — цикл по полям
  │     └── render_field()   — вызывает markup() у каждого поля
  ├── get_submit()           — кнопка Submit + hidden поля
  ├── generate_styles()      — кастомные CSS для формы
  └── forminator_render_front_scripts() — инициализация jQuery.forminatorFront
```

**Урок для VertexCMS:**
- `PageRenderer` — инкапсулирует рендеринг дерева нод в Blade-представления.
- Разделить "форматировщик контента" (JSON → представления) от "генератора CSS".

---

### 7.2 Шаблоны HTML

Форминатор хранит HTML-шаблоны в:
```
assets/js/front/templates/{module-slug}/
  ├── main.html      — основной шаблон формы
  ├── basic.html     — упрощённый
  └── global/        — общие части
```

Рендерер использует `include` с передачей переменных View и получает готовую строку через `ob_get_clean()`.

**Урок для VertexCMS:**
- Использовать Laravel Blade вместо строковых шаблонов.
- `view('builder::blocks.heading', [...])->render()` даёт тот же результат чище.

---

### 7.3 Генерация CSS

```php
public function generate_styles($model = null, $echo_styles = true) {
    foreach ($style_properties as $style_property) {
        // Берёт настройки формы
        // Подключает соответствующий PHP-шаблон стилей
        // Возвращает или выводит готовый <style> блок
    }
}
```

CSS хранится в шаблонах с переменными `{$property}` и подменяется через `str_replace`.

**Урок для VertexCMS:**
- Создать `PageCssGenerator`, который генерирует класс `.vx-node-{id}` с уникальными стилями.
- Кешировать готовый CSS в файл и подключать как статику (не инлайнить на каждой странице).

---

## 8. Подтверждение и проверка (Spam Protection)

```php
// Абстракция
abstract class Forminator_Spam_Protection {
    public function is_available($settings) { return true; }
    abstract public function validate($field, $data);
}

// Реализации:
Forminator_Captcha
Forminator_Honeypot
Forminator_Google_Recaptcha
```

Форминатор поддерживает одновременно несколькoprotection-провайдеров.

**Урок для VertexCMS:**
- `SpamProtection` интерфейс с методами `isEnabled()`, `validate()`, `render()`.
- Встроенный honeypot как дефолт, reCAPTCHA v3 как опция.

---

## 9. Импорт / Экспорт

```php
// Экспорт
$model->to_exportable_data();
// → ['type' => 'form', 'data' => [...], 'status' => 'publish', 'version' => '1.51.0']

// Импорт
Forminator_Base_Form_Model::create_from_import_data($import_data, $name = '');
// → Создаёт новую запись через wp_insert_post, загружает meta, вызывает Forminator_Migration
```

Фильтр `forminator_import_model` позволяет подменить результат импорта.

Миграция поддерживает переходы между версиями схем (`Forminator_Migration::migrate_custom_form_settings`).

**Урок для VertexCMS:**
- `import(array $data)` в `PageModel` полностью повторяет логику.
- Версионировать схему: в JSON страницы хранить `"version": "1.0"`, на загрузке вызывать соответствующий мигратор.

---

## 10. Переменные (Autofill)

Система autofill позволяет автоматически подставлять значения полей из внешних провайдеров:

```php
public static function get_autofill_setting($settings) {
    // Возвращает ['element_id' => [provider, mapping]]
}

public function maybe_autofill($field_array, $field_data, $settings) {
    // Если поле не редактируемое — подставляет значение из провайдера
    // Вызывается перед валидацией
}
```

**Урок:** Готовый паттерн для "pre-fill" форм данными из cookie, query-параметров или админки (например, предзаполнение email).

---

## 11. Шорткоды и публичный API

```php
// Рендерер регистрирует шорткод автоматически
add_shortcode('forminator_custom-form', [$this, 'render_shortcode']);

// Все операции через статический API
Forminator_API::add_form($name, $wrappers, $settings);
Forminator_API::get_form($form_id);
Forminator_API::get_entries($form_id);
```

**Урок для VertexCMS:**
- Шорткод в Blade: компонент `<x-builder.form :id="$formId" />`.
- Публичный API контроллер `BuilderController` с CRUD эндпоинтами под Inertia.

---

## 12. Абстракции для расширения

| Абстрактный класс           | Назначение                      |
|----------------------------|---------------------------------|
| `abstract-class-field.php` | Базовый класс для всех типов полей |
| `abstract-class-form-result.php` | Результат обработки формы |
| `abstract-class-form-template.php` | Шаблоны email-уведомлений |
| `abstract-class-front-action.php` | Обработчик отправки формы |
| `abstract-class-mail.php` | Email уведомления |
| `abstract-class-payment-gateway.php` | Платёжные шлюзы |
| `abstract-class-spam-protection.php` | Анти-спам |
| `abstract-class-user.php` | Управление пользователем после отправки |

**Урок для VertexCMS:** Сделать аналогичные абстрактные классы для каждого типа сущности: `AbstractField`, `AbstractBlockRenderer`, `AbstractFormAction`, `AbstractNotification`.

---

## 13. Ключевые файлы и их роль

| Путь                                                         | Роль                                                |
|--------------------------------------------------------------|-----------------------------------------------------|
| `forminator.php`                                             | Точка входа, регистрация хуков активации/деактивации |
| `constants.php`                                              | Все константы плагина (версии, флаги PRO)            |
| `functions.php`                                              | Глобальный helper `forminator()` — доступ к ядру    |
| `library/class-core.php`                                     | Синглтон ядра, include'ит все остальные классы       |
| `library/class-form-fields.php`                              | Регистрация всех типов полей                         |
| `library/class-loader.php`                                   | Автоматическая загрузка файлов из директории        |
| `library/abstracts/abstract-class-field.php`                 | Базовый абстрактный класс для всех полей             |
| `library/abstracts/abstract-class-front-action.php`          | Базовый обработчик отправки формы                    |
| `library/class-form-fields.php`                              | Менеджер полей                                       |
| `library/model/class-form-field-model.php`                   | Модель одного поля                                   |
| `library/model/class-base-form-model.php`                    | Базовая модель формы (CRUD + сериализация)           |
| `library/model/class-custom-form-model.php`                  | Конкретная модель обычной формы                      |
| `library/render/class-render-form.php`                       | Базовый абстрактный рендерер формы                   |
| `library/class-api.php`                                      | Публичный API-фасад                                  |
| `library/class-upgrade.php`                                  | Миграции схем данных между версиями                  |
| `admin/abstracts/class-admin-module-edit-page.php`           | Абстрактная страница редактирования модуля           |
| `library/helpers/helper-forms.php`                           | Хелперы для работы с формами (визуализация списков)  |
| `addons/`                                                    | Архитектура плагинов (addons), загружаемых динамически |

### 13.1 Ключевые файлы модуля `vertex-forms` (актуальный код)

| Путь                                                        | Роль                                                      |
|-------------------------------------------------------------|-----------------------------------------------------------|
| `modules/vertex-forms/VertexFormsServiceProvider.php`       | Регистрация синглтонов, загрузка миграций, роутов, конфига |
| `modules/vertex-forms/src/FieldTypeRegistry.php`            | Реестр 15 типов полей, статический массив `FIELD_TYPES`   |
| `modules/vertex-forms/src/Services/FormService.php`         | CRUD формы, валидация, submit, checkLimits, createSnapshot |
| `modules/vertex-forms/src/Services/FormConditionEngine.php` | Движок условий: `evaluateFields()`, `evaluate()`          |
| `modules/vertex-forms/src/Services/FormCalculatorEngine.php`| Безопасный парсер выражений (RPN)                         |
| `modules/vertex-forms/src/Services/FormImportExportService.php`| Экспорт/импорт форм в JSON                               |
| `modules/vertex-forms/src/Services/FormAnalyticsService.php`| Метрики: submissions, views, completion rate              |
| `modules/vertex-forms/src/Models/Form.php`                  | Модель формы, связи `fields()`, `submissions()`            |
| `modules/vertex-forms/src/Models/FormField.php`             | Модель поля формы, своя таблица                          |
| `modules/vertex-forms/src/Models/FormSubmission.php`        | Модель отправки, связь `values()`                         |
| `modules/vertex-forms/src/Models/FormSubmissionValue.php`   | Значение одного поля в отправке                          |
| `modules/vertex-forms/src/Models/FormVersion.php`           | Снимок версии формы для истории изменений                |
| `modules/vertex-forms/src/Controllers/FormApiController.php`| REST API: CRUD формы, синхронизация полей                 |
| `modules/vertex-forms/src/Controllers/FormPublicController.php`| Публичные эндпоинты: `submit()`, `config()`              |
| `modules/vertex-forms/src/Controllers/FormSubmissionController.php`| CRUD отправок, экспорт в CSV                          |
| `modules/vertex-forms/config/forms.php`                     | 21 ключ конфигурации (antispam, uploads, limits, analytics)|
| `modules/vertex-forms/routes/web.php`                       | `GET /forms/{form:slug}`, `POST /forms/{form:slug}/submit` |
| `modules/vertex-forms/routes/api.php`                       | REST JSON API (FormApiController)                         |
| `modules/vertex-forms/routes/admin.php`                     | Админка: формы, отправки, аналитика, версии               |
| `modules/vertex-forms/database/migrations/2026_05_12_*`     | 3 миграции: forms+submissions, versions, analytics       |

---

## 14. Паттерны, которые стоит скопировать напрямую

### 14.1 Registry pattern для полей

```php
// Forminator_Core::set_field_objects()
foreach ($this->fields as $field_object) {
    self::$field_objects[$field_object->slug] = $field_object;
}

// Получение поля по типу
public static function get_field_object($type) {
    return self::$field_objects[$type] ?? null;
}
```

**В VertexCMS:** `FieldRegistry::register($field)`, `FieldRegistry::get(string $type): ?Field`.

---

### 14.2 Модель с дефолтными значениями и маппингом

```php
// В Forminator_Form_Field_Model
public function __construct($settings = null) {
    if (!empty($settings)) $this->form_settings = $settings;
}

// defaults() в поле
public function defaults() {
    return ['required' => false, 'label' => ''];
}
```

**В VertexCMS:** Каждая Field сущность имеет `defaults()` для инициализации новых инстансов.

---

### 14.3 Массовое сохранение с маппингом

```php
public function save($clone_form = false) {
    $maps = array_merge($this->get_default_maps(), $this->get_maps());
    foreach ($maps as $map) {
        if ('meta' === $map['type']) {
            $meta_data[$map['field']] = $this->{$map['property']};
        }
    }
    update_post_meta($id, self::META_KEY, $meta_data);
}
```

**В VertexCMS:** `PageModel::save()` сериализует `content` и `settings` в JSON и сохраняет в соответствующие колонки таблицы.

---

### 14.4 Превью без сохранения

```php
public function load_preview($id, $data) {
    $form_model = $this->load($id, true);
    $form_model->clear_fields();
    $form_model->settings = $data['settings'] ?? [];
    return static::prepare_data_for_preview($form_model, $data);
}
```

**В VertexCMS:** Метод `loadPreview($id, $payload)` создаёт временный инстанс модели с данными из запроса, чтобы рендерить превью без обращения к БД.

---

### 14.5 Условие is_available()

```php
// В поле capcha
public function is_available($field) {
    return !empty($field['captcha_key']);
}

// В базовом классе — по умолчанию true
public function is_available($field) { return true; }
```

**В VertexCMS:** Использовать для скрытия полей, которые не могут быть отображены без конфигурации (например, поле Stripe без настроек аккаунта).

---

### 14.6 Отслеживание статусов и валидация возможности сабмита

```php
public function form_can_submit() {
    $can_show = ['can_submit' => true, 'error' => ''];
    // 1. Только залогиненные
    // 2. Лимит по пользователю
    // 3. Срок действия формы
    // 4. Статус draft
    // 5. Субмит-кнопка скрыта условиями
    return $can_show;
}

public function form_is_visible($is_preview) {
    // Аналогично проверяет права и сроки перед отображением
}
```

**В VertexCMS:** Вынести в `PageSubmissionPolicy` — отдельный класс, который знает все бизнес-правила доступности формы.

---

## 15. Сводка: что пригодилось для построения форм и блока в VertexCMS

| Паттерн Форминатора              | Актуальная реализация в VertexCMS (код)              |
|----------------------------------|------------------------------------------------------|
| `Forminator_Core::get_instance()`| `VertexFormsServiceProvider` — singleton контейнер    |
| `Forminator_Field::get_instance()` | `FieldTypeRegistry::getRegistryPayload()`          |
| Реестр типов полей `Forminator_Fields` | `FieldTypeRegistry::FIELD_TYPES` (статический массив) |
| Абстрактный класс поля | реализован как массив схем в `FieldTypeRegistry` (без отдельных PHP-классов) |
| `Forminator_Form_Model` | `Vertex\Forms\Models\Form` + `Form::fields()` relation |
| `Forminator_Form_Field_Model` | `Vertex\Forms\Models\FormField` (отдельная таблица) |
| `Forminator_Form_Action` (обработка отправки) | `FormService::submit()` |
| `ConditionalLogicService` (is_hidden) | `FormConditionEngine::evaluateFields()` + `{depends_on, operator, value}` |
| `Forminator_Render_Form` | `FormPublicController::config()` → JSON → Vue `FormRenderer.vue` |
| `Forminator_API::add_form` | `FormApiController::store()` // POST /api/forms |
| `Forminator_API::get_entries` | `FormSubmissionController::index()` // GET admin submissions |
| `to_exportable_data()` | `FormImportExportService::export()` (вложенный массив `["form"=>[...]]`) |
| `create_from_import_data()` | `FormImportExportService::import()` |
| Повторяющиеся поля wrapper-ы | `sort_order` в `form_fields`, конфигурация врапперов отложена на v0.11 |
| Спам-защита (honeypot + reCAPTCHA) | Встроена в `FormService::buildValidationRules()` |
| reCAPTCHA v2/v3 + Turnstile | ключи в `config/forms.php`, вызов API не реализован |
| Лимиты отправок | `FormService::checkLimits()` → `entry_limit` + `daily_limit` |
| Снимки версий | `FormService::createSnapshot()` / `restoreVersion()` → `form_versions` |
| Аналитика | `FormAnalyticsService::getAnalytics()` → административная панель |
| CSV экспорт | `FormSubmissionController::export()` — BOM, UTF-8, текстовый разделитель |
| Фильтр `settingsSchema()` в админке | `FieldTypeRegistry::getSchema()` → JSON Schema для дочерних настроек |
| `FieldConditionRule` (отдельный класс) | ❌ Не реализован, логика встроена в `FormConditionEngine` |
| `spam_protection` в поле `settings` | `honeypot_enabled`, `recaptcha_enabled`, `recaptcha_version` в `forms.settings` |
| `submit_action: email` из Forminator | `notify_admin` + `notify_user` в `forms.settings` |
| Сборка `PageRenderer::preview($json)` | `FormController::preview()` — показывает builder в read-only режиме |

---

## 16. Что НЕ копировать из Форминатора

| В Форминаторе           | В VertexCMS делаем иначе                  |
|-------------------------|-------------------------------------------|
| WordPress CPT           | Собственные таблицы `forms`, `form_fields`, `form_submissions`, `form_submission_values`, `form_versions`, `form_analytics` в модуле `vertex-forms` |
| Post meta               | JSON колонки (`settings`, `options`, `content_json`, `meta`) в соответствующих таблицах |
| jQuery Front            | Vue 3 + Inertia для админки и фронтенда   |
| PHP-рендеринг фронта    | Laravel Blade + Vue 3 через Inertia (SSR + SPA) |
| Action Scheduler        | Laravel Queue + Job classes               |
| WP_Error                | Illuminate\Validation\Validator + JsonResponse с правильными HTTP-кодами |
| Несколько post types    | Один модуль `vertex-forms` с разными типами форм через поле `type` в таблице `forms` |
| Shortcodes              | Blade компоненты (`<x-forms.form />`) + Inertia endpoints через Page Builder блок |
| Singleton ядра          | Laravel Service Container с привязками в ServiceProvider |
| Прямой SQL              | Eloquent ORM + Query Builder              |
| Глобальные функции      | Сервис-классы и Facades через DI контейнер |

---

## 17. Техзадание: Vertex Forms (конструктор форм для VertexCMS)

> Референс: Forminator v1.51.0. Цель — перенести проверенные паттерны в стек Laravel + Vue 3, не копируя WordPress-специфику.

---

### 17.1 Назначение и границы модуля

```
Vertex Forms — модуль VertexCMS для создания, редактирования и обработки
онлайн-форм (контактные, регистрация, опросы, обратная связь, заявки).

Входит в состав ядра CMS наравне с Page Builder, Media, SEO.
Не является отдельным плагином.
```

**Границы модуля (in-scope для v0.10):**
- Визуальный drag-and-drop конструктор форм
- 15 стандартных типов полей
- Условная логика (показ/скрытие полей)
- Встроенная валидация на фронте и бэке
- Обработка отправки (e-mail, запись в БД, редирект)
- Спам-защита (honeypot, reCAPTCHA v3)
- Записи отправленных форм в админке
- Импорт/экспорт форм в JSON
- Подключение формы на любую страницу через блок Page Builder

**Out-of-scope (отложено на v0.11+):**
- Платёжные шлюзы (Stripe, PayPal)
- Квизы и опросы с подсчётом очков
- Интеграции с внешними CRM
-條件式 на основе даты/времени
- Условное перенаправление после отправки

---

### 17.2 Цели и не-цели

Цель модуля — дать пользователям VertexCMS возможность создавать, редактировать и
обрабатывать онлайн-формы без написания кода, с drag-and-drop конструктором и
встроенной валидацией на фронте и бэке.

| Цель | Не-цель |
|------|---------|
| Пользователь создаёт форму за 2–5 минут | Не делать полный аналог Elementor Forms |
| Drag & drop полей в конструкторе | Не поддерживать произвольный PHP-код в полях |
| Валидация без написания кода | Не делать визуальный редактор email-шаблонов (v0.11) |
| Отправка на e-mail + запись в БД | Не делать mutation API для внешних клиентов в v0.10 |
| Условная логика полей | Не делать многошаговые формы с сохранением прогресса |
| Форма как блок Page Builder | Не делать отдельный публичный маршрут для каждой формы |

---

### 17.3 Архитектурные решения (на основании Forminator)

```
app/
└── Forms/
    ├── BuilderCore.php              # Синглтон ядра модуля (как Forminator_Core)
    ├── FieldRegistry.php            # Реестр всех типов полей
    ├── FormService.php              # Бизнес-логика CRUD, валидация, отправка
    ├── FormConditionEngine.php      # Движок условной логики
    ├── FormCalculatorEngine.php     # Безопасный парсер математических выражений
    ├── FormImportExportService.php  # Импорт/экспорт JSON
    ├── FormAnalyticsService.php     # Статистика по формам
    ├── SpamProtection/                 # Концепция: интерфейс для правил антиспам-защиты
    │   └── (HoneypotRule, RecaptchaRule — в плане на v0.11)
    ├── Models/
    │   ├── Form.php                 # Модель формы
    │   ├── FormField.php            # Модель поля
    │   ├── FormSubmission.php       # Модель отправки
    │   ├── FormSubmissionValue.php  # Значение поля в отправке
    │   ├── FormVersion.php          # Снимок версии формы
    │   └── FormAnalytic.php         # Агрегированная статистика по дням
    ├── Contracts/
    │   ├── FormRepositoryInterface.php
    │   └── CalculatorEngineInterface.php
    ├── Repositories/
    │   └── EloquentFormRepository.php
    ├── Dto/
    │   ├── CreateFormDto.php
    │   ├── UpdateFormDto.php
    │   └── SubmitFormDto.php
    └── Rules/
        └── (валидационные правила — в плане на v0.11)

routes/
├── web.php    # Публичные эндпоинты: GET /{form:slug}, POST /{form:slug}/submit
├── api.php    # REST API: CRUD form + field-registry
└── admin.php  # Админка: формы, отправки, аналитика, версии

resources/
└── forms/
    ├── builder/
    │   ├── BuilderCanvas.vue
    │   ├── BuilderSidebar.vue
    │   ├── FieldNode.vue
    │   ├── SettingsPanel.vue
    │   └── ConditionalLogicModal.vue
    └── front/
        └── FormRenderer.vue
```

### 17.4 Сущности и структура БД

#### 17.4.1 Таблица `forms`

```php
Schema::create('forms', function (Blueprint $table): void {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('type')->default('standard'); // standard, calculator, survey, poll
    $table->text('description')->nullable();
    $table->json('settings')->nullable();  // notifications, spam protection, limits, progress display
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('require_login')->default(false);
    $table->integer('entry_limit')->nullable()->comment('Max total submissions');
    $table->integer('daily_limit')->nullable()->comment('Max per day per IP/user');
    $table->dateTime('available_from')->nullable();
    $table->dateTime('available_to')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

| Колонка | Тип | Назначение |
|---------|-----|------------|
| `id` | bigint PK | Внутренний ID |
| `name` | string | Отображаемое имя формы |
| `slug` | string unique | URL-идентификатор для публичного доступа |
| `type` | string | Тип формы: `standard`, `calculator`, `survey`, `poll` |
| `description` | text/nullable | Описание (фронтенд) |
| `settings` | json/nullable | Глобальные настройки: антиспам, уведомления, лимиты, прогресс-бар |
| `sort_order` | int | Порядок в списке админки |
| `is_active` | bool | Включена ли форма для публичного доступа |
| `require_login` | bool | Требовать авторизацию для отправки |
| `entry_limit` | int/nullable | Общий лимит отправок по форме |
| `daily_limit` | int/nullable | Общий лимит отправок на форму в сутки (суммарно по всем IP). Резервный лимит на уровне БД, не переопределяется `settings` |
| `available_from` | datetime/nullable | Дата открытия формы |
| `available_to` | datetime/nullable | Дата закрытия формы |
| `created_by` | FK users | Автор формы |
| `created_at` / `updated_at` | timestamp | Временные метки |

> **Примечание:** Форма не имеет поля `status`. Публикация/снятие с публикации управляется через флаг `is_active`.
> Колонка `updated_by` из схемы §1.3 (`BasePageModel`) в таблице `forms` **отсутствует** — только `created_by`.
> Два отдельных механизма лимитов: `entry_limit` + `daily_limit` через столбцы БД и `settings` (`max_entries` + `daily_limit_per_ip`) через ключи. Глобальные фоллбеки: `max_entries_global` + `daily_limit_per_ip_global` в `config/forms.php`.

#### 17.4.2 Таблица `form_fields`

Поля формы хранятся в отдельной таблице — не как JSON в `forms.content`.
Каждая строка — один элемент формы (input, select, heading, divider и т.д.):

```php
Schema::create('form_fields', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
    $table->string('name')->comment('Имя поля для отправки (slug)');
    $table->string('label');
    $table->string('type'); // text, number, email, tel, textarea, select, radio, checkbox, checkbox_group, file, date, hidden, calculator, heading, divider, html, page_break
    $table->integer('sort_order')->default(0);
    $table->json('options')->nullable(); // choices, validation rules, conditional logic, calculator formula
    $table->boolean('required')->default(false);
    $table->boolean('visible')->default(true);
    $table->string('default_value')->nullable();
    $table->text('placeholder')->nullable();
    $table->text('help_text')->nullable();
    $table->string('css_class')->nullable();
    $table->timestamps();
});
```

| Колонка | Тип | Назначение |
|---------|-----|------------|
| `id` | bigint PK | ID поля |
| `form_id` | FK → forms.id | Привязка к форме |
| `name` | string | Сlug для отправки в POST-запросе (например `email`, `phone`) |
| `label` | string | Отображаемое название поля |
| `type` | string | Тип поля (`text`, `email`, `select`, `checkbox_group`, `calculator`, `page_break` и т.д.) |
| `sort_order` | int | Порядок отображения в форме |
| `options` | json/nullable | Выбор select/radio, правила валидации, условия видимости, формула калькулятора |
| `required` | bool | Обязательность заполнения |
| `visible` | bool | Видимость поля на фронте (управляется автоматически при оценке условий) |
| `default_value` | string/nullable | Значение по умолчанию |
| `placeholder` | text/nullable | Плейсхолдер |
| `help_text` | text/nullable | Подсказка под полем |
| `css_class` | string/nullable | Дополнительные CSS-классы |
| `created_at` / `updated_at` | timestamp | Временные метки |

**Пример строки** (`type = select` с опциями и условной логикой):

```json
{
  "id": 5,
  "form_id": 3,
  "name": "topic",
  "label": "Тема обращения",
  "type": "select",
  "sort_order": 2,
  "options": {
    "choices": {"support": "Техподдержка", "sales": "Продажи"},
    "conditional": {
      "depends_on": "referrer",
      "operator": "equals",
      "value": "google"
    }
  },
  "required": true,
  "visible": true,
  "placeholder": null,
  "help_text": "Выберите направление",
  "css_class": ""
}
```

> **Примечание:** Условная логика хранится в `options.conditional` как один объект
> `{depends_on, operator, value}` — это соответствует структуре, которую использует
> `FormConditionEngine::evaluateFields()`. Старая схема Forminator с массивом `conditions[]`
> и флагами `condition_rule`/`condition_action` в исходном документе не используется в
> реальном коде VertexCMS.

#### 17.4.3 Формат `settings` формы (JSON-колонка)

Хранится в `forms.settings`. Поддерживаемые ключи:

```json
{
  // 📢 Уведомления
  "notify_admin": true,
  "notify_admin_emails": ["admin@example.com"],
  "notify_user": false,
  "user_email_template": "form_confirmation",
  "autoresponder_body": "",

  // 🛡️ Антиспам
  "honeypot_enabled": true,
  "recaptcha_enabled": false,
  "recaptcha_version": "v2",

  // 🔢 Калькулятор
  "tax_enabled": false,
  "tax_rate": 0,

  // 🎛️ Поведение формы
  "show_progress": true,
  "show_page_titles": false,

  // ⏱️ Лимиты (доступны также как отдельные колонки forms)
  "max_entries": null,
  "daily_limit_per_ip": null
}
```

| Ключ | Тип | Назначение |
|------|-----|------------|
| `notify_admin` | bool | Отправлять уведомление админу при отправке |
| `notify_admin_emails` | string[] | Список Email админов |
| `notify_user` | bool | Отправлять подтверждение пользователю |
| `user_email_template` | string | Шаблон email для пользователя |
| `autoresponder_body` | string | Текст autoresponder-письма |
| `honeypot_enabled` | bool | Включить honeypot-защиту |
| `recaptcha_enabled` | bool | Включить reCAPTCHA |
| `recaptcha_version` | string | `v2` или `v3` |
| `tax_enabled` | bool | Учитывать налог в калькуляторе |
| `tax_rate` | number | Ставка налога в % |
| `show_progress` | bool | Показывать прогресс-бар многошаговой формы |
| `show_page_titles` | bool | Показывать заголовки страниц при разбиении |
| `max_entries` | int/nullable | Лимит общих отправок по форме |
| `daily_limit_per_ip` | int/nullable | Лимит отправок в сутки с одного IP |

> **Примечание:** В старом варианте документа использовались ключи `submit_action:email`
> и `store_submissions:true`. В текущем коде отправка на email и сохранение в БД всегда
> включены и управляются отдельными флагами (`notify_admin`, `notify_user`). Отправка
> всегда записывает строку в `form_submissions` и соответствующие `form_submission_values`.

#### 17.4.4 Таблица `form_submissions` (отправки)

```php
Schema::create('form_submissions', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
    $table->string('submission_id')->unique()->comment('Публичный UUID отправки');
    $table->string('ip_address', 45)->nullable()->index();
    $table->string('user_agent')->nullable();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('status')->default('completed'); // completed, pending, spam, trashed
    $table->json('meta')->nullable(); // total (калькулятор), payment_status и т.д.
    $table->timestamps();
});
```

Хранит одну отправку формы. Детальные значения полей — в таблице `form_submission_values`.

#### 17.4.5 Таблица `form_submission_values`

```php
Schema::create('form_submission_values', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('submission_id')->constrained('form_submissions')->cascadeOnDelete();
    $table->foreignId('field_id')->constrained('form_fields')->cascadeOnDelete();
    $table->text('value')->nullable(); // массив/объект хранится как JSON-строка
    $table->timestamps();
});
```

Одна строка = значение одного поля конкретной отправки. Поддерживает повторяющиеся поля
(чекбоксы, загрузка файлов) через JSON-сериализацию.

#### 17.4.6 Таблица `form_versions`

```php
Schema::create('form_versions', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
    $table->integer('version_number');
    $table->json('content_json');
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->text('comment')->nullable();
    $table->timestamps();

    $table->unique(['form_id', 'version_number']);
});
```

Снимок состояния формы для истории изменений. `FormService::createSnapshot()` создаёт версию,
`FormService::restoreVersion()` восстанавливает из неё.

#### 17.4.7 Таблица `form_analytics`

```php
Schema::create('form_analytics', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
    $table->date('date'); // агрегация за день
    $table->integer('views')->default(0);
    $table->integer('unique_visitors')->default(0);
    $table->integer('submissions')->default(0);
    $table->integer('avg_time_seconds')->default(0);
    $table->json('top_fields')->nullable(); // топ взаимодействий с полями
    $table->timestamps();

    $table->unique(['form_id', 'date']);
});
```

Агрегированная статистика по дням. Заполняется фоновым пересчётом
`FormAnalyticsService::recalculate()` и/или при каждом просмотре формы
`FormAnalyticsService::recordView()`.

#### 17.4.8 Сводка по таблицам модуля vertex-forms

| Таблица | Назначение | Связь |
|---------|-----------|-------|
| `forms` | Определения форм (имя, slug, тип, настройки, лимиты) | 1 → N form_fields/form_submissions |
| `form_fields` | Элементы формы (поля, заголовки, разделители) | N ← 1 forms |
| `form_submissions` | Отправки форм (участник, IP, статус) | N ← 1 forms |
| `form_submission_values` | Значения полей одной отправки | N ← 1 form_submissions, N ← 1 form_fields |
| `form_versions` | Снимки формы для истории изменений | N ← 1 forms |
| `form_analytics` | Агрегированная статистика по дням | N ← 1 forms |

---

### 17.5 Перечень типов полей (v0.10 — MVP форм)

Регистр типов полей хранится в `FieldTypeRegistry::FIELD_TYPES` и автоматически
сканируется при загрузке модуля. Ниже реальные типы из текущего кода:

| Slug | Категория | Валидация по умолчанию |
|------|-----------|------------------------|
| `text` | basic | maxlength, minlength, pattern |
| `email` | basic | email |
| `tel` | basic | regex:/^[\d\+\-\(\) ]+$/ |
| `number` | basic | numeric, min, max |
| `textarea` | basic | string |
| `date` | basic | date |
| `select` | choice | required |
| `radio` | choice | required + inline |
| `checkbox` | choice | required |
| `checkbox_group` | choice | required + inline |
| `file` | advanced | file, max_size, mimes |
| `hidden` | hidden | — |
| `calculator` | advanced | формула (safe math parser) |
| `heading` | layout | — |
| `divider` | layout | — |
| `html` | layout | — |
| `page_break` | layout | разбиение на шаги |

**Категории для фильтрации в админке (совпадают с кодом `FieldTypeRegistry`):**

```php
// Категории как строки (не enum, чтобы не ограничивать расширения)
'basic'       // стандартные текстовые поля
'choice'      // select, radio, checkbox, checkbox_group
'advanced'    // file, calculator, html
'hidden'      // hidden
'layout'      // heading, divider, page_break
```

> **Изменено относительно исходного раздела:**
> - `multiselect` удалён — отсутствует в текущем реестре
> - `time` отсутствует в текущем реестре, переносится в бэклог
> - Added `checkbox_group`, `calculator`, `page_break` — присутствуют в коде
> - Категории `standard`/`advanced`/`layout`/`spam`/`payment` заменены на
>   реальные `basic`/`choice`/`advanced`/`hidden`/`layout` из `FieldTypeRegistry`
> - Применяются строки, а не enum, чтобы расширения могли добавлять типы без обновления код

---

### 17.6 Реестр типов полей (`FieldTypeRegistry`)

`FieldTypeRegistry` — синглтон-реестр, хранящий все типы полей как статический массив
`FIELD_TYPES` в `src/FieldTypeRegistry.php`. В отличие от Forminator, где типы — отдельные
PHP-классы наследуемые от абстрактного `Forminator_Field`, в VertexCMS типы описаны
декларативно в массиве. Это упрощает добавление новых типов, но требует ручного указания
всех свойств и правил валидации.

Каждая запись в реестре:

```php
'text' => [
    'label'       => 'Text Input',
    'category'    => 'basic',
    'icon'        => 'type-text',
    'description' => 'Single-line text input.',
    'defaults'    => [
        'label'          => 'Text Field',
        'placeholder'    => '',
        'default_value'  => '',
        'maxlength'      => 255,
        'help_text'      => '',
        'required'       => false,
        'visible'        => true,
    ],
    'props'   => [ /* JSON Schema для полей настроек */ ],
    'validation' => ['string', 'max:255'],
    'editor'  => [
        'component' => 'vc-form-field-text',
        'tabs'      => [ /* вкладки редактора настроек */ ],
    ],
],
```

| Свойство схемы | Назначение |
|---------------|------------|
| `label` | Отображаемое имя типа в админке |
| `category` | Категория: `basic`, `choice`, `advanced`, `hidden`, `layout` |
| `icon` | Имя иконки (Luke icon) для панели полей |
| `description` | Подсказка в панели полей |
| `defaults` | Значения по умолчанию при создании нового поля |
| `props` | Схема свойств для динамической генерации панели настроек |
| `validation` | Laravel validation rules, применятся к значению поля |
| `editor.component` | Имя Vue-компонента редактора: `vc-form-field-{type}` |
| `editor.tabs` | Порядок и состав вкладок в панели настроек |

**Публичные методы:**

| Метод | Возврат | Назначение |
|-------|---------|-----------|
| `getSchema(string $type): ?array` | Схема типа или `null` | Получить схему по slug типа |
| `createDefault(string $type, array $overrides = []): array` | Массив | Создать дефолтный экземпляр поля |
| `getAll(): array` | Массив схем | Все зарегистрированные типы |
| `getByCategory(): array` | Группировка по категориям | Для фильтрации в админке |
| `getRegistryPayload(): array` | Полная нагрузка | Для API и админки (версия реестра + все поля + категории) |
| `registryVersion(): string` | Строка версии | Текущая версия реестра (семантическое версионирование типов) |

Реестр инициализируется в `VertexFormsServiceProvider::registerFieldTypeRegistry()`.

---

### 17.7 Движок условной логики

Реализован в `FormConditionEngine`. Хранится в поле `options.conditional` каждого
`form_fields` элемента.

**Структура условия на одно поле** (хранение в `form_fields.options.conditional`):

```json
{
  "depends_on": "referrer",   // имя поля, от которого зависит
  "operator": "equals",        // оператор сравнения
  "value": "google"            // целевое значение
}
```

Если поле без условия — `options.conditional` равен `null`, поле всегда видимо.

Метод `evaluateFields(array $fields, array $data)` возвращает массив имён полей,
которые должны быть видимы на текущем шаге заполнения.

**Поддерживаемые операторы** (реализованные в `FormConditionEngine::evaluate()`):

| Оператор в коде | Семантика |
|-----------------|-----------|
| `equals` | Поле равно целевому значению |
| `not_equals` | Поле не равно целевому значению |
| `contains` | Поле содержит подстроку |
| `greater_than` | Поле больше целевого значения (числа) |
| `less_than` | Поле меньше целевого значения (числа) |
| `is_empty` | Поле пустое |
| `is_not_empty` | Поле заполнено |

> **Отличие от исходного Forminator:** Старая схема Forminator использовала
> массив `conditions[]` с флагами `condition_rule=all/any` и
> `condition_action=show/hide`. В текущем коде VertexCMS используется
> одно условие на поле (`depends_on` + `operator` + `value`), а видимость
> определяется через булево значение: если условие выполняется — поле показывается,
> если нет — скрывается. Поддержка `condition_rule=any` и цепочек условий
> перенесена в бэклог v0.11.

---

### 17.8 Движок калькулятора (`FormCalculatorEngine`)

`FormCalculatorEngine` в `src/Services/FormCalculatorEngine.php` безопасно вычисляет
математические выражения без `eval()`. Поддерживает: `+`, `-`, `*`, `/`, `()`,
числа с плавающей точкой, плейсхолдеры полей вида `{field_name}`.

**Шаги вычисления:**

1. Заменить все плейсхолдеры `{field_name}` на числовые значения из данных отправки формы.
2. Заменить специальные переменные (`{total}`, `{subtotal}`, `{tax}`, `{discount}`) на их текущие значения.
3. Очистить выражение от нечисловых символов.
4. Токенизировать выражение и преобразовать в обратную польскую нотацию (алгоритм сортировочной станции).
5. Вычислить RPN-стек.

```php
public function evaluate(string $formula, array $values, array $dependsOn = []): float
{
    $expression = $formula;

    foreach ($values as $key => $val) {
        $numeric = is_numeric($val) ? (float) $val : 0;
        $expression = str_replace('{' . $key . '}', (string) $numeric, $expression);
    }

    $expression = str_replace(['{total}', '{subtotal}'], '0', $expression);
    $expression = str_replace('{tax}', '0', $expression);
    $expression = str_replace('{discount}', '0', $expression);

    $expression = preg_replace('/[^0-9\+\-\*\/\.\(\) ]/', '', $expression);

    if (trim($expression) === '') return 0.0;

    return $this->parseMath($expression); // безопасный парсер, берёт из FormCalculatorEngine
}
```

Используется в `FormService::submit()` через `calculateTotal()` и в `FormService::renderForm()`
для предварительного показа результата вычислений в реальном времени поля `calculator`.

---

### 17.9 Валидация на фронте и бэке

#### Бэкенд (Laravel)

```php
class SubmitFormDto
{
    public function __construct(
        public Form $form,
        public array $data,
    ) {}

    public function validate(): array
    {
        return Validator::validate($this->data, $this->buildRules());
    }

    private function buildRules(): array
    {
        $rules = [];
        foreach ($this->form->fields as $field) {
            if (! $field->visible) continue;

            $fieldType = FieldTypeRegistry::getSchema($field->type);
            $fieldRules = $fieldType
                ? implode('|', $fieldType['validation'] ?? [])
                : 'nullable';

            if ($field->required && ! isset($rules[$field->name])) {
                $rules[$field->name] = $fieldRules ? $fieldRules . '|required' : 'required';
            } elseif ($fieldRules) {
                $rules[$field->name] = $fieldRules;
            }
        }

        // Honeypot
        $settings = $this->form->settings ?? [];
        if ($settings['honeypot_enabled'] ?? config('forms.honeypot_enabled', true)) {
            $hpName = 'form_' . md5($this->form->slug . '_hp');
            $rules[$hpName] = 'nullable';
        }

        // reCAPTCHA
        if ($settings['recaptcha_enabled'] ?? config('forms.recaptcha_enabled', false)) {
            $version = $settings['recaptcha_version'] ?? config('forms.recaptcha_version', 'v2');
            $rules[$version === 'v2' ? 'g-recaptcha-response' : 'recaptcha_token'] = 'required';
        }

        return $rules;
    }
}
```

Для `$this->form->fields` в примере выше — это Eloquent-отношение `hasMany(FormField::class)`,
поэтому доступ к свойствам осуществляется через стрелку `$field->name`, `$field->type`,
а не через `$field['name']` как в обычном массиве.

---

#### Фронт (Vue — повторяет бэкенд)

Для каждого поля типизированный набор правил хранится в `FieldTypeRegistry`
(`validation` ключ в схеме типа). Vue-форма синхронизирует правила с бэкендом
без ручного дублирования:

| Правило на фронте | Laravel-аналог |
|------------------|----------------|
| `required` | `required` |
| `email` | `email` |
| `numeric` | `numeric` |
| `url` | `url` |
| `string` | `string` |
| `minlength` / `maxlength` | `min` / `max` (число символов) |
| `min` / `max` (числа) | `min` / `max` |
| `regex` | `regex:/pattern/` |
| `file` | `file` |
| `mimes:jpg,png` | `mimes:jpg,png` |
| `max:size_kb` | `max:size_kb` |
| `date` | `date` |

Правила хранятся в поле `validation` схемы типа в `FieldTypeRegistry` и отображаются
в админке при редактировании поля формы.

---

### 17.10 Публичный API

#### REST API (Inertia JSON)

| Метод  | Маршрут                         | Контроллер | Mid.                       | Назначение |
|--------|--------------------------------|------------|---------------------------|-----------|
| GET    | `GET /api/forms/field-registry`| FormApiController | `forms.view`             | Реестр типов полей |
| GET    | `GET /api/forms`                | FormApiController | `forms.view`             | Список всех форм |
| POST   | `POST /api/forms`               | FormApiController | `forms.create`           | Создать форму |
| GET    | `GET /api/forms/{form}`         | FormApiController | `forms.view`             | Данные формы по slug/ID |
| PUT    | `PUT /api/forms/{form}`         | FormApiController | `forms.edit`             | Обновить форму |
| DELETE | `DELETE /api/forms/{form}`      | FormApiController | `forms.delete`           | Удалить форму |
| POST   | `POST /api/forms/{form}/duplicate` | FormApiController | `forms.edit`           | Дублировать форму |
| GET    | `GET /api/forms/{form}/export-json` | FormApiController | `forms.view`         | Экспорт в JSON |
| POST   | `POST /api/forms/{form}/import-json` | FormApiController | `forms.edit`        | Импорт из JSON |

**Краевые эндпоинты для POST через глобальную форму selector-страницу:**

| Метод  | Маршрут                        | Назначение                        |
|--------|--------------------------------|------------------------------------|
| GET    | `GET /forms/{form:slug}`       | Рендер страницы формы (Inertia)    |
| POST   | `POST /forms/{form:slug}/submit`| Отправка формы (публичный endpoint)|

#### Админка (Inertia)

| Метод  | Маршрут                           | Назначение   |
|--------|----------------------------------|--------------|
| GET    | `/admin/forms` (FormController::index) | Список форм |
| GET    | `/admin/forms/create` (FormController::create) | Форма создания |
| POST   | `/admin/forms/` (FormController::store) | Сохранить новую форму |
| GET    | `/admin/forms/{form}/edit` (FormController::edit) | Редактор формы |
| PUT    | `/admin/forms/{form}` (FormController::update) | Обновить форму |
| GET    | `/admin/forms/{form}/builder` (FormBuilderController::show) | Drag & drop конструктор (Vue SPA) |
| GET    | `/admin/forms/{form}/preview` (FormController::preview) | Превью формы без сохранения |
| DELETE | `/forms/{form}` (FormController::destroy) | Удалить форму |
| POST   | `/admin/forms/{form}/duplicate` (FormController::duplicate) | Дублировать форму |

#### Отправки формы в админке

| Метод  | Маршрут                                       | Назначение    |
|--------|-----------------------------------------------|---------------|
| GET    | `/admin/forms/{form}/submissions`             | Список отправок |
| GET    | `/admin/forms/{form}/submissions/{submission}` | Просмотр отправки |
| DELETE | `/admin/forms/{form}/submissions/{submission}` | Удалить отправку |
| DELETE | `/admin/forms/{form}/clear-submissions`         | Очистить все отправки |
| POST   | `/admin/forms/{form}/export-submissions`       | Экспорт в CSV  |

#### Аналитика

| Метод  | Маршрут                                       | Назначение    |
|--------|-----------------------------------------------|---------------|
| GET    | `/admin/forms/{form}/analytics`               | Страница аналитики |
| GET    | `/admin/forms/{form}/analytics/data`          | Данные для графиков |

`GET /admin/forms/{form}/analytics/data` возвращает объект от `FormAnalyticsService::getAnalytics()`:

```json
{
  "total_submissions": 142,
  "unique_visitors": 98,
  "views": 426,
  "conversion_rate": 33.33,
  "daily": {
    "dates": ["2026-05-01", "2026-05-02", ...],
    "submissions": [3, 5, 2, 0, 7, ...]
  },
  "fields_completion": {
    "Name": 100.0,
    "Email": 95.8,
    "Message": 82.1
  },
  "avg_time_seconds": 0
}
```

Ключи ответа соответствуют возврату `FormAnalyticsService::getAnalytics()`.

#### Версионирование

| Метод  | Маршрут                                       | Назначение    |
|--------|-----------------------------------------------|---------------|
| GET    | `/admin/forms/{form}/versions`                | Список версий |
| POST   | `/admin/forms/{form}/versions`                | Создать снимок |
| POST   | `/admin/forms/{form}/restore/{version}`       | Восстановить из версии |

#### Лимит отправок

Лимит на отправку формы (`FormService::checkLimits()`):
- `entry_limit` (таблица `forms`) — общий лимит по форме
- `daily_limit` (таблица `forms`) — лимит в сутки с одного IP
- Runtime-параметры в `settings`: `max_entries`, `daily_limit_per_ip`

Любое превышение вызывает исключение с HTTP 429.

---

### 17.11 RBAC permissions

| RBAC permission | slug | Description |
|-----------|------|-----------|
| view forms | `forms.view` | View form list |
| create forms | `forms.create` | Create new forms |
| edit forms | `forms.edit` | Edit any form |
| delete forms | `forms.delete` | Delete any form |

---

### 17.12 Спам-защита

| Провайдер | Дефолт | Примечание |
|-----------|--------|-----------|
| Honeypot | ✅ Включён | Скрытое поле, заполненное ботами; имя поля настраивается через `config('forms.honeypot_field_name')` (деф. `vertex_honeypot`) |
| reCAPTCHA v2/v3 | ❌ Опционально | Включается в настройках формы, требует ключи; для v2 правило `g-recaptcha-response\|captcha`, для v3 `recaptcha_token` |
| Cloudflare Turnstile | ❌ Опционально | `config('forms.turnstile_enabled')`; альтернатива reCAPTCHA без трекинга пользователя |

Конфиг `config/forms.php`:

```php
'honeypot_enabled'        => env('FORMS_HONEYPOT_ENABLED', true),
'honeypot_field_name'     => env('FORMS_HONEYPOT_FIELD', 'vertex_honeypot'),

'recaptcha_enabled'       => env('FORMS_RECAPTCHA_ENABLED', false),
'recaptcha_version'       => env('FORMS_RECAPTCHA_VERSION', 'v2'),
'recaptcha_site_key'      => env('RECAPTCHA_SITE_KEY'),
'recaptcha_secret_key'    => env('RECAPTCHA_SECRET_KEY'),
'recaptcha_min_score'     => env('FORMS_RECAPTCHA_MIN_SCORE', 0.5),

'turnstile_enabled'       => env('FORMS_TURNSTILE_ENABLED', false),
'turnstile_site_key'      => env('TURNSTILE_SITE_KEY'),
'turnstile_secret_key'    => env('TURNSTILE_SECRET_KEY'),
```

Интерфейс концепции (пока не выделен в отдельные классы):

```php
interface SpamProtectionRule
{
    public function isEnabled(array $settings): bool;
    public function validate(Request $request, Form $form): ?string; // null = ok, string = error message
    public function render(Form $form): string; // HTML для фронта
}
```

> **Примечание:** В текущем коде honeypot и reCAPTCHA правила реализованы
> напрямую в `FormService::buildValidationRules()` и `FormService::validate()`,
> а не через отдельные классы `HoneypotRule`/`RecaptchaRule`. Выделение в
> отдельные классы-правила запланировано на v0.11.

---

### 17.13 Включение формы на страницу

Форма регистрируется как блок Page Builder:

```json
{
  "id": "form_block_1",
  "type": "form",
  "content": { "form_id": 5 },
  "settings": { "form_id": "5", "title": "Связаться с нами" }
}
```

Рендерер вызывает `resources/forms/front/FormRenderer.vue`, который загружает конфигурацию формы через `FormPublicController::config()` и рендерит поля через Vue-компоненты. Для публичного доступа используется связка `FormPublicController::submit()` + Vue-форма на фронте.

---

### 17.14 Импорт / Экспорт

```php
// Экспорт — возвращает вложенный массив
$service->export($form);
// → ['form' => ['id'=>..., 'name'=>..., 'slug'=>..., 'type'=>'standard', 'description'=>..., 'settings'=>..., 'fields'=>[...]]]

// Импорт — принимает массив из export() или совместимый
$importService->import($formData);
// → Возвращает готовый инстанс Form, поля создаются автоматически
```

Схема версионируется через таблицу `form_versions` и поле `version_number`. Миграторы живут в `app/Forms/Migrations/`.

---

### 17.15 Этапы реализации

#### Этап 1 — Сущности и БД (2 дня)

- [x] Создать таблицы `forms`, `form_fields`, `form_submissions`, `form_submission_values`
- [x] Модели `Form`, `FormField`, `FormSubmission`, `FormSubmissionValue` с Eloquent
- [x] Реестр `FieldTypeRegistry` с предзагруженными типами полей (статический массив `FIELD_TYPES`)
- [x] Базовые типы полей: `text`, `email`, `textarea`, `select`, `radio`, `checkbox`, `file`, `date`, `hidden`, `heading`, `divider`, `html`, `calculator`, `checkbox_group`, `page_break`

#### Этап 2 — Конструктор формы (3 дня)

- [x] Vue-компонент `BuilderCanvas.vue` с drag & drop (SortableJS / Vue.Draggable)
- [x] `FieldNode.vue` — узел поля в canvas
- [x] `SettingsPanel.vue` — правая панель, генерируемая из `FieldTypeRegistry::getSchema()`
- [x] `FormService::create()`, `update()`, `delete()`
- [x] Autosave каждые 30 сек (debounce)

#### Этап 3 — Валидация и спам-защита (1 дня)

- [x] Валидация отправки в `FormService::validate()` с динамическими правилами из реестра
- [x] Встроенная honeypot-защита по умолчанию (`honeypot_enabled: true` в `settings`)
- [x] reCAPTCHA v2: скрытый токен `recaptcha_token`, v3 по требованию
- [ ] Визуальная проверка на фронте перед отправкой (реализован частично в ValidationModal)

#### Этап 4 — Условная логика (1 день)

- [x] `FormConditionEngine` с поддержкой `equals`, `not_equals`, `contains`, `greater_than`, `less_than`, `is_empty`, `is_not_empty`
- [x] `ConditionalLogicModal.vue` в конструкторе для задания операторов и значений условий
- [x] `evaluateFields()` вызывается в `FormService::validate()` перед проверкой правил
- [x] Реактивное переключение видимости полей в Preview

#### Этап 5 — Обработка отправки (1 день)

- [x] `FormService::submit()` — валидация, сохранение отправки, отправка уведомлений
- [x] Уведомление по e-mail через `App\System\Services\EmailService` (не `Mail::raw`)
- [ ] Обработка файловых загрузок (валидация по `config/forms.php` — `allowed_mime_types`, `max_file_size`) — мин. размер реализован в `FieldTypeRegistry`, чтение из `config/forms.php` не используется
- [ ] Редирект или success-сообщение на фронте (частично реализован через `success_message` в `settings`)

#### Этап 6 — Админка и импорт/экспорт (1 день)

- [x] Список форм в админке (Inertia страница)
- [x] Страница просмотра отправок
- [x] Экспорт отправок в CSV
- [x] Импорт/экспорт формы в JSON

#### Этап 7 — Интеграция с Page Builder (0.5 дня)

- [ ] Блок `<x-builder.form :form-id="...">`
- [ ] `FormRenderer` для публичного фронта
- [ ] Flash-сообщения после отправки

**Итого: ~9.5 рабочих дней.**

---

### 17.16 Acceptance criteria (v0.10)

| # | Критерий |
|---|----------|
| AC-01 | Админ может открыть `/admin/forms` и увидеть список форм |
| AC-02 | Админ может создать новую форму с именем и типом |
| AC-03 | В конструкторе можно перетащить поле из панели в canvas |
| AC-04 | Поле `text` имеет настройки: label, placeholder, required, maxlength |
| AC-05 | Поле `select` имеет настройки: опции (key/label), multiple |
| AC-06 | Условие `show/hide` поля работает: `select == "Да" → show:text` |
| AC-07 | При попытке отправить форму с незаполненным обязательным полем — ошибка на фронте |
| AC-08 | При отправке формы создаётся строка в `form_submissions`, значения полей в `form_submission_values` и отправляется e-mail |
| AC-09 | В админке виден список отправок с фильтрацией по форме |
| AC-10 | Форма экспортируется в JSON и импортируется обратно без потери данных |
| AC-11 | Форму можно вставить на страницу через блок Page Builder |
| AC-12 | Honeypot отсеивает ≥95% отправок от ботов в условиях теста |
| AC-13 | Отправка формы отклоняется если достигнут `entry_limit` или суточный лимит `daily_limit` на IP, возвращается ошибка 429 |

---

### 17.17 Бэклог после v0.10

- Платёжные поля (Stripe, PayPal) — паттерн из `stripe.php`, `paypal.php`
- reCAPTCHA v2 / v3 полная интеграция
- Upload-поле с ограничением по типу и размеру файла
- Поле "Дата и время" с условной логикой по дню/месяцу
- Подтверждение по e-mail (email verification before storing)
- Многошаговые формы с прогресс-баром
- Условные уведомления (email routing по значению поля)
- Интеграция с вебхуками на отправку
- Условный редирект после отправки
- Подсчёт и отображение статистики по форме (графики)
- Режим "не хранить отправки" (GDPR compliance)
- Запросы на удаление отправок по требованию пользователя

---

### 17.18 Известные ограничения и недокументированное

Ниже то, что имеет место уже в коде, но не описано в §17 или описано неполностью.

#### Структура модуля

| Объект | Статус | Примечание |
|--------|--------|------------|
| `src/Events/` | ❌ Пустая папка | События `FormSubmitted`, `FormCreated` не определены и не выбрасываются из кода |
| `src/Listeners/` | ❌ Пустая папка | Подписчиков на события нет. Папка зарезервирована для будущих уведомлений и вебхуков |
| `src/Rules/` | ❌ Не создана | `FormCanBeSubmittedRule`, `FieldConditionRule` отсутствуют; логика проверки встроена в `FormService::checkLimits()` и `FormConditionEngine::evaluateFields()` |
| `src/Support/` | ❌ Пустая | Резерв для фасадов и вспомогательных классов |

#### Поведение формы

| Объект | Статус | Примечание |
|--------|--------|------------|
| reCAPTCHA v3 верификация score | ❌ Не реализована | Валидация только добавляет правило `required`, но не вызывает API Cloudflare для проверки score |
| Turnstile | ❌ Не реализована | Ключи есть в конфиге, но нет вызова API Cloudflare или рендера виджета |
| `success_message` / `error_message` | ✅ Работает | Читаются из `$form->settings['success_message']` / `error_message` в `FormPublicController::submit()` |
| Запись `submitted_at` | ❌ Не реализовано | В таблице `form_submissions` нет столбца `submitted_at`; время отправки отслеживается через `created_at` |
| Форма без полей | ❌ Не защищена | `syncFields([])` удаляет все существующие поля; нет отдельной проверки на пустую форму при сохранении |

#### Экспорт в CSV (`FormSubmissionController::export()`)

| Объект | Статус | Примечание |
|--------|--------|------------|
| Ограничение по числу строк | ❌ Не ограничено | Загружает все отправки через `->latest()->get()` без пагинации, риск утечки памяти при 100k+ отправок |
| Разделитель | ⚠️ Только запятые | Значения экранируются двойными кавычками, но нет TSV или пользовательского разделителя |
| Имя файла | `form-{slug}-{YYYY-MM-DD}.csv` | Всегда содержит текущую дату, не дату периода выборки |

#### Конфиг `config/forms.php`

| Ключ | Используется? | Примечание |
|------|--------------|------------|
| `default_from_email` / `default_from_name` | ⚠️ Не напрямую | Управляются через `App\System\Services\EmailService`, который читает `MAIL_FROM_ADDRESS` из `.env` |
| `max_entries_global` / `daily_limit_per_ip_global` | ✅ Частично | Fallback в `FormService::checkLimits()`, если нет значений в `settings` формы |
| `honeypot_*` | ✅ | Используются в `FormService::buildValidationRules()` |
| `recaptcha_*`, `turnstile_*` | ⚠️ Ключи в конфиге | Вызов API для v2/v3 и Turnstile не реализован |
| `max_file_size` / `allowed_mime_types` | ❌ Не используется | Цифры зашиты в `FieldTypeRegistry` в схеме поля `file` |
| `upload_dir` | ❌ Не используется | Загрузка файлов не реализована (поле `file` не сохраняет путь в `form_submission_values`) |
| `log_form_views` / `log_view_details` | ✅ Частично | `FormAnalyticsService::recordView()` логирует просмотры через `FormAnalytic`, нет отдельной таблицы `form_view_logs` |
| `analytics_retention_days` | ❌ Не используется | Данные `form_analytics` не удаляются по истечении срока |
| `allow_import_export` | ❌ Не используется | Импорт/экспорт всегда разрешён, нет проверки флага |
| `auto_response_enabled` | ❌ Не используется | Отправка уведомлений управляется `notify_user` / `autoresponder_body` в `settings` |
| `currency*` | ❌ Не используется | Калькулятор возвращает число, не форматирует в валюту |
| `auto_snapshot_on_save` | ❌ Не используется | `FormService::update()` не вызывает `createSnapshot()` автоматически |
| `max_snapshots_per_form` | ❌ Не используется | В `FormService::createSnapshot()` нет лимита на количество версий |

---

### 17.19 Отсутствие vertex-forms в дорожной карте и плане UX Builder

На момент обновления этого документа существуют **два документарных разрыва** между кодом и планом:

#### 17.19.1 Дорожная карта (`docs/roadmap.md`)

| Версия | Статус vertex-forms |
|--------|-------------------|
| v0.1–v0.5 | ❌ Не упоминается |
| v0.6 | ❌ Не упоминается |
| v0.7 | ❌ Не упоминается |
| v0.8 | ❌ Не упоминается |
| v0.9 | ❌ Не упоминается |
| v1.0 | ❌ Не упоминается |
| Backlog после v1.0 | ✅ Упоминается как "Forms module" (строка 280) |

Модуль `vertex-forms` имеет **существенную кодовую базу** (6 таблиц, 3 контроллера, 5 сервисов, 6 моделей, конфиг, маршруты, реестр типов полей), но в дорожной карте отсутствует полностью до бэклога после v1.0. Это создаёт риск:
— требования к модулю не фиксированы в виде acceptance criteria на текущие версии
— приоритет и сроки не определены
— интеграция с другими модулями (Page Builder, Media, SEO, Auth) не планируется явно

#### 17.19.2 План доставки UX Builder (`docs/architecture/ux-builder-inspired-delivery-plan.md`)

План описывает 6 фаз работы с визуальным конструктором страниц, но:
— не упоминает `FormRenderer.vue` (из §17.3, `resources/forms/front/FormRenderer.vue`)
— не упоминает `ConditionalLogicModal.vue` (из §17.3, `resources/forms/builder/ConditionalLogicModal.vue`)
— не упоминает `ConditionalLogicEngine` и условную логику полей вообще
— не упоминает `FormConditionEngine` как бэкенд-движок условий

При этом формы описываются как блок Page Builder (§17.13: `<x-builder.form :form-id="...">`) — то есть логика конструктора форм должна быть частью плана UX Builder, но отдельно не выделена.

#### 17.19.3 Фактическое состояние

| Объект | Статус |
|--------|--------|
| `VertexFormsServiceProvider` | ✅ Зарегистрирован, загружает маршруты и миграции |
| Маршруты `web.php` | ✅ Публичный `/forms/{form:slug}` и `/forms/{form:slug}/submit` |
| Маршруты `api.php` | ✅ REST API CRUD + field-registry |
| Маршруты `admin.php` | ✅ Админка: Forms CRUD, submissions, analytics, versions |
| Конфиг `config/forms.php` | ✅ 21 ключ |
| `FormTypeRegistry` | ✅ 15 типов полей |
| В админке (sidebar/topbar) | ❌ Формы не отображаются в навигации — маршруты есть, но нет пункта меню |

> **Рекомендация:** Добавить версию с vertex-forms в `docs/roadmap.md` (например, как v0.6 + v0.7- расширение) и отразить Forms-блоки и условную логику в `docs/architecture/ux-builder-inspired-delivery-plan.md` на уровне фаз.

---

_Раздел 17 добавлен в `docs/forminator-patterns.md` на основе анализа Forminator v1.51.0._

---

_Документ создан на основе анализа Forminator v1.51.0 в репозитории Vertex-CMS._
