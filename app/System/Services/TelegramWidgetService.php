<?php

namespace App\System\Services;

use Illuminate\Support\Facades\Config;

class TelegramWidgetService
{
    public function isEnabled(): bool
    {
        return (bool) config_value('telegram.enabled', false);
    }

    public function getUsername(): ?string
    {
        $username = config_value('telegram.username');
        return $username ? ltrim($username, '@') : null;
    }

    public function getBotToken(): ?string
    {
        return config_value('telegram.bot_token');
    }

    public function getChatId(): ?string
    {
        return config_value('telegram.chat_id');
    }

    public function getWidgetStyle(): string
    {
        return config_value('telegram.widget_style', 'floating');
    }

    public function getWidgetPosition(): string
    {
        return config_value('telegram.widget_position', 'bottom-right');
    }

    public function getGreeting(): ?string
    {
        return config_value('telegram.greeting');
    }

    public function getColor(): ?string
    {
        return config_value('telegram.color');
    }

    public function showOnlineStatus(): bool
    {
        return (bool) config_value('telegram.show_online_status', false);
    }

    public function getMessagePrefill(): ?string
    {
        return config_value('telegram.message_prefill');
    }

    /**
     * Build Telegram deep link URL
     */
    public function getTelegramUrl(): ?string
    {
        $username = $this->getUsername();
        if (!$username) {
            return null;
        }

        $prefill = $this->getMessagePrefill();
        $url = "https://t.me/{$username}";
        
        if ($prefill) {
            $url .= '?text=' . urlencode($prefill);
        }

        return $url;
    }

    /**
     * Build Telegram Widget iframe URL (for embedded widget)
     * Requires bot_token and chat_id
     */
    public function getWidgetIframeUrl(): ?string
    {
        $botToken = $this->getBotToken();
        $chatId = $this->getChatId();
        
        if (!$botToken || !$chatId) {
            return null;
        }

        // Telegram Widget API
        return "https://widget.telegram.org/chat/{$botToken}/{$chatId}?width=100%&height=500&single=false";
    }

    /**
     * Get inline widget script URL (for <script> tag)
     * This creates a floating button that opens Telegram widget
     */
    public function getInlineWidgetScript(): string
    {
        $username = $this->getUsername();
        if (!$username) {
            return '';
        }

        $options = [
            'url' => "https://t.me/{$username}",
            'width' => 360,
            'height' => 480,
        ];

        if ($this->getMessagePrefill()) {
            $options['text'] = $this->getMessagePrefill();
        }

        return 'https://telegram.org/js/telegram-widget.js?' . http_build_query($options);
    }
}
