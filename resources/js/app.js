import { createApp, ref, reactive, computed, onMounted } from 'vue';

// Expose Vue composition API globally for inline scripts (e.g., page builder)
window.Vue = { createApp, ref, reactive, computed, onMounted };

// Admin UI utilities (theme toggle, sidebar)
import './admin/app';
import { mountBuilderPrototype } from './components/builder/mountPrototype';
import { mountAdvancedBuilder } from './admin/builder/mountAdvancedBuilder';
import { mountDesignLibrary } from './admin/builder/mountDesignLibrary';
import { mountFormBuilder } from './admin/forms/mountFormBuilder';
import { mountMediaManager } from './admin/media/mountMediaManager';
import { mountMediaPicker, unmountMediaPicker } from './admin/media/mountMediaPicker';

if (typeof window !== 'undefined') {
    window.Vertex = window.Vertex || {};
    window.Vertex.mountBuilderPrototype = mountBuilderPrototype;
    window.Vertex.mountAdvancedBuilder = mountAdvancedBuilder;
    window.Vertex.mountDesignLibrary = mountDesignLibrary;
    window.Vertex.mountFormBuilder = mountFormBuilder;
    window.Vertex.mountMediaManager = mountMediaManager;
    window.Vertex.mountMediaPicker = mountMediaPicker;
    window.Vertex.unmountMediaPicker = unmountMediaPicker;
}

function mountFrontendGalleries(root = document) {
    const galleries = [...root.querySelectorAll('[data-vc-gallery]')];

    galleries.forEach((gallery) => {
        if (gallery.dataset.vcGalleryMounted === 'true') return;
        gallery.dataset.vcGalleryMounted = 'true';

        const track = gallery.querySelector('.vc-gallery-track');
        const slides = [...gallery.querySelectorAll('.vc-gallery-item')];
        const dots = [...gallery.querySelectorAll('[data-vc-gallery-dot]')];
        const isSlider = ['slider', 'carousel'].includes(gallery.dataset.layout || '');

        if (!track || !isSlider || slides.length <= 1) return;

        const scrollToIndex = (index) => {
            const slide = slides[Math.max(0, Math.min(index, slides.length - 1))];
            slide?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
        };

        const activeIndex = () => {
            const trackLeft = track.getBoundingClientRect().left;
            return slides.reduce((closestIndex, slide, index) => {
                const distance = Math.abs(slide.getBoundingClientRect().left - trackLeft);
                const closestDistance = Math.abs(slides[closestIndex].getBoundingClientRect().left - trackLeft);
                return distance < closestDistance ? index : closestIndex;
            }, 0);
        };

        const syncDots = () => {
            const index = activeIndex();
            dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === index));
        };

        gallery.querySelector('[data-vc-gallery-prev]')?.addEventListener('click', () => scrollToIndex(activeIndex() - 1));
        gallery.querySelector('[data-vc-gallery-next]')?.addEventListener('click', () => scrollToIndex(activeIndex() + 1));
        dots.forEach((dot) => dot.addEventListener('click', () => scrollToIndex(Number(dot.dataset.vcGalleryDot || 0))));
        track.addEventListener('scroll', () => window.requestAnimationFrame(syncDots), { passive: true });
        syncDots();

        if (gallery.dataset.autoplay === 'true') {
            const interval = Math.max(1000, Number(gallery.dataset.interval || 5000));
            window.setInterval(() => scrollToIndex((activeIndex() + 1) % slides.length), interval);
        }
    });
}

