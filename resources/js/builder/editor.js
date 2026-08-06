/**
 * Page Builder Editor - Drag & Drop Interface
 * Advanced visual editor with nested blocks support
 */

class PageBuilderEditor {
    constructor(options = {}) {
        this.options = {
            availableBlocks: options.availableBlocks || {},
            onSave: options.onSave || (() => {}),
            onPreview: options.onPreview || (() => {}),
            ...options
        };
        
        this.content = [];
        this.selectedIndex = null;
        this.draggedIndex = null;
        this.dragOverIndex = null;
        this.nestedBlocks = new Map(); // Store nested block structures
        this.blockIdCounter = 0;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.renderBlockLibrary();
        this.loadAvailableBlocks();
        console.log('Page Builder Editor initialized');
    }

    bindEvents() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));
        
        // Click outside to deselect
        document.addEventListener('click', (e) => {
            if (!e.target.closest('[data-block-item]') && 
                !e.target.closest('[data-settings-panel]')) {
                this.deselectBlock();
            }
        });

        // Auto-save warning
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    loadAvailableBlocks() {
        // Load from window config or fetch from API
        const blocksConfig = window.builderBlocks || this.options.availableBlocks;
        this.availableBlocks = blocksConfig;
        this.renderBlockLibrary();
    }

    generateBlockId() {
        return `block_${Date.now()}_${++this.blockIdCounter}`;
    }

    addBlock(type, targetIndex = null) {
        const blockConfig = this.availableBlocks[type];
        if (!blockConfig) {
            console.error(`Block type "${type}" not found`);
            return;
        }

        const newBlock = JSON.parse(JSON.stringify(blockConfig.default));
        newBlock._id = this.generateBlockId();
        newBlock._type = type;
        newBlock._createdAt = Date.now();

        if (targetIndex !== null) {
            this.content.splice(targetIndex, 0, newBlock);
        } else {
            this.content.push(newBlock);
        }

        this.selectBlock(this.content.length - 1);
        this.renderCanvas();
        this.markAsChanged();
    }

    deleteBlock(index) {
        if (index < 0 || index >= this.content.length) return;
        
        const block = this.content[index];
        if (confirm(`Удалить блок "${block._type}"?`)) {
            this.content.splice(index, 1);
            if (this.selectedIndex === index) {
                this.deselectBlock();
            } else if (this.selectedIndex > index) {
                this.selectedIndex--;
            }
            this.renderCanvas();
            this.markAsChanged();
        }
    }

    duplicateBlock(index) {
        if (index < 0 || index >= this.content.length) return;
        
        const original = this.content[index];
        const duplicate = JSON.parse(JSON.stringify(original));
        duplicate._id = this.generateBlockId();
        duplicate._duplicatedFrom = original._id;
        
        this.content.splice(index + 1, 0, duplicate);
        this.selectBlock(index + 1);
        this.renderCanvas();
        this.markAsChanged();
    }

    moveBlock(fromIndex, toIndex) {
        if (fromIndex < 0 || fromIndex >= this.content.length ||
            toIndex < 0 || toIndex >= this.content.length) return;

        const [block] = this.content.splice(fromIndex, 1);
        this.content.splice(toIndex, 0, block);
        this.selectedIndex = toIndex;
        this.renderCanvas();
        this.markAsChanged();
    }

    moveBlockUp(index) {
        if (index > 0) {
            this.moveBlock(index, index - 1);
        }
    }

    moveBlockDown(index) {
        if (index < this.content.length - 1) {
            this.moveBlock(index, index + 1);
        }
    }

    selectBlock(index) {
        if (index < 0 || index >= this.content.length) {
            this.deselectBlock();
            return;
        }
        
        this.selectedIndex = index;
        this.renderCanvas();
        this.renderSettingsPanel();
    }

    deselectBlock() {
        this.selectedIndex = null;
        this.renderCanvas();
        this.clearSettingsPanel();
    }

    updateBlockSetting(key, value) {
        if (this.selectedIndex === null) return;
        
        const block = this.content[this.selectedIndex];
        if (!block.settings) {
            block.settings = {};
        }
        
        // Support nested settings (e.g., "items.0.question")
        const keys = key.split('.');
        let obj = block.settings;
        
        for (let i = 0; i < keys.length - 1; i++) {
            const k = keys[i];
            if (!(k in obj)) {
                obj[k] = isNaN(keys[i + 1]) ? {} : [];
            }
            obj = obj[k];
        }
        
        obj[keys[keys.length - 1]] = value;
        this.renderCanvas();
        this.markAsChanged();
    }

    addNestedItem(path) {
        const block = this.content[this.selectedIndex];
        if (!block) return;

        const keys = path.split('.');
        let obj = block.settings;
        
        for (let i = 0; i < keys.length - 1; i++) {
            obj = obj[keys[i]];
        }
        
        const lastKey = keys[keys.length - 1];
        if (Array.isArray(obj[lastKey])) {
            obj[lastKey].push({});
        } else {
            obj[lastKey] = [{}];
        }
        
        this.renderSettingsPanel();
        this.renderCanvas();
        this.markAsChanged();
    }

    removeNestedItem(path, index) {
        const block = this.content[this.selectedIndex];
        if (!block) return;

        const keys = path.split('.');
        let obj = block.settings;
        
        for (let i = 0; i < keys.length - 1; i++) {
            obj = obj[keys[i]];
        }
        
        const lastKey = keys[keys.length - 1];
        if (Array.isArray(obj[lastKey])) {
            obj[lastKey].splice(index, 1);
        }
        
        this.renderSettingsPanel();
        this.renderCanvas();
        this.markAsChanged();
    }

    // Drag and Drop handlers
    handleDragStart(e, index) {
        this.draggedIndex = index;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', index);
        e.target.classList.add('dragging');
    }

    handleDragOver(e, index) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        this.dragOverIndex = index;
        this.updateDragVisuals();
    }

    handleDragLeave(e, index) {
        this.dragOverIndex = null;
        this.updateDragVisuals();
    }

    handleDrop(e, targetIndex) {
        e.preventDefault();
        const sourceIndex = parseInt(e.dataTransfer.getData('text/plain'));
        
        if (sourceIndex !== targetIndex && !isNaN(sourceIndex)) {
            this.moveBlock(sourceIndex, targetIndex);
        }
        
        this.draggedIndex = null;
        this.dragOverIndex = null;
        this.updateDragVisuals();
        document.querySelectorAll('.dragging').forEach(el => el.classList.remove('dragging'));
    }

    handleDragEnd(e) {
        this.draggedIndex = null;
        this.dragOverIndex = null;
        this.updateDragVisuals();
        e.target.classList.remove('dragging');
    }

    updateDragVisuals() {
        document.querySelectorAll('[data-block-index]').forEach((el, idx) => {
            if (idx === this.dragOverIndex && this.draggedIndex !== this.dragOverIndex) {
                el.classList.add('drag-over');
            } else {
                el.classList.remove('drag-over');
            }
        });
    }

    handleKeyboard(e) {
        if (this.selectedIndex === null) return;

        switch(e.key) {
            case 'Delete':
            case 'Backspace':
                if (!e.target.matches('input, textarea, select')) {
                    e.preventDefault();
                    this.deleteBlock(this.selectedIndex);
                }
                break;
            case 'ArrowUp':
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this.moveBlockUp(this.selectedIndex);
                }
                break;
            case 'ArrowDown':
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this.moveBlockDown(this.selectedIndex);
                }
                break;
            case 'Escape':
                this.deselectBlock();
                break;
        }
    }

    // Rendering methods
    renderBlockLibrary() {
        const container = document.querySelector('[data-block-library]');
        if (!container) return;

        // Group by category
        const categories = {};
        Object.entries(this.availableBlocks).forEach(([type, config]) => {
            const category = config.category || 'other';
            if (!categories[category]) {
                categories[category] = [];
            }
            categories[category].push({ type, ...config });
        });

        let html = '';
        Object.entries(categories).forEach(([category, blocks]) => {
            html += `<div class="mb-4">`;
            html += `<h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">${this.getCategoryName(category)}</h4>`;
            html += `<div class="grid grid-cols-2 gap-2">`;
            
            blocks.forEach(block => {
                html += `
                    <div 
                        data-block-type="${block.type}"
                        draggable="true"
                        @dragstart="editor.handleDragStartFromLibrary(event, '${block.type}')"
                        @click="editor.addBlock('${block.type}')"
                        class="p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-400 hover:shadow-md transition-all group"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-lg">${block.icon || '📦'}</span>
                            <span class="text-sm font-medium">${block.name}</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1 truncate">${block.description || ''}</div>
                    </div>
                `;
            });
            
            html += `</div></div>`;
        });

        container.innerHTML = html;
    }

    getCategoryName(category) {
        const names = {
            content: 'Контент',
            media: 'Медиа',
            layout: 'Макет',
            dynamic: 'Динамические',
            interactive: 'Интерактивные',
            ecommerce: 'E-commerce',
            utility: 'Утилиты',
            seo: 'SEO'
        };
        return names[category] || category;
    }

    handleDragStartFromLibrary(e, type) {
        e.dataTransfer.setData('application/x-block-type', type);
        e.dataTransfer.effectAllowed = 'copy';
    }

    renderCanvas() {
        const container = document.querySelector('[data-canvas]');
        if (!container) return;

        if (this.content.length === 0) {
            container.innerHTML = `
                <div class="text-center py-20 bg-white rounded-lg border-2 border-dashed border-slate-300">
                    <div class="text-4xl mb-4">📦</div>
                    <p class="text-slate-400 text-lg">Перетащите блок из библиотеки или нажмите на него</p>
                    <p class="text-slate-300 text-sm mt-2">Для начала создания страницы</p>
                </div>
            `;
            this.setupCanvasDropZone();
            return;
        }

        let html = '<div class="space-y-4">';
        
        this.content.forEach((block, index) => {
            const isSelected = index === this.selectedIndex;
            const blockConfig = this.availableBlocks[block._type] || this.availableBlocks[block.type];
            
            html += `
                <div 
                    data-block-index="${index}"
                    data-block-item
                    draggable="true"
                    @dragstart="editor.handleDragStart(event, ${index})"
                    @dragover="editor.handleDragOver(event, ${index})"
                    @dragleave="editor.handleDragLeave(event, ${index})"
                    @drop="editor.handleDrop(event, ${index})"
                    @dragend="editor.handleDragEnd(event)"
                    @click="editor.selectBlock(${index})"
                    class="relative bg-white rounded-lg border-2 transition-all cursor-move group"
                    :class="{
                        'border-slate-200 hover:border-slate-300': ${!isSelected},
                        'border-blue-500 shadow-lg ring-2 ring-blue-100': ${isSelected},
                        'dragging': ${index === this.draggedIndex},
                        'drag-over': ${index === this.dragOverIndex}
                    }"
                >
                    <!-- Block controls -->
                    ${isSelected ? `
                        <div class="absolute -top-3 right-4 z-10 flex gap-1 bg-slate-900 rounded-md shadow-lg overflow-hidden">
                            <button 
                                @click.stop="editor.moveBlockUp(${index})"
                                class="p-2 text-white hover:bg-slate-700 transition-colors"
                                title="Вверх (Ctrl+↑)"
                            >
                                ↑
                            </button>
                            <button 
                                @click.stop="editor.moveBlockDown(${index})"
                                class="p-2 text-white hover:bg-slate-700 transition-colors"
                                title="Вниз (Ctrl+↓)"
                            >
                                ↓
                            </button>
                            <button 
                                @click.stop="editor.duplicateBlock(${index})"
                                class="p-2 text-white hover:bg-slate-700 transition-colors"
                                title="Дублировать"
                            >
                                ⧉
                            </button>
                            <button 
                                @click.stop="editor.deleteBlock(${index})"
                                class="p-2 text-red-400 hover:bg-red-600 hover:text-white transition-colors"
                                title="Удалить (Del)"
                            >
                                ✕
                            </button>
                        </div>
                    ` : ''}
                    
                    <!-- Block type badge -->
                    <div class="absolute top-2 left-2 px-2 py-1 bg-slate-100 rounded text-xs text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity">
                        ${blockConfig?.name || block.type}
                    </div>
                    
                    <!-- Block preview -->
                    <div class="p-4 pt-8">
                        ${this.renderBlockPreview(block)}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
        this.setupCanvasDropZone();
    }

    setupCanvasDropZone() {
        const container = document.querySelector('[data-canvas]');
        if (!container) return;

        container.addEventListener('dragover', (e) => {
            e.preventDefault();
            const blockType = e.dataTransfer.getData('application/x-block-type');
            if (blockType) {
                e.dataTransfer.dropEffect = 'copy';
                container.classList.add('drop-active');
            }
        });

        container.addEventListener('dragleave', (e) => {
            if (e.target === container) {
                container.classList.remove('drop-active');
            }
        });

        container.addEventListener('drop', (e) => {
            e.preventDefault();
            container.classList.remove('drop-active');
            const blockType = e.dataTransfer.getData('application/x-block-type');
            if (blockType && this.availableBlocks[blockType]) {
                this.addBlock(blockType);
            }
        });
    }

    renderBlockPreview(block) {
        const type = block._type || block.type;
        const settings = block.settings || block;

        switch(type) {
            case 'heading':
                const level = settings.level || 'h2';
                return `<${level} style="color: ${settings.color || '#111827'}; text-align: ${settings.align || 'left'}; font-size: ${settings.font_size || '1.5rem'};">
                    ${settings.text || 'Заголовок'}
                </${level}>`;
            
            case 'text':
                return `<div style="color: ${settings.color || '#4b5563'}; text-align: ${settings.align || 'left'}; font-size: ${settings.font_size || '1rem'};">
                    ${(settings.content || settings.text || 'Текстовый блок...').split('\n').map(p => `<p>${p}</p>`).join('')}
                </div>`;
            
            case 'button':
                const btnStyles = {
                    primary: 'bg-blue-600 text-white',
                    secondary: 'bg-slate-600 text-white',
                    outline: 'border-2 border-blue-600 text-blue-600',
                    ghost: 'text-blue-600'
                };
                const sizeClasses = { sm: 'px-3 py-1.5 text-sm', md: 'px-4 py-2', lg: 'px-6 py-3 text-lg' };
                return `<a href="${settings.url || '#'}" target="${settings.target || '_self'}" 
                    class="inline-block ${btnStyles[settings.style || 'primary']} ${sizeClasses[settings.size || 'md']} rounded-md transition-colors">
                    ${settings.icon || ''}${settings.text || 'Кнопка'}
                </a>`;
            
            case 'image':
                return `<div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center bg-slate-50">
                    <div class="text-4xl mb-2">🖼️</div>
                    <div class="text-slate-400">${settings.alt || 'Изображение'}</div>
                </div>`;
            
            case 'divider':
                return `<hr class="my-4 border-t-2 border-slate-200">`;
            
            case 'spacer':
                const height = settings.height || '40px';
                return `<div class="bg-slate-100 border border-dashed border-slate-300 rounded text-center text-xs text-slate-400 py-2">
                    Распорка: ${height}
                </div>`;
            
            case 'accordion':
                const items = settings.items || [];
                return `<div class="space-y-2">
                    ${items.slice(0, 3).map((item, i) => `
                        <div class="border border-slate-200 rounded p-3">
                            <div class="font-medium">${item.question || `Вопрос ${i+1}`}</div>
                        </div>
                    `).join('')}
                    ${items.length > 3 ? `<div class="text-xs text-slate-400">+ ещё ${items.length - 3}</div>` : ''}
                </div>`;
            
            case 'tabs':
                const tabs = settings.tabs || [];
                return `<div class="border border-slate-200 rounded">
                    <div class="flex border-b border-slate-200">
                        ${tabs.slice(0, 4).map((tab, i) => `
                            <div class="px-4 py-2 text-sm border-r border-slate-200 bg-slate-50">${tab.title || `Вкладка ${i+1}`}</div>
                        `).join('')}
                    </div>
                </div>`;
            
            case 'counter':
                return `<div class="text-center p-4">
                    <div class="text-3xl font-bold text-blue-600">${settings.end_value || '100'}</div>
                    <div class="text-sm text-slate-500">${settings.label || 'Счетчик'}</div>
                </div>`;
            
            case 'pricing-table':
                return `<div class="border border-slate-200 rounded-lg p-4 text-center">
                    <div class="text-lg font-semibold">${settings.title || 'Тариф'}</div>
                    <div class="text-2xl font-bold text-blue-600 my-2">${settings.price || '$0'}</div>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Выбрать</button>
                </div>`;
            
            case 'testimonials':
                const testimonials = settings.testimonials || [];
                return `<div class="space-y-2">
                    ${testimonials.slice(0, 2).map((t, i) => `
                        <div class="border border-slate-200 rounded p-3 bg-slate-50">
                            <div class="text-sm italic">"${t.text?.substring(0, 50) || 'Отзыв...'}${t.text?.length > 50 ? '...' : ''}"</div>
                            <div class="text-xs text-slate-500 mt-1">— ${t.author || 'Автор'}</div>
                        </div>
                    `).join('')}
                </div>`;
            
            case 'form':
                return `<div class="space-y-3">
                    <div class="h-10 border border-slate-300 rounded bg-slate-50"></div>
                    <div class="h-10 border border-slate-300 rounded bg-slate-50"></div>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Отправить</button>
                </div>`;
            
            case 'alert':
                const alertStyles = {
                    info: 'bg-blue-50 border-blue-200 text-blue-800',
                    success: 'bg-green-50 border-green-200 text-green-800',
                    warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
                    error: 'bg-red-50 border-red-200 text-red-800'
                };
                return `<div class="p-4 rounded border ${alertStyles[settings.variant || 'info']}">
                    <strong>${settings.title || 'Уведомление'}</strong>
                    <div class="text-sm mt-1">${settings.message || 'Текст уведомления'}</div>
                </div>`;
            
            default:
                return `<div class="text-center py-8 text-slate-400">
                    <div class="text-2xl mb-2">📦</div>
                    <div>${type}</div>
                </div>`;
        }
    }

    renderSettingsPanel() {
        const container = document.querySelector('[data-settings-panel]');
        if (!container || this.selectedIndex === null) return;

        const block = this.content[this.selectedIndex];
        const blockConfig = this.availableBlocks[block._type] || this.availableBlocks[block.type];
        const settings = block.settings || block;

        if (!blockConfig) {
            container.innerHTML = '<div class="p-4 text-slate-400">Конфигурация блока не найдена</div>';
            return;
        }

        let html = `
            <div class="p-4 border-b border-slate-200">
                <h3 class="font-semibold text-lg">${blockConfig.name}</h3>
                <p class="text-xs text-slate-500 mt-1">${blockConfig.description || ''}</p>
            </div>
            <div class="p-4 space-y-4">
        `;

        // Render fields based on block config
        if (blockConfig.fields) {
            Object.entries(blockConfig.fields).forEach(([fieldKey, fieldConfig]) => {
                html += this.renderField(fieldKey, fieldConfig, settings[fieldKey]);
            });
        }

        // Common settings section
        html += `
            <div class="pt-4 border-t border-slate-200">
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Общие настройки</h4>
        `;
        
        html += this.renderField('css_class', {
            type: 'text',
            label: 'CSS класс',
            placeholder: 'custom-class'
        }, settings.css_class);

        html += this.renderField('background_color', {
            type: 'color',
            label: 'Цвет фона'
        }, settings.background_color);

        html += `</div>`;
        html += `</div>`;

        container.innerHTML = html;
        this.bindSettingsEvents();
    }

    renderField(key, config, value) {
        const currentValue = value !== undefined ? value : config.default;
        const required = config.required ? 'required' : '';
        const placeholder = config.placeholder ? `placeholder="${config.placeholder}"` : '';

        let input = '';
        
        switch(config.type) {
            case 'text':
                input = `<input type="text" name="${key}" value="${currentValue || ''}" ${placeholder} ${required} 
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">`;
                break;
            
            case 'textarea':
                const rows = config.rows || 4;
                input = `<textarea name="${key}" rows="${rows}" ${required} 
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">${currentValue || ''}</textarea>`;
                break;
            
            case 'select':
                const options = Object.entries(config.options || {}).map(([optValue, optLabel]) => 
                    `<option value="${optValue}" ${currentValue === optValue ? 'selected' : ''}>${optLabel}</option>`
                ).join('');
                input = `<select name="${key}" ${required} 
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">${options}</select>`;
                break;
            
            case 'color':
                input = `<div class="flex items-center gap-2">
                    <input type="color" name="${key}" value="${currentValue || '#000000'}" 
                        class="w-10 h-10 rounded border border-slate-300 cursor-pointer">
                    <input type="text" value="${currentValue || '#000000'}" 
                        class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm font-mono">
                </div>`;
                break;
            
            case 'checkbox':
                input = `<label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="${key}" ${currentValue ? 'checked' : ''} 
                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm">${config.label}</span>
                </label>`;
                break;
            
            case 'number':
                input = `<input type="number" name="${key}" value="${currentValue || ''}" step="${config.step || 1}" 
                    min="${config.min ?? ''}" max="${config.max ?? ''}" ${required}
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">`;
                break;
            
            case 'repeater':
                input = this.renderRepeaterField(key, config, currentValue);
                break;
            
            case 'media':
                input = `<div class="flex items-center gap-2">
                    <button type="button" onclick="editor.openMediaPicker('${key}')" 
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 rounded-md text-sm">
                        📁 Выбрать медиа
                    </button>
                    <span class="text-sm text-slate-500 truncate flex-1">${currentValue || 'Не выбрано'}</span>
                </div>`;
                break;
            
            default:
                input = `<input type="text" name="${key}" value="${currentValue || ''}" ${placeholder} 
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">`;
        }

        return `
            <div>
                <label class="block text-sm text-slate-600 mb-1">${config.label}${config.required ? ' <span class="text-red-500">*</span>' : ''}</label>
                ${input}
            </div>
        `;
    }

    renderRepeaterField(key, config, values) {
        const items = Array.isArray(values) ? values : [];
        const itemFields = config.fields || {};
        
        let html = `<div class="space-y-2">`;
        
        items.forEach((item, index) => {
            html += `<div class="border border-slate-200 rounded-lg p-3 bg-slate-50 relative group">`;
            html += `<button type="button" onclick="editor.removeNestedItem('${key}', ${index})" 
                class="absolute top-2 right-2 p-1 text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">✕</button>`;
            
            Object.entries(itemFields).forEach(([fieldKey, fieldConfig]) => {
                const fieldValue = item[fieldKey] || '';
                html += `<div class="mb-2">`;
                html += `<label class="block text-xs text-slate-500 mb-1">${fieldConfig.label}</label>`;
                
                if (fieldConfig.type === 'textarea') {
                    html += `<textarea name="${key}[${index}][${fieldKey}]" rows="2" 
                        class="w-full rounded border border-slate-300 px-2 py-1 text-xs">${fieldValue}</textarea>`;
                } else {
                    html += `<input type="${fieldConfig.type || 'text'}" name="${key}[${index}][${fieldKey}]" value="${fieldValue}" 
                        class="w-full rounded border border-slate-300 px-2 py-1 text-xs">`;
                }
                
                html += `</div>`;
            });
            
            html += `</div>`;
        });
        
        html += `<button type="button" onclick="editor.addNestedItem('${key}')" 
            class="w-full py-2 border-2 border-dashed border-slate-300 rounded-lg text-slate-400 hover:border-blue-400 hover:text-blue-500 transition-colors text-sm">
            + Добавить элемент
        </button>`;
        
        html += `</div>`;
        
        return html;
    }

    bindSettingsEvents() {
        const container = document.querySelector('[data-settings-panel]');
        if (!container) return;

        // Text inputs
        container.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(input => {
            input.addEventListener('input', (e) => {
                this.updateBlockSetting(e.target.name, e.target.value);
            });
        });

        // Selects
        container.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', (e) => {
                this.updateBlockSetting(e.target.name, e.target.value);
            });
        });

        // Color inputs
        container.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', (e) => {
                // Update text input next to color picker
                const textInput = input.parentElement.querySelector('input[type="text"]');
                if (textInput) {
                    textInput.value = e.target.value;
                }
                this.updateBlockSetting(e.target.name, e.target.value);
            });
        });

        // Checkboxes
        container.querySelectorAll('input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.updateBlockSetting(e.target.name, e.target.checked);
            });
        });
    }

    clearSettingsPanel() {
        const container = document.querySelector('[data-settings-panel]');
        if (!container) return;
        
        container.innerHTML = `
            <div class="p-8 text-center text-slate-400">
                <div class="text-4xl mb-4">👆</div>
                <p>Выберите блок для редактирования</p>
            </div>
        `;
    }

    openMediaPicker(fieldKey) {
        // Placeholder for media picker integration
        alert('Media Picker: Интеграция с медиа-библиотекой\nПоле: ' + fieldKey);
        // In real implementation, this would open a modal with media library
    }

    getContent() {
        return this.content;
    }

    setContent(content) {
        this.content = content || [];
        this.deselectBlock();
        this.renderCanvas();
    }

    saveContent() {
        return this.options.onSave(this.content);
    }

    previewContent() {
        return this.options.onPreview(this.content);
    }

    hasUnsavedChanges() {
        // Implement change tracking logic
        return false;
    }

    markAsChanged() {
        // Mark as having unsaved changes
        const saveBtn = document.querySelector('[data-save-button]');
        if (saveBtn) {
            saveBtn.classList.add('has-changes');
        }
    }

    exportJSON() {
        return JSON.stringify(this.content, null, 2);
    }

    importJSON(jsonString) {
        try {
            const content = JSON.parse(jsonString);
            this.setContent(content);
            return true;
        } catch (e) {
            console.error('Invalid JSON:', e);
            return false;
        }
    }

    clearAll() {
        if (confirm('Вы уверены, что хотите удалить все блоки?')) {
            this.content = [];
            this.deselectBlock();
            this.renderCanvas();
            this.markAsChanged();
        }
    }
}

// Initialize editor when DOM is ready
let editor;

document.addEventListener('DOMContentLoaded', () => {
    const builderContainer = document.getElementById('page-builder');
    if (builderContainer) {
        editor = new PageBuilderEditor({
            availableBlocks: window.builderBlocks || {},
            onSave: async (content) => {
                console.log('Saving content:', content);
                // Implement save logic
            },
            onPreview: (content) => {
                console.log('Preview content:', content);
                // Implement preview logic
            }
        });
        
        // Expose to window for blade template access
        window.pageBuilderEditor = editor;
    }
});

export { PageBuilderEditor };
export default PageBuilderEditor;
