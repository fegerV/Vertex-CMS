<template>
  <div v-if="enabled" class="telegram-widget" :class="[styleClass, positionClass]">
    <!-- Launcher button -->
    <button 
      v-if="style !== 'inline'"
      @click="openChat"
      class="tg-launcher"
      :style="{ backgroundColor: buttonColor }"
      aria-label="Open Telegram chat"
      title="{{ greeting || 'Написать в Telegram' }}">
      
      <!-- Telegram icon SVG -->
      <svg viewBox="0 0 24 24" class="tg-icon" fill="currentColor">
        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
      </svg>
      
      <span v-if="greeting" class="tg-greeting-text">{{ greeting }}</span>
    </button>

    <!-- Chat window (simple iframe replacement with direct link) -->
    <div v-if="isOpen && !useIframe" class="tg-chat-window">
      <div class="tg-chat-header" :style="{ backgroundColor: buttonColor }">
        <span class="tg-chat-title">Telegram</span>
        <button @click="closeChat" class="tg-close-btn" aria-label="Close">×</button>
      </div>
      <div class="tg-chat-body">
        <p class="tg-chat-message">{{ chatMessage }}</p>
        <a 
          :href="telegramUrl" 
          target="_blank" 
          class="tg-open-button"
          :style="{ backgroundColor: buttonColor }">
          Открыть Telegram
        </a>
        <p class="tg-hint" v-if="showOnlineStatus">
          Статус: {{ isUserOnline ? '🟢 Онлайн' : '⚫ Не в сети' }}
        </p>
      </div>
    </div>

    <!-- Inline widget (embedded iframe) -->
    <div v-if="style === 'inline'" class="tg-inline-widget" :style="{ width: '100%', height: '500px' }">
      <iframe 
        v-if="widgetIframeUrl"
        :src="widgetIframeUrl" 
        width="100%" 
        height="100%" 
        frameborder="0"
        allow="clipboard-write">
      </iframe>
      <div v-else class="tg-error">
        Настройте Bot Token и Chat ID в админке для встроенного виджета.
        <a :href="telegramUrl" target="_blank">Написать в Telegram</a>
      </div>
    </div>
  </div>
</template>

<script>
const { defineComponent, ref, computed, onMounted } = Vue;

export default defineComponent({
  name: 'TelegramWidget',
  setup() {
    const isOpen = ref(false);
    const isUserOnline = ref(false);

    const config = computed(() => window.telegramWidgetConfig || {});
    
    const enabled = computed(() => config.value?.enabled ?? false);
    const style = computed(() => config.value?.widget_style ?? 'floating');
    const position = computed(() => config.value?.widget_position ?? 'bottom-right');
    const greeting = computed(() => config.value?.greeting ?? null);
    const buttonColor = computed(() => config.value?.color ?? '#0088cc');
    const showOnlineStatus = computed(() => config.value?.show_online_status ?? false);
    const messagePrefill = computed(() => config.value?.message_prefill ?? '');
    const username = computed(() => config.value?.username ?? null);

    const styleClass = computed(() => `tg-style-${style.value}`);
    const positionClass = computed(() => `tg-pos-${position.value}`);
    
    const telegramUrl = computed(() => {
      if (!username.value) return '#';
      let url = `https://t.me/${username.value}`;
      if (messagePrefill.value) {
        url += `?text=${encodeURIComponent(messagePrefill.value)}`;
      }
      return url;
    });

    const widgetIframeUrl = computed(() => {
      const botToken = config.value?.bot_token;
      const chatId = config.value?.chat_id;
      if (botToken && chatId) {
        return `https://widget.telegram.org/chat/${botToken}/${chatId}?width=100%&height=500&single=false`;
      }
      return null;
    });

    const useIframe = computed(() => style.value === 'inline' && widgetIframeUrl.value);

    const chatMessage = computed(() => {
      return messagePrefill.value || 'Напишите нам в Telegram, мы ответим как можно скорее.';
    });

    const openChat = () => {
      if (style.value === 'inline') return;
      isOpen.value = true;
    };

    const closeChat = () => {
      isOpen.value = false;
    };

    // Check online status via Telegram Bot API (optional)
    const checkOnlineStatus = async () => {
      if (!showOnlineStatus.value) return;
      try {
        // This requires a bot token; we can only check if user is online if they started the bot
        const botToken = config.value?.bot_token;
        const chatId = config.value?.chat_id;
        if (botToken && chatId) {
          const resp = await fetch(`https://api.telegram.org/bot${botToken}/getChatMember?chat_id=${chatId}&user_id=${chatId}`);
          const data = await resp.json();
          isUserOnline.value = data?.result?.status === 'online';
        }
      } catch (e) {
        // ignore
      }
    };

    onMounted(() => {
      if (showOnlineStatus.value) {
        // poll every 30 sec
        checkOnlineStatus();
        setInterval(checkOnlineStatus, 30000);
      }
    });

    return {
      isOpen,
      enabled,
      style,
      position,
      greeting,
      buttonColor,
      showOnlineStatus,
      messagePrefill,
      username,
      styleClass,
      positionClass,
      telegramUrl,
      widgetIframeUrl,
      useIframe,
      chatMessage,
      isUserOnline,
      openChat,
      closeChat,
    };
  },
});
</script>

