<?php

return [

    // ── Block / page builder ──────────────────────────────
    'block_name' => 'Form (Universal Builder)',
    'block_description' => 'Powerful form builder: calculator, conditions, multilingual, files, payments',
    'block_category' => 'Dynamic Blocks',

    // ── Admin sidebar ─────────────────────────────────────
    'nav_label' => 'Forms',
    'nav_description' => 'Form and calculator builder',
    'nav_create' => 'New form',
    'nav_back_to_dashboard' => 'Back to dashboard',

    // ── Form listing ──────────────────────────────────────
    'listing_title' => 'Form Builder',
    'listing_subtitle' => 'Universal form and calculator builder',
    'form_title_default' => 'Untitled',
    'form_type_standard' => 'Standard',
    'form_type_calculator' => 'Calculator',
    'form_type_survey' => 'Survey',
    'form_type_poll' => 'Poll',
    'edit_form' => 'Edit',
    'duplicate_form' => 'Duplicate',
    'delete_form' => 'Delete',
    'preview_form' => 'Preview',
    'export_json' => 'Export JSON',
    'import_json' => 'Import JSON',
    'no_forms' => 'No forms yet. Create your first form.',
    'total_submissions' => 'Submissions',
    'today' => 'Today',

    // ── Crud messages ─────────────────────────────────────
    'created' => 'Form created.',
    'updated' => 'Form updated.',
    'deleted' => 'Form deleted.',
    'duplicated' => 'Form duplicated.',
    'imported' => 'Form imported.',
    'save' => 'Save',
    'saving' => 'Saving...',
    'saved' => 'Saved!',
    'save_failed' => 'Save failed',
    'save_failed_detail' => 'Could not save the form. Please try again.',

    // ── Frontend form rendering ───────────────────────────
    'form_title' => 'Form',
    'submit' => 'Submit',
    'submitting' => 'Submitting...',
    'page_of' => 'Page {page} of {total}',
    'prev' => 'Back',
    'next' => 'Next',
    'success_title' => 'Thank you!',
    'form_not_found' => 'Form not found',
    'required_field' => 'required',
    'validation_invalid_email' => 'Invalid email address',
    'validation_min' => 'Minimum {min}',
    'validation_max' => 'Maximum {max}',
    'validation_file_too_big' => 'File too large (max {max} KB)',
    'error_network' => 'Network error. Please try again.',
    'error_required_field' => '{label} is required',

    // ── Validation errors ─────────────────────────────────
    'validation_required' => 'This field is required',
    'validation_email' => 'Invalid email address',
    'validation_numeric' => 'Must be a number',
    'validation_file' => 'Must be a file',
    'validation_mimes' => 'Invalid file type',
    'validation_honeypot_spam' => 'Spam detected',
    'validation_captcha_failed' => 'CAPTCHA verification failed.',
    'validation_captcha_unavailable' => 'CAPTCHA verification is temporarily unavailable.',

    // ── Server errors ─────────────────────────────────────
    'error_validation_failed' => 'Validation failed',
    'error_daily_limit_reached' => 'Daily submission limit reached.',
    'error_form_closed' => 'Form is closed: maximum submissions reached.',
    'error_form_not_open' => 'Form is not yet open.',
    'error_form_closed_date' => 'Form is closed.',
    'error_submission_failed' => 'Form submission error',
    'error_rate_limit' => 'Too many submissions. Please try again later.',

    // ── Generic ───────────────────────────────────────────
    'field_type_text' => 'Text Input',
    'field_type_email' => 'Email',
    'field_type_tel' => 'Phone',
    'field_type_number' => 'Number',
    'field_type_textarea' => 'Textarea',
    'field_type_select' => 'Dropdown',
    'field_type_radio' => 'Radio Buttons',
    'field_type_checkbox' => 'Checkbox',
    'field_type_checkbox_group' => 'Checkbox Group',
    'field_type_file' => 'File Upload',
    'field_type_date' => 'Date',
    'field_type_hidden' => 'Hidden Field',
    'field_type_calculator' => 'Calculator',
    'field_type_heading' => 'Heading',
    'field_type_divider' => 'Divider',
    'field_type_html' => 'Custom HTML',
    'field_type_page_break' => 'Page Break',

    // ── Field label / generic ─────────────────────────────
    'field_label_asterisk'   => '*',
    'select_placeholder'     => 'Select...',
    'search_placeholder'     => 'Search forms...',
    'calculator_label'       => 'Calculator',
    'result'                 => 'Result:',
    'calculate'              => 'Calculate',
    'max_file_size_label'    => 'Maximum size: {max} KB',

    // ── Confirm / alert dialogs ───────────────────────────
    'confirm_delete'         => 'Delete form «{name}»? All data will be lost.',
    'confirm_duplicate'      => 'Duplicate this form?',
    'error_unknown'          => 'Unknown error',

    // ── Admin listing labels ──────────────────────────────
    'status_active'    => 'Active',
    'status_inactive'  => 'Disabled',
    'actions'          => 'Actions',
    'title'            => 'Title',
    'type'             => 'Type',
    'created_at'       => 'Created',
    'status'           => 'Status',
    'empty_title'      => 'No forms created yet',
    'empty_subtitle'   => 'Create your first form to start collecting submissions.',
    'empty_cta'         => '+ Create form',
    'pagination_today' => '(today: {today})',
    'return_dashboard' => 'Back to dashboard',
    'duplicated_name_suffix' => 'copy',

    // ── Import / export ───────────────────────────────────
    'import_no_json'        => 'No JSON provided',
    'import_invalid_json'   => 'Invalid JSON: {message}',
    'import_failed'         => 'Import failed: {message}',
    'all_submissions_deleted' => 'All submissions deleted',
    'submit_success'          => 'Form submitted successfully!',
    'validation_fix_errors'   => 'Please fix the errors in the form.',

];