function mountFrontendLightbox(root = document) {
    const links = [...root.querySelectorAll('[data-vc-lightbox]')];
    if (!links.length) return;

    const groupedLinks = (group) => links.filter((link) => (link.dataset.vcLightboxGroup || '') === group);
    let overlay = null;
    let currentGroup = [];
    let currentIndex = 0;

    const close = () => {
        overlay?.remove();
        overlay = null;
        currentGroup = [];
        currentIndex = 0;
        document.documentElement.style.overflow = '';
    };

    const render = () => {
        const link = currentGroup[currentIndex];
        if (!overlay || !link) return;

        const image = overlay.querySelector('img');
        const caption = overlay.querySelector('.vc-lightbox-caption');
        image.src = link.href;
        image.alt = link.querySelector('img')?.alt || '';
        caption.textContent = link.dataset.vcLightboxCaption || image.alt || '';
        caption.hidden = caption.textContent === '';
    };

    const move = (direction) => {
        if (!currentGroup.length) return;
        currentIndex = (currentIndex + direction + currentGroup.length) % currentGroup.length;
        render();
    };

    const open = (link) => {
        currentGroup = groupedLinks(link.dataset.vcLightboxGroup || '');
        currentIndex = Math.max(0, currentGroup.indexOf(link));
        overlay = document.createElement('div');
        overlay.className = 'vc-lightbox-overlay';
        overlay.innerHTML = `
            <div class="vc-lightbox-dialog" role="dialog" aria-modal="true">
                <button class="vc-lightbox-close" type="button" aria-label="Close lightbox">×</button>
                <button class="vc-lightbox-control vc-lightbox-prev" type="button" aria-label="Previous image">‹</button>
                <img src="" alt="">
                <button class="vc-lightbox-control vc-lightbox-next" type="button" aria-label="Next image">›</button>
                <div class="vc-lightbox-caption"></div>
            </div>
        `;
        document.body.appendChild(overlay);
        document.documentElement.style.overflow = 'hidden';
        overlay.querySelector('.vc-lightbox-close')?.addEventListener('click', close);
        overlay.querySelector('.vc-lightbox-prev')?.addEventListener('click', () => move(-1));
        overlay.querySelector('.vc-lightbox-next')?.addEventListener('click', () => move(1));
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close();
        });
        window.requestAnimationFrame(() => overlay?.classList.add('is-open'));
        render();
    };

    links.forEach((link) => {
        if (link.dataset.vcLightboxMounted === 'true') return;
        link.dataset.vcLightboxMounted = 'true';
        link.addEventListener('click', (event) => {
            event.preventDefault();
            open(link);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (!overlay) return;
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowLeft') move(-1);
        if (event.key === 'ArrowRight') move(1);
    });
}

function mountFrontendBlocks(root = document) {
    // Tabs
    [...root.querySelectorAll('[data-vc-tabs]')].forEach((tabs) => {
        if (tabs.dataset.vcTabsMounted === 'true') return;
        tabs.dataset.vcTabsMounted = 'true';

        const buttons = [...tabs.querySelectorAll('[data-vc-tab]')];
        const panels = [...tabs.querySelectorAll('[data-vc-tab-panel]')];

        const activate = (index) => {
            buttons.forEach((button) => {
                const isActive = Number(button.dataset.vcTab) === index;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            panels.forEach((panel) => {
                panel.classList.toggle('is-active', Number(panel.dataset.vcTabPanel) === index);
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => activate(Number(button.dataset.vcTab || 0)));
        });

        activate(0);
    });

    // Accordions that only allow one open panel at a time
    [...root.querySelectorAll('[data-vc-accordion]')].forEach((accordion) => {
        if (accordion.dataset.vcAccordionMounted === 'true') return;
        accordion.dataset.vcAccordionMounted = 'true';

        if (accordion.dataset.allowMultiple === 'true') return;

        const items = [...accordion.querySelectorAll('details')];
        items.forEach((item) => {
            item.addEventListener('toggle', () => {
                if (!item.open) return;
                items.forEach((other) => {
                    if (other !== item) other.open = false;
                });
            });
        });
    });

    // Dismissible alerts
    [...root.querySelectorAll('[data-vc-alert]')].forEach((alert) => {
        if (alert.dataset.vcAlertMounted === 'true') return;
        alert.dataset.vcAlertMounted = 'true';

        alert.querySelector('[data-vc-alert-close]')?.addEventListener('click', () => alert.remove());
    });

    // Native modal dialogs
    [...root.querySelectorAll('[data-vc-modal]')].forEach((modal) => {
        if (modal.dataset.vcModalMounted === 'true') return;
        modal.dataset.vcModalMounted = 'true';

        const opener = root.querySelector(`[data-vc-modal-open="${modal.id}"]`);
        opener?.addEventListener('click', () => {
            if (typeof modal.showModal === 'function') modal.showModal();
            else modal.setAttribute('open', '');
        });

        modal.querySelector('[data-vc-modal-close]')?.addEventListener('click', () => modal.close());
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.close();
        });
    });

    // Animated counters
    const counters = [...root.querySelectorAll('[data-vc-counter]')].filter(
        (counter) => counter.dataset.vcCounterMounted !== 'true'
    );

    if (!counters.length) return;

    const animate = (counter) => {
        const target = Number(counter.dataset.value || 0);
        const duration = Number(counter.dataset.duration || 0);
        const output = counter.querySelector('[data-vc-counter-value]');

        if (!output || !target || duration <= 0) return;

        const decimals = Number.isInteger(target) ? 0 : 2;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min(1, (now - start) / duration);
            output.textContent = (target * progress).toFixed(decimals);
            if (progress < 1) window.requestAnimationFrame(step);
            else output.textContent = target.toFixed(decimals);
        };

        window.requestAnimationFrame(step);
    };

    counters.forEach((counter) => {
        counter.dataset.vcCounterMounted = 'true';

        if (typeof IntersectionObserver !== 'function') {
            animate(counter);
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                animate(counter);
                observer.disconnect();
            });
        }, { threshold: 0.4 });

        observer.observe(counter);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    mountBuilderPrototype();
    mountAdvancedBuilder();
    mountDesignLibrary();
    mountFormBuilder();
    mountMediaManager();
    mountFrontendGalleries();
    mountFrontendLightbox();
    mountFrontendBlocks();
});