<style scoped>
.telegram-widget {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  z-index: 999999;
}

/* Launcher button */
.tg-launcher {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
}
.tg-launcher:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}
.tg-icon {
  width: 32px;
  height: 32px;
  color: white;
}
.tg-greeting-text {
  position: absolute;
  bottom: -30px;
  left: 50%;
  transform: translateX(-50%);
  white-space: nowrap;
  font-size: 12px;
  background: rgba(0,0,0,0.7);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  opacity: 0;
  transition: opacity 0.2s;
  pointer-events: none;
}
.tg-launcher:hover .tg-greeting-text {
  opacity: 1;
}

/* Positions */
.tg-pos-bottom-right {
  bottom: 20px;
  right: 20px;
}
.tg-pos-bottom-left {
  bottom: 20px;
  left: 20px;
}
.tg-pos-bottom-center {
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
}

/* Chat window (simple modal) */
.tg-chat-window {
  position: fixed;
  bottom: 90px;
  width: 320px;
  max-width: calc(100vw - 40px);
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.2);
  overflow: hidden;
  z-index: 999998;
}
.tg-pos-bottom-right .tg-chat-window,
.tg-pos-bottom-left .tg-chat-window {
  /* aligned to button edge automatically via CSS logic */
}
.tg-pos-bottom-right .tg-chat-window { right: 20px; }
.tg-pos-bottom-left .tg-chat-window { left: 20px; }
.tg-pos-bottom-center .tg-chat-window { 
  left: 50%; 
  transform: translateX(-50%);
}

.tg-chat-header {
  padding: 16px;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
}
.tg-close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  line-height: 1;
}
.tg-chat-body {
  padding: 20px;
  text-align: center;
}
.tg-chat-message {
  margin-bottom: 16px;
  color: #374151;
  line-height: 1.5;
}
.tg-open-button {
  display: inline-block;
  color: white;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
}
.tg-hint {
  margin-top: 12px;
  font-size: 12px;
  color: #9ca3af;
}

/* Inline */
.tg-inline-widget {
  border: none;
  border-radius: 8px;
  overflow: hidden;
}
.tg-error {
  padding: 20px;
  text-align: center;
  color: #ef4444;
}

/* Button style variations */
.tg-style-button .tg-launcher {
  width: auto;
  height: auto;
  padding: 12px 24px;
  border-radius: 8px;
  flex-direction: row;
  gap: 8px;
}
.tg-style-button .tg-icon {
  width: 20px;
  height: 20px;
}

.tg-style-badge .tg-launcher {
  width: 56px;
  height: 56px;
  border: 3px solid white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Hide greeting tooltip for badge/floating */
.tg-style-badge .tg-greeting-text,
.tg-style-floating .tg-greeting-text {
  display: none;
}
</style>
