<?php

namespace Database\Seeders;

use App\Models\Chatbot;
use Illuminate\Database\Seeder;

/**
 * Сидер для создания демо-чатбота
 * Используется в разработке для быстрого тестирования функционала
 */
class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем основного чатбота по умолчанию
        Chatbot::create([
            'name' => 'Vertex Assistant',
            'slug' => 'vertex-assistant',
            'description' => 'Умный помощник для ответов на вопросы о сайте',
            'is_active' => true,
            'is_default' => true,
            
            // Конфигурация LLM
            'llm_provider' => 'openai',
            'llm_model' => 'gpt-4o-mini',
            'temperature' => 0.7,
            'max_tokens' => 1000,
            
            // Системный промпт
            'system_prompt' => "Вы - полезный ассистент компании VertexCMS. Отвечайте кратко и по делу. Если не знаете ответа - честно признайтесь. Используйте информацию из контекста страницы для более релевантных ответов.",
            
            // Настройки поведения
            'config' => [
                'use_page_context' => true,
                'use_knowledge_base' => true,
                'enable_web_search' => false,
                'enable_image_generation' => false,
                'enable_voice_input' => false,
                'enable_file_upload' => true,
                'max_file_size_mb' => 20,
                'allowed_file_types' => ['pdf', 'txt', 'doc', 'docx'],
            ],
            
            // Лимиты
            'rate_limits' => [
                'messages_per_minute' => 10,
                'messages_per_hour' => 100,
                'tokens_per_day' => 50000,
            ],
            
            // UI настройки
            'ui_config' => [
                'welcome_message' => 'Привет! Я виртуальный помощник VertexCMS. Чем могу помочь?',
                'placeholder_text' => 'Введите ваш вопрос...',
                'primary_color' => '#3B82F6',
                'position' => 'bottom-right',
                'show_avatar' => true,
                'avatar_url' => null,
            ],
            
            // Webhook для интеграции с n8n
            'webhook_url' => null,
            'webhook_events' => ['message_received', 'form_submitted'],
            
            // Привязка к страницам (null = все страницы)
            'page_ids' => null,
            
            // Роли пользователей, которые могут использовать бота
            'allowed_roles' => null, // null = все роли
        ]);

        // Создаем специализированного бота для поддержки
        Chatbot::create([
            'name' => 'Support Bot',
            'slug' => 'support-bot',
            'description' => 'Бот технической поддержки для ответов на вопросы клиентов',
            'is_active' => true,
            'is_default' => false,
            
            'llm_provider' => 'openai',
            'llm_model' => 'gpt-4o',
            'temperature' => 0.5,
            'max_tokens' => 1500,
            
            'system_prompt' => "Вы - специалист технической поддержки VertexCMS. Ваша задача - помогать пользователям решать проблемы с CMS. Будьте вежливы, терпеливы и предоставляйте пошаговые инструкции. Если проблема сложная, предложите создать тикет в поддержку.",
            
            'config' => [
                'use_page_context' => true,
                'use_knowledge_base' => true,
                'enable_web_search' => false,
                'enable_image_generation' => false,
                'enable_voice_input' => false,
                'enable_file_upload' => true,
                'max_file_size_mb' => 20,
                'allowed_file_types' => ['pdf', 'txt', 'png', 'jpg'],
                'auto_create_ticket' => true,
            ],
            
            'rate_limits' => [
                'messages_per_minute' => 5,
                'messages_per_hour' => 50,
                'tokens_per_day' => 100000,
            ],
            
            'ui_config' => [
                'welcome_message' => 'Здравствуйте! Я бот технической поддержки. Опишите вашу проблему, и я постараюсь помочь.',
                'placeholder_text' => 'Опишите проблему...',
                'primary_color' => '#10B981',
                'position' => 'bottom-right',
                'show_avatar' => true,
                'avatar_url' => null,
            ],
            
            'webhook_url' => env('N8N_SUPPORT_WEBHOOK_URL'),
            'webhook_events' => ['message_received', 'form_submitted', 'ticket_created'],
            
            'page_ids' => null,
            'allowed_roles' => null,
        ]);

        // Создаем бота для сбора лидов
        Chatbot::create([
            'name' => 'Lead Generator',
            'slug' => 'lead-generator',
            'description' => 'Бот для сбора контактных данных потенциальных клиентов',
            'is_active' => true,
            'is_default' => false,
            
            'llm_provider' => 'openai',
            'llm_model' => 'gpt-4o-mini',
            'temperature' => 0.8,
            'max_tokens' => 800,
            
            'system_prompt' => "Вы - дружелюбный помощник по продажам. Ваша задача - мягко подвести пользователя к оставлению контактных данных. Не будьте навязчивы. Предложите бесплатную консультацию или демо-версию.",
            
            'config' => [
                'use_page_context' => true,
                'use_knowledge_base' => false,
                'enable_web_search' => false,
                'enable_image_generation' => false,
                'enable_voice_input' => false,
                'enable_file_upload' => false,
                'collect_leads' => true,
                'lead_form_fields' => [
                    ['name' => 'full_name', 'label' => 'Ваше имя', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'phone', 'label' => 'Телефон', 'type' => 'tel', 'required' => false],
                    ['name' => 'company', 'label' => 'Компания', 'type' => 'text', 'required' => false],
                    ['name' => 'budget', 'label' => 'Бюджет проекта', 'type' => 'select', 'required' => false, 'options' => ['До $1000', '$1000-$5000', '$5000-$10000', 'Более $10000']],
                ],
            ],
            
            'rate_limits' => [
                'messages_per_minute' => 15,
                'messages_per_hour' => 150,
                'tokens_per_day' => 30000,
            ],
            
            'ui_config' => [
                'welcome_message' => 'Привет! Interested в наших услугах? Расскажу подробнее и отвечу на вопросы!',
                'placeholder_text' => 'Задайте вопрос...',
                'primary_color' => '#F59E0B',
                'position' => 'bottom-right',
                'show_avatar' => true,
                'avatar_url' => null,
            ],
            
            'webhook_url' => env('N8N_LEAD_WEBHOOK_URL'),
            'webhook_events' => ['message_received', 'form_submitted', 'lead_captured'],
            
            'page_ids' => null,
            'allowed_roles' => null,
        ]);

        $this->command->info('✓ Chatbot seeder completed. Created 3 demo chatbots.');
    }
}
