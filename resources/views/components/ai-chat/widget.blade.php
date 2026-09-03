@props([
    'title' => null, // Будет взято из chatbot.name если не указано
    'welcomeMessage' => null, // Будет взято из chatbot.ui_config.welcome_message
    'position' => null, // Будет взято из chatbot.ui_config.position
    'color' => null, // Будет взято из chatbot.ui_config.primary_color
    'enabled' => true,
    'chatbotSlug' => null, // Slug чатбота для использования (null = default bot)
    'showAvatar' => true,
    'avatarUrl' => null
])

@php
    // Загружаем чатбот если указан slug или используем дефолтный
    $chatbot = null;
    if ($enabled) {
        try {
            $chatbotClass = '\App\Models\Chatbot';
            if (class_exists($chatbotClass)) {
                if ($chatbotSlug) {
                    $chatbot = $chatbotClass::where('slug', $chatbotSlug)->where('is_active', true)->first();
                } else {
                    $chatbot = $chatbotClass::where('is_default', true)->where('is_active', true)->first();
                }
                
                // Если чатбот найден, используем его настройки
                if ($chatbot) {
                    $title = $title ?? $chatbot->name;
                    $welcomeMessage = $welcomeMessage ?? ($chatbot->ui_config['welcome_message'] ?? $welcomeMessage);
                    $position = $position ?? ($chatbot->ui_config['position'] ?? 'right');
                    $color = $color ?? ($chatbot->ui_config['primary_color'] ?? '#4f46e5');
                    $showAvatar = $showAvatar && ($chatbot->ui_config['show_avatar'] ?? true);
                    $avatarUrl = $avatarUrl ?? ($chatbot->ui_config['avatar_url'] ?? null);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Chatbot widget: Failed to load chatbot settings', ['error' => $e->getMessage()]);
        }
        
        // Fallback значения если чатбот не загружен
        $title = $title ?? 'Онлайн-консультант';
        $welcomeMessage = $welcomeMessage ?? 'Здравствуйте! Я AI-помощник. Спросите меня о ценах, услугах или условиях работы.';
        $position = $position ?? 'right';
        $color = $color ?? '#4f46e5';
    }
@endphp

@if($enabled)
<div id="ai-chat-widget" class="ai-chat-widget" data-position="{{ $position }}" data-chatbot-slug="{{ $chatbot?->slug ?? 'default' }}">
    <!-- Кнопка открытия чата -->
    <button id="ai-chat-toggle" class="ai-chat-toggle" style="background-color: {{ $color }};">
        <svg class="chat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <svg class="close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <!-- Окно чата -->
    <div id="ai-chat-window" class="ai-chat-window" style="display:none;">
        <!-- Заголовок -->
        <div class="ai-chat-header" style="background-color: {{ $color }};">
            <div class="ai-chat-title">
                @if($showAvatar)
                <div class="ai-avatar">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Avatar" class="ai-avatar-image">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1v-1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/>
                        </svg>
                    @endif
                </div>
                @endif
                <div>
                    <div class="ai-chat-name">{{ $title }}</div>
                    <div class="ai-chat-status">
                        <span class="status-dot"></span> Онлайн
                    </div>
                </div>
            </div>
            <button id="ai-chat-minimize" class="ai-chat-minimize">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>

        <!-- Сообщения -->
        <div id="ai-chat-messages" class="ai-chat-messages">
            <div class="ai-message ai-message-bot">
                <div class="ai-message-content">{{ $welcomeMessage }}</div>
            </div>
        </div>

        <!-- Индикатор набора -->
        <div id="ai-typing-indicator" class="ai-typing-indicator" style="display:none;">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>

        <!-- Поле ввода -->
        <div class="ai-chat-input-container">
            <input 
                type="text" 
                id="ai-chat-input" 
                class="ai-chat-input" 
                placeholder="Введите ваш вопрос..."
                autocomplete="off"
            >
            <button id="ai-chat-send" class="ai-chat-send" style="background-color: {{ $color }};">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>

        <!-- Источники -->
        <div id="ai-sources-panel" class="ai-sources-panel" style="display:none;">
            <div class="sources-title">Источники:</div>
            <div id="ai-sources-list"></div>
        </div>
    </div>
</div>

<style>
.ai-chat-widget {
    position: fixed;
    z-index: 999999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.ai-chat-widget[data-position="right"] {
    bottom: 20px;
    right: 20px;
}

.ai-chat-widget[data-position="left"] {
    bottom: 20px;
    left: 20px;
}

/* Кнопка */
.ai-chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.ai-chat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.ai-chat-toggle svg {
    width: 28px;
    height: 28px;
    color: white;
}

/* Окно чата */
.ai-chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 550px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 40px rgba(0,0,0,0.16);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Заголовок */
.ai-chat-header {
    padding: 16px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-chat-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-avatar svg {
    width: 24px;
    height: 24px;
}

.ai-chat-name {
    font-weight: 600;
    font-size: 15px;
}

.ai-chat-status {
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #4ade80;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.ai-chat-minimize {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 4px;
}

.ai-chat-minimize svg {
    width: 20px;
    height: 20px;
}

/* Сообщения */
.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f9fafb;
}

.ai-message {
    margin-bottom: 12px;
    max-width: 85%;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.ai-message-bot {
    margin-right: auto;
}

.ai-message-user {
    margin-left: auto;
}

.ai-message-content {
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.5;
}

.ai-message-bot .ai-message-content {
    background: white;
    border: 1px solid #e5e7eb;
    border-bottom-left-radius: 4px;
}

.ai-message-user .ai-message-content {
    background: {{ $color }};
    color: white;
    border-bottom-right-radius: 4px;
}

/* Индикатор набора */
.ai-typing-indicator {
    padding: 12px 16px;
    display: flex;
    gap: 4px;
}

.typing-dot {
    width: 8px;
    height: 8px;
    background: #9ca3af;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite;
}

.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-4px); }
}

/* Поле ввода */
.ai-chat-input-container {
    padding: 12px 16px;
    background: white;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 8px;
}

.ai-chat-input {
    flex: 1;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 10px 16px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.ai-chat-input:focus {
    border-color: {{ $color }};
}

.ai-chat-send {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}

.ai-chat-send:hover {
    transform: scale(1.05);
}

.ai-chat-send svg {
    width: 18px;
    height: 18px;
    color: white;
}

/* Источники */
.ai-sources-panel {
    padding: 12px 16px;
    background: #f3f4f6;
    border-top: 1px solid #e5e7eb;
    font-size: 12px;
}

.sources-title {
    font-weight: 600;
    margin-bottom: 8px;
    color: #6b7280;
}

#ai-sources-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.source-badge {
    background: white;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
    font-size: 11px;
}

/* Адаптивность для мобильных */
@media (max-width: 480px) {
    .ai-chat-window {
        width: calc(100vw - 40px);
        height: 70vh;
        right: 0;
        left: 0;
        margin: 0 20px;
    }
}

/* Стили для форм в чате */
.ai-chat-form {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px;
    margin: 8px 0;
}

.form-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 12px;
    color: #374151;
}

