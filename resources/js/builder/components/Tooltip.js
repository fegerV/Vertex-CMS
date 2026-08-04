/**
 * Tooltip Component
 * Handles hover/click tooltips
 */
class Tooltip {
    constructor(element) {
        this.element = element;
        this.tooltip = element.querySelector('.tooltip-content');
        this.triggerType = element.dataset.trigger || 'hover'; // hover, click, focus
        this.position = element.dataset.position || 'top'; // top, bottom, left, right
        this.isOpen = false;
        
        this.init();
    }

    init() {
        if (this.triggerType === 'hover') {
            this.element.addEventListener('mouseenter', () => this.show());
            this.element.addEventListener('mouseleave', () => this.hide());
        } else if (this.triggerType === 'click') {
            this.element.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });
            
            // Close on outside click
            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.element.contains(e.target)) {
                    this.hide();
                }
            });
        } else if (this.triggerType === 'focus') {
            this.element.addEventListener('focus', () => this.show());
            this.element.addEventListener('blur', () => this.hide());
        }

        // Handle keyboard
        this.element.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.hide();
            }
        });
    }

    show() {
        if (this.isOpen || !this.tooltip) return;

        this.tooltip.classList.add('active');
        this.tooltip.setAttribute('aria-hidden', 'false');
        this.positionTooltip();
        this.isOpen = true;

        this.element.dispatchEvent(new CustomEvent('tooltip-show', {
            detail: { element: this.element }
        }));
    }

    hide() {
        if (!this.isOpen || !this.tooltip) return;

        this.tooltip.classList.remove('active');
        this.tooltip.setAttribute('aria-hidden', 'true');
        this.isOpen = false;

        this.element.dispatchEvent(new CustomEvent('tooltip-hide', {
            detail: { element: this.element }
        }));
    }

    toggle() {
        if (this.isOpen) {
            this.hide();
        } else {
            this.show();
        }
    }

    positionTooltip() {
        if (!this.tooltip) return;

        const rect = this.element.getBoundingClientRect();
        const tooltipRect = this.tooltip.getBoundingClientRect();
        const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollY = window.pageYOffset || document.documentElement.scrollTop;

        let top, left;

        switch (this.position) {
            case 'top':
                top = rect.top + scrollY - tooltipRect.height - 10;
                left = rect.left + scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                break;
            case 'bottom':
                top = rect.bottom + scrollY + 10;
                left = rect.left + scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                break;
            case 'left':
                top = rect.top + scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.left + scrollX - tooltipRect.width - 10;
                break;
            case 'right':
                top = rect.top + scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.right + scrollX + 10;
                break;
            default:
                top = rect.top + scrollY - tooltipRect.height - 10;
                left = rect.left + scrollX + (rect.width / 2) - (tooltipRect.width / 2);
        }

        // Boundary check
        if (top < scrollY) {
            top = rect.bottom + scrollY + 10;
        }
        if (left < scrollX) {
            left = scrollX + 5;
        }
        if (left + tooltipRect.width > scrollX + window.innerWidth) {
            left = scrollX + window.innerWidth - tooltipRect.width - 5;
        }

        this.tooltip.style.top = `${top}px`;
        this.tooltip.style.left = `${left}px`;
    }

    destroy() {
        this.hide();
    }
}

export default Tooltip;