// Telegram Widget — lazy load only on frontend when element exists
if (typeof window !== 'undefined' && window.telegramWidgetConfig?.enabled) {
    import('./telegram-widget.js').catch(() => {
        // fail silently if module not found
    });
}

/**
 * Vertex CMS Core JavaScript
 * Handles Page Builder, Dashboards, and UI interactions
 */

document.addEventListener('alpine:init', () => {
    // --- Page Builder Logic ---
    Alpine.data('pageBuilder', () => ({
        blocks: [],
        selectedBlock: null,
        dragging: false,
        dragIndex: null,

        init() {
            // Load initial blocks from JSON input or default
            const jsonInput = document.getElementById('page-builder-data');
            if (jsonInput && jsonInput.value) {
                try {
                    this.blocks = JSON.parse(jsonInput.value);
                } catch (e) {
                    console.error('Invalid builder data', e);
                    this.blocks = [];
                }
            }

            // Setup drag and drop listeners
            this.setupDragAndDrop();
        },

        addBlock(type) {
            const newBlock = {
                id: 'block_' + Date.now(),
                type: type,
                content: {},
                settings: { class: '', style: '' }
            };

            // Default content based on type
            if (type === 'hero') newBlock.content = { title: 'Welcome', subtitle: 'Subtitle' };
            if (type === 'text') newBlock.content = { text: 'Lorem ipsum...' };
            if (type === 'image') newBlock.content = { src: '', alt: '' };

            this.blocks.push(newBlock);
            this.updateJson();
        },

        removeBlock(index) {
            if(confirm('Are you sure you want to remove this block?')) {
                this.blocks.splice(index, 1);
                this.selectedBlock = null;
                this.updateJson();
            }
        },

        selectBlock(index) {
            this.selectedBlock = index;
        },

        updateContent(key, value) {
            if (this.selectedBlock !== null) {
                this.blocks[this.selectedBlock].content[key] = value;
                this.updateJson();
            }
        },

        updateSettings(key, value) {
            if (this.selectedBlock !== null) {
                this.blocks[this.selectedBlock].settings[key] = value;
                this.updateJson();
            }
        },

        updateJson() {
            const jsonInput = document.getElementById('page-builder-data');
            if (jsonInput) {
                jsonInput.value = JSON.stringify(this.blocks);
                jsonInput.dispatchEvent(new Event('change'));
            }
        },

        setupDragAndDrop() {
            const container = document.getElementById('builder-canvas');
            if (!container) return;

            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.dragging = true;
            });

            container.addEventListener('drop', (e) => {
                e.preventDefault();
                this.dragging = false;
            });
        },

        moveBlock(fromIndex, toIndex) {
            if (toIndex < 0 || toIndex >= this.blocks.length) return;
            const item = this.blocks.splice(fromIndex, 1)[0];
            this.blocks.splice(toIndex, 0, item);
            this.selectedBlock = toIndex;
            this.updateJson();
        }
    }));

    // --- Dashboard Charts Logic ---
    Alpine.data('dashboardStats', () => ({
        chartInstance: null,
        stats: { visitors: 0, sales: 0, revenue: 0 },

        init() {
            this.fetchStats();
            this.initChart();
        },

        async fetchStats() {
            try {
                const response = await fetch('/admin/api/dashboard/stats');
                if (response.ok) {
                    this.stats = await response.json();
                }
            } catch (e) {
                console.warn('Could not load dashboard stats', e);
            }
        },

        initChart() {
            const ctx = document.getElementById('revenueChart');
            if (!ctx || typeof Chart === 'undefined') return;

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [120, 190, 300, 500, 200, 300, 450],
                        borderColor: '#4F46E5',
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }));

    // --- Confirmation Modal Logic ---
    Alpine.data('confirmAction', (message, callback) => ({
        open: false,
        message: message,
        confirm() {
            this.open = false;
            if (typeof callback === 'function') callback();
            else if (typeof callback === 'string') {
                document.getElementById(callback)?.submit();
                window.location.href = callback;
            }
        },
        cancel() {
            this.open = false;
        },
        show() {
            this.open = true;
        }
    }));
});

// Helper for global confirmations
window.vertexConfirm = function(message, callback) {
    if (confirm(message)) {
        callback();
    }
};