.ai-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-field label {
    font-size: 12px;
    font-weight: 500;
    color: #4b5563;
}

.form-field input,
.form-field select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.form-field input:focus,
.form-field select:focus {
    border-color: {{ $color }};
}

.ai-form-submit {
    background: {{ $color }};
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: opacity 0.2s;
    margin-top: 4px;
}

.ai-form-submit:hover {
    opacity: 0.9;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const widget = document.getElementById('ai-chat-widget');
    const toggleBtn = document.getElementById('ai-chat-toggle');
    const chatWindow = document.getElementById('ai-chat-window');
    const minimizeBtn = document.getElementById('ai-chat-minimize');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const messagesContainer = document.getElementById('ai-chat-messages');
    const typingIndicator = document.getElementById('ai-typing-indicator');
    const sourcesPanel = document.getElementById('ai-sources-panel');
    const sourcesList = document.getElementById('ai-sources-list');

    let sessionId = localStorage.getItem('ai_chat_session_id') || null;
    let isOpen = false;

    // Инициализация сессии
    async function initSession() {
        if (!sessionId) {
            try {
                const response = await fetch('/api/ai/chat/session', { method: 'POST' });
                const data = await response.json();
                if (data.success) {
                    sessionId = data.session_id;
                    localStorage.setItem('ai_chat_session_id', sessionId);
                }
            } catch (e) {
                console.error('Ошибка инициализации сессии:', e);
            }
        }
    }

    // Открытие/закрытие чата
    toggleBtn.addEventListener('click', () => {
        isOpen = !isOpen;
        chatWindow.style.display = isOpen ? 'flex' : 'none';
        toggleBtn.querySelector('.chat-icon').style.display = isOpen ? 'none' : 'block';
        toggleBtn.querySelector('.close-icon').style.display = isOpen ? 'block' : 'none';
        
        if (isOpen) {
            setTimeout(() => input.focus(), 300);
            initSession();
        }
    });

    // Свернуть чат
    minimizeBtn.addEventListener('click', () => {
        isOpen = false;
        chatWindow.style.display = 'none';
        toggleBtn.querySelector('.chat-icon').style.display = 'block';
        toggleBtn.querySelector('.close-icon').style.display = 'none';
    });

    // Отправка сообщения
    async function sendMessage() {
        const message = input.value.trim();
        if (!message) return;

        // Добавляем сообщение пользователя
        addMessage(message, 'user');
        input.value = '';

        // Показываем индикатор набора
        typingIndicator.style.display = 'flex';
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        try {
            // Собираем контекст страницы
            const pageContext = {
                uri: window.location.pathname,
                title: document.title,
                excerpt: document.querySelector('meta[name="description"]')?.content || null,
            };

            const response = await fetch('/api/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Page-Uri': pageContext.uri,
                    'X-Page-Title': pageContext.title,
                    'X-Page-Excerpt': pageContext.excerpt || '',
                },
                body: JSON.stringify({
                    message: message,
                    session_id: sessionId,
                    chatbot_slug: widget.dataset.chatbotSlug,
                    page_context: pageContext,
                })
            });

            const data = await response.json();

            // Скрываем индикатор
            typingIndicator.style.display = 'none';

            if (data.success) {
                addMessage(data.answer, 'bot');
                
                // Показываем источники если есть
                if (data.sources && data.sources.length > 0) {
                    showSources(data.sources);
                }
                
                // Если есть форма для отображения
                if (data.form_schema) {
                    renderForm(data.form_schema);
                }
            } else {
                addMessage('Извините, произошла ошибка. Попробуйте позже.', 'bot');
            }

        } catch (e) {
            typingIndicator.style.display = 'none';
            addMessage('Ошибка соединения. Проверьте интернет.', 'bot');
        }
    }

    // Рендеринг формы в чате
    function renderForm(formSchema) {
        const formDiv = document.createElement('div');
        formDiv.className = 'ai-chat-form';
        formDiv.innerHTML = `
            <div class="form-title">${formSchema.title || 'Заполните форму'}</div>
            <form class="ai-form"></form>
        `;
        
        const form = formDiv.querySelector('form');
        
        formSchema.fields?.forEach(field => {
            const fieldWrapper = document.createElement('div');
            fieldWrapper.className = 'form-field';
            
            const label = document.createElement('label');
            label.textContent = field.label;
            label.htmlFor = `field_${field.name}`;
            
            let input;
            if (field.type === 'select') {
                input = document.createElement('select');
                input.id = `field_${field.name}`;
                input.name = field.name;
                input.required = field.required || false;
                
                field.options?.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option;
                    opt.textContent = option;
                    input.appendChild(opt);
                });
            } else {
                input = document.createElement('input');
                input.type = field.type || 'text';
                input.id = `field_${field.name}`;
                input.name = field.name;
                input.required = field.required || false;
                input.placeholder = field.placeholder || '';
            }
            
            fieldWrapper.appendChild(label);
            fieldWrapper.appendChild(input);
            form.appendChild(fieldWrapper);
        });
        
        const submitBtn = document.createElement('button');
        submitBtn.type = 'submit';
        submitBtn.className = 'ai-form-submit';
        submitBtn.textContent = formSchema.submit_text || 'Отправить';
        form.appendChild(submitBtn);
        
        // Обработчик отправки формы
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            addMessage(`Форма заполнена: ${JSON.stringify(data)}`, 'user');
            typingIndicator.style.display = 'flex';
            
            // Отправляем данные формы через webhook
            try {
                const response = await fetch('/api/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        message: `Заполнена форма: ${JSON.stringify(data)}`,
                        session_id: sessionId,
                        chatbot_slug: widget.dataset.chatbotSlug,
                        form_data: data,
                    })
                });
                
                const result = await response.json();
                typingIndicator.style.display = 'none';
                
                if (result.success && result.answer) {
                    addMessage(result.answer, 'bot');
                }
            } catch (e) {
                typingIndicator.style.display = 'none';
                addMessage('Ошибка при отправке формы.', 'bot');
            }
        });
        
        messagesContainer.appendChild(formDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Добавление сообщения в чат
    function addMessage(content, role) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-message ai-message-${role}`;
        msgDiv.innerHTML = `<div class="ai-message-content">${escapeHtml(content)}</div>`;
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Показ источников
    function showSources(sources) {
        sourcesList.innerHTML = sources.map(s => 
            `<span class="source-badge" title="${escapeHtml(s.content_preview)}">
                ${s.similarity}% — ${escapeHtml(s.title)}
            </span>`
        ).join('');
        sourcesPanel.style.display = 'block';
    }

    // Экранирование HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Обработчики событий
    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // Инициализация при загрузке
    initSession();
});
</script>
@endif
