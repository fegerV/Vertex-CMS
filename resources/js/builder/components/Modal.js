/**
 * Modal Component
 * Handles modal dialogs with overlay
 */
class Modal {
    constructor(element) {
        this.element = element;
        this.overlay = element.querySelector('.modal-overlay');
        this.content = element.querySelector('.modal-content');
        this.closeButtons = element.querySelectorAll('[data-modal-close]');
        this.triggerButtons = document.querySelectorAll(`[data-modal-open="${element.id}"]`);
        this.isOpen = false;
        
        this.init();
    }

    init() {
        // Close button handlers
        this.closeButtons.forEach(button => {
            button.addEventListener('click', () => this.close());
        });

        // Trigger button handlers
        this.triggerButtons.forEach(button => {
            button.addEventListener('click', () => this.open());
        });

        // Close on overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay && this.element.dataset.closeOnOverlay !== 'false') {
                    this.close();
                }
            });
        }

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });

        // Handle initial state
        if (this.element.dataset.autoOpen === 'true') {
            setTimeout(() => this.open(), 300);
        }
    }

    open() {
        if (this.isOpen) return;

        this.element.classList.add('active');
        this.element.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        
        // Focus first focusable element
        const focusable = this.content?.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            setTimeout(() => focusable.focus(), 100);
        }

        this.isOpen = true;

        // Emit custom event
        this.element.dispatchEvent(new CustomEvent('modal-open', {
            detail: { modalId: this.element.id }
        }));
    }

    close() {
        if (!this.isOpen) return;

        this.element.classList.remove('active');
        this.element.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        
        // Return focus to trigger button
        if (this.triggerButtons.length > 0) {
            this.triggerButtons[0].focus();
        }

        this.isOpen = false;

        // Emit custom event
        this.element.dispatchEvent(new CustomEvent('modal-close', {
            detail: { modalId: this.element.id }
        }));
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    destroy() {
        this.closeButtons.forEach(button => {
            button.removeEventListener('click', this.close);
        });
        this.triggerButtons.forEach(button => {
            button.removeEventListener('click', this.open);
        });
    }
}

export default Modal;
