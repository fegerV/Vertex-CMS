import { createApp } from 'vue';
import TelegramWidget from './components/TelegramWidget.vue';

// Config injected by Laravel into page
const config = window.telegramWidgetConfig || {};

if (config.enabled) {
    const app = createApp(TelegramWidget);
    app.mount('#telegram-widget');
}
