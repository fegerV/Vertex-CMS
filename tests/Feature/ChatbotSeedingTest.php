<?php

namespace Tests\Feature;

use App\Models\Chatbot;
use Database\Seeders\ChatbotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotSeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_chatbots_are_seeded_with_their_json_settings(): void
    {
        $this->seed(ChatbotSeeder::class);

        $this->assertSame(3, Chatbot::count());

        $default = Chatbot::where('is_default', true)->firstOrFail();

        $this->assertSame('openai', $default->llm_provider);
        $this->assertSame('gpt-4o-mini', $default->llm_model);
        $this->assertSame('vertex-assistant', $default->slug);
        $this->assertIsArray($default->config);
        $this->assertTrue($default->config['use_page_context']);
        $this->assertSame(['pdf', 'txt', 'doc', 'docx'], $default->config['allowed_file_types']);
        $this->assertSame(10, $default->rate_limits['messages_per_minute']);
        $this->assertSame(['message_received', 'form_submitted'], $default->webhook_events);
    }

    public function test_helpers_read_settings_from_the_config_column(): void
    {
        $chatbot = Chatbot::create([
            'name' => 'Config Bot',
            'slug' => 'config-bot',
            'config' => [
                'use_page_context' => true,
                'enable_web_search' => true,
                'enable_file_upload' => true,
                'max_file_size_mb' => 5,
            ],
        ]);

        $this->assertTrue($chatbot->canWebSearch());
        $this->assertTrue($chatbot->canUploadFiles());
        $this->assertSame(5 * 1024 * 1024, $chatbot->getMaxFileSizeBytes());
        $this->assertStringContainsString('Config Bot', $chatbot->name);
        $this->assertStringContainsString(
            'Текущая страница: Delivery',
            $chatbot->getSystemPromptWithPageContext('Delivery')
        );
    }
}
