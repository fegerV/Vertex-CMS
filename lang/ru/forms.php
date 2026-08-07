<?php

return [

    // ── Block / page builder ──────────────────────────────
    'block_name' => 'Форма (универсальный конструктор)',
    'block_description' => 'Мощный конструктор форм: калькулятор, условия, мультиязычность, файлы, оплата',
    'block_category' => 'Динамические блоки',

    // ── Admin sidebar ─────────────────────────────────────
    'nav_label' => 'Формы',
    'nav_description' => 'Конструктор форм и калькуляторов',
    'nav_create' => 'Новая форма',
    'nav_back_to_dashboard' => 'Назад к дашборду',

    // ── Form listing ──────────────────────────────────────
    'listing_title' => 'Конструктор форм',
    'listing_subtitle' => 'Универсальный конструктор калькуляторов и форм',
    'form_title_default' => 'Без названия',
    'form_type_standard' => 'Стандартная',
    'form_type_calculator' => 'Калькулятор',
    'form_type_survey' => 'Опрос',
    'form_type_poll' => 'Голосование',
    'edit_form' => 'Редактировать',
    'duplicate_form' => 'Дублировать',
    'delete_form' => 'Удалить',
    'preview_form' => 'Превью',
    'export_json' => 'Экспорт JSON',
    'import_json' => 'Импорт JSON',
    'no_forms' => 'Формы не созданы. Создайте первую форму.',
    'total_submissions' => 'Отправок',
    'today' => 'Сегодня',

    // ── Crud messages ─────────────────────────────────────
    'created' => 'Форма создана.',
    'updated' => 'Форма обновлена.',
    'deleted' => 'Форма удалена.',
    'duplicated' => 'Форма дублирована.',
    'imported' => 'Форма импортирована.',
    'save' => 'Сохранить',
    'saving' => 'Сохранение...',
    'saved' => 'Сохранено!',
    'save_failed' => 'Ошибка сохранения',
    'save_failed_detail' => 'Не удалось сохранить форму. Попробуйте позже.',

    // ── Frontend form rendering ───────────────────────────
    'form_title' => 'Форма',
    'submit' => 'Отправить',
    'submitting' => 'Отправка...',
    'page_of' => 'Страница {page} из {total}',
    'prev' => 'Назад',
    'next' => 'Далее',
    'success_title' => 'Спасибо!',
    'form_not_found' => 'Форма не найдена',
    'required_field' => 'обязательно для заполнения',
    'validation_invalid_email' => 'Некорректный email',
    'validation_min' => 'Минимум {min}',
    'validation_max' => 'Максимум {max}',
    'validation_file_too_big' => 'Файл слишком большой (макс. {max} КБ)',
    'error_network' => 'Ошибка сети. Попробуйте позже.',
    'error_required_field' => '{label} обязательно для заполнения',

    // ── Validation errors ─────────────────────────────────
    'validation_required' => 'Обязательное поле',
    'validation_email' => 'Некорректный email',
    'validation_numeric' => 'Должно быть числом',
    'validation_file' => 'Должно быть файлом',
    'validation_mimes' => 'Недопустимый тип файла',
    'validation_honeypot_spam' => 'Обнаружен спам',
    'validation_captcha_failed' => 'Не удалось пройти проверку CAPTCHA.',
    'validation_captcha_unavailable' => 'Проверка CAPTCHA временно недоступна.',

    // ── Server errors ─────────────────────────────────────
    'error_validation_failed' => 'Ошибка валидации',
    'error_daily_limit_reached' => 'Достигнут дневной лимит отправок.',
    'error_form_closed' => 'Форма закрыта: достигнут максимальный лимит отправок.',
    'error_form_not_open' => 'Форма ещё не открыта.',
    'error_form_closed_date' => 'Форма закрыта.',
    'error_submission_failed' => 'Ошибка отправки формы',
    'error_rate_limit' => 'Слишком много отправок. Повторите попытку позже.',

    // ── Generic ───────────────────────────────────────────
    'field_type_text' => 'Текстовое поле',
    'field_type_email' => 'Email',
    'field_type_tel' => 'Телефон',
    'field_type_number' => 'Число',
    'field_type_textarea' => 'Текстовая область',
    'field_type_select' => 'Выпадающий список',
    'field_type_radio' => 'Радиокнопки',
    'field_type_checkbox' => 'Чекбокс',
    'field_type_checkbox_group' => 'Группа чекбоксов',
    'field_type_file' => 'Загрузка файла',
    'field_type_date' => 'Дата',
    'field_type_hidden' => 'Скрытое поле',
    'field_type_calculator' => 'Калькулятор',
    'field_type_heading' => 'Заголовок',
    'field_type_divider' => 'Разделитель',
    'field_type_html' => 'HTML-блок',
    'field_type_page_break' => 'Разрыв страницы',

    // ── Field label / generic ──────────────────────────────
    'field_label_asterisk'   => '*',
    'select_placeholder'     => 'Выберите...',
    'search_placeholder'     => 'Поиск форм...',
    'calculator_label'       => 'Калькулятор',
    'result'                 => 'Результат:',
    'calculate'              => 'Рассчитать',
    'max_file_size_label'    => 'Максимальный размер: {max} KB',

    // ── Confirm / alert dialogs ────────────────────────────
    'confirm_delete'         => 'Удалить форму «{name}»? Все данные будут потеряны.',
    'confirm_duplicate'      => 'Дублировать форму?',
    'error_unknown'          => 'Неизвестная ошибка',

    // ── Admin listing labels ──────────────────────────────
    'status_active'    => 'Активна',
    'status_inactive'  => 'Отключена',
    'actions'          => 'Действия',
    'title'            => 'Название',
    'type'             => 'Тип',
    'created_at'       => 'Создана',
    'status'           => 'Статус',
    'empty_title'      => 'Формы не созданы',
    'empty_subtitle'   => 'Создайте первую форму, чтобы начать собирать заявки.',
    'empty_cta'         => '+ Создать форму',
    'pagination_today' => '(сегодня: {today})',
    'return_dashboard' => 'Назад к дашборду',
    'duplicated_name_suffix' => 'копия',

    // ── Import / export ───────────────────────────────────
    'import_no_json'        => 'JSON не предоставлен',
    'import_invalid_json'   => 'Неверный формат JSON: {message}',
    'import_failed'         => 'Ошибка импорта: {message}',
    'all_submissions_deleted' => 'Все отправки удалены',
    'submit_success'          => 'Форма успешно отправлена!',
    'validation_fix_errors'   => 'Пожалуйста, исправьте ошибки в форме.',

];
