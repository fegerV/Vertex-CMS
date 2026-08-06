/**
 * Lightbox Component
 * Handles image/video gallery lightbox
 */
class Lightbox {
    constructor(element) {
        this.element = element;
        this.items = Array.from(element.querySelectorAll('[data-lightbox-item]'));
        this.currentIndex = 0;
        this.isOpen = false;
        
        this.init();
    }

    init() {
        // Create lightbox overlay
        this.createOverlay();

        // Add click handlers to items
        this.items.forEach((item, index) => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                this.open(index);
            });

            // Keyboard support for individual items
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.open(index);
                }
            });
        });

        // Global keyboard events
        document.addEventListener('keydown', (e) => {
            if (!this.isOpen) return;
            
            if (e.key === 'ArrowLeft') {
                this.prev();
            } else if (e.key === 'ArrowRight') {
                this.next();
            } else if (e.key === 'Escape') {
                this.close();
            }
        });
    }

    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'lightbox-overlay';
        this.overlay.innerHTML = `
            <button class="lightbox-close" aria-label="Close">&times;</button>
            <button class="lightbox-nav lightbox-prev" aria-label="Previous">&#8249;</button>
            <button class="lightbox-nav lightbox-next" aria-label="Next">&#8250;</button>
            <div class="lightbox-content">
                <div class="lightbox-media"></div>
                <div class="lightbox-caption"></div>
            </div>
        `;
        
        document.body.appendChild(this.overlay);

        // Close button
        this.overlay.querySelector('.lightbox-close').addEventListener('click', () => this.close());
        
        // Navigation
        this.overlay.querySelector('.lightbox-prev').addEventListener('click', (e) => {
            e.stopPropagation();
            this.prev();
        });
        this.overlay.querySelector('.lightbox-next').addEventListener('click', (e) => {
            e.stopPropagation();
            this.next();
        });

        // Close on overlay click
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay || e.target.classList.contains('lightbox-content')) {
                this.close();
            }
        });
    }

    open(index) {
        if (index < 0 || index >= this.items.length) return;
        
        this.currentIndex = index;
        this.isOpen = true;
        this.overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        this.updateContent();

        this.element.dispatchEvent(new CustomEvent('lightbox-open', {
            detail: { index, item: this.items[index] }
        }));
    }

    close() {
        if (!this.isOpen) return;
        
        this.isOpen = false;
        this.overlay.classList.remove('active');
        document.body.style.overflow = '';
        
        this.element.dispatchEvent(new CustomEvent('lightbox-close', {
            detail: { index: this.currentIndex }
        }));
    }

    next() {
        const newIndex = (this.currentIndex + 1) % this.items.length;
        this.open(newIndex);
    }

    prev() {
        const newIndex = (this.currentIndex - 1 + this.items.length) % this.items.length;
        this.open(newIndex);
    }

    updateContent() {
        const currentItem = this.items[this.currentIndex];
        const mediaContainer = this.overlay.querySelector('.lightbox-media');
        const captionContainer = this.overlay.querySelector('.lightbox-caption');
        
        const src = currentItem.dataset.lightboxSrc || currentItem.src || currentItem.href;
        const type = currentItem.dataset.lightboxType || 'image';
        const caption = currentItem.dataset.lightboxCaption || currentItem.alt || '';

        if (type === 'image') {
            mediaContainer.innerHTML = `<img src="${src}" alt="${caption}" loading="eager">`;
        } else if (type === 'video') {
            mediaContainer.innerHTML = `
                <iframe src="${src}" frameborder="0" allowfullscreen></iframe>
            `;
        }

        captionContainer.textContent = caption;
        captionContainer.style.display = caption ? 'block' : 'none';

        // Update navigation visibility
        this.overlay.querySelector('.lightbox-prev').style.display = this.items.length > 1 ? 'block' : 'none';
        this.overlay.querySelector('.lightbox-next').style.display = this.items.length > 1 ? 'block' : 'none';
    }

    destroy() {
        if (this.overlay) {
            this.overlay.remove();
        }
    }
}

export default Lightbox;
