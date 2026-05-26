import { createApp, ref, reactive, computed, onMounted } from 'vue';

// Expose Vue composition API globally for inline scripts (e.g., page builder)
window.Vue = { createApp, ref, reactive, computed, onMounted };

// Admin UI utilities (theme toggle, sidebar)
import './admin/app';
import { mountBuilderPrototype } from './components/builder/mountPrototype';
import { mountAdvancedBuilder } from './admin/builder/mountAdvancedBuilder';
import { mountFormBuilder } from './admin/forms/mountFormBuilder';
import { mountMediaManager } from './admin/media/mountMediaManager';
import { mountMediaPicker, unmountMediaPicker } from './admin/media/mountMediaPicker';

if (typeof window !== 'undefined') {
    window.Vertex = window.Vertex || {};
    window.Vertex.mountBuilderPrototype = mountBuilderPrototype;
    window.Vertex.mountAdvancedBuilder = mountAdvancedBuilder;
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

document.addEventListener('DOMContentLoaded', () => {
    mountBuilderPrototype();
    mountAdvancedBuilder();
    mountFormBuilder();
    mountMediaManager();
    mountFrontendGalleries();
    mountFrontendLightbox();
});

// Telegram Widget — lazy load only on frontend when element exists
if (typeof window !== 'undefined' && window.telegramWidgetConfig?.enabled) {
    import('./telegram-widget.js').catch(() => {
        // fail silently if module not found
    });
}
