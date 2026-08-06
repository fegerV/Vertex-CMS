/**
 * Accordion Component
 * Handles collapsible content sections
 */
class Accordion {
    constructor(element) {
        this.element = element;
        this.items = element.querySelectorAll('.accordion-item');
        this.allowMultiple = element.dataset.allowMultiple === 'true';
        
        this.init();
    }

    init() {
        this.items.forEach((item, index) => {
            const header = item.querySelector('.accordion-header');
            const content = item.querySelector('.accordion-content');
            
            if (header && content) {
                header.addEventListener('click', () => this.toggleItem(index));
                
                // Set initial state
                if (!item.classList.contains('active')) {
                    content.style.maxHeight = null;
                }
            }
        });
    }

    toggleItem(index) {
        const item = this.items[index];
        const content = item.querySelector('.accordion-content');
        const isActive = item.classList.contains('active');

        if (!this.allowMultiple && !isActive) {
            // Close all other items
            this.items.forEach((otherItem, otherIndex) => {
                if (otherIndex !== index) {
                    this.closeItem(otherIndex);
                }
            });
        }

        if (isActive) {
            this.closeItem(index);
        } else {
            this.openItem(index);
        }

        // Emit custom event
        this.element.dispatchEvent(new CustomEvent('accordion-toggle', {
            detail: { index, isOpen: !isActive }
        }));
    }

    openItem(index) {
        const item = this.items[index];
        const content = item.querySelector('.accordion-content');
        
        item.classList.add('active');
        content.style.maxHeight = content.scrollHeight + 'px';
    }

    closeItem(index) {
        const item = this.items[index];
        const content = item.querySelector('.accordion-content');
        
        item.classList.remove('active');
        content.style.maxHeight = null;
    }

    destroy() {
        this.items.forEach(item => {
            const header = item.querySelector('.accordion-header');
            if (header) {
                header.removeEventListener('click', this.toggleItem);
            }
        });
    }
}

export default Accordion;
