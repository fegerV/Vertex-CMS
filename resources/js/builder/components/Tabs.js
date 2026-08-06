/**
 * Tabs Component
 * Handles tabbed content navigation
 */
class Tabs {
    constructor(element) {
        this.element = element;
        this.tabList = element.querySelector('.tabs-list');
        this.tabs = element.querySelectorAll('.tab-item');
        this.panels = element.querySelectorAll('.tab-panel');
        this.activeTab = 0;
        
        this.init();
    }

    init() {
        // Set initial active tab
        if (this.tabs.length > 0) {
            this.activateTab(0);
        }

        // Add click handlers
        this.tabs.forEach((tab, index) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.activateTab(index);
            });

            // Keyboard navigation
            tab.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.activateTab((index + 1) % this.tabs.length);
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.activateTab((index - 1 + this.tabs.length) % this.tabs.length);
                } else if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.activateTab(index);
                }
            });
        });
    }

    activateTab(index) {
        if (index < 0 || index >= this.tabs.length) return;

        const previousTab = this.tabs[this.activeTab];
        const previousPanel = this.panels[this.activeTab];
        const newTab = this.tabs[index];
        const newPanel = this.panels[index];

        // Deactivate previous
        if (previousTab) {
            previousTab.classList.remove('active');
            previousTab.setAttribute('aria-selected', 'false');
            previousTab.setAttribute('tabindex', '-1');
        }
        if (previousPanel) {
            previousPanel.classList.remove('active');
            previousPanel.hidden = true;
        }

        // Activate new
        newTab.classList.add('active');
        newTab.setAttribute('aria-selected', 'true');
        newTab.setAttribute('tabindex', '0');
        newPanel.classList.add('active');
        newPanel.hidden = false;

        this.activeTab = index;

        // Emit custom event
        this.element.dispatchEvent(new CustomEvent('tab-change', {
            detail: { index, tabId: newTab.dataset.tabId }
        }));
    }

    getActiveTab() {
        return this.activeTab;
    }

    destroy() {
        this.tabs.forEach(tab => {
            tab.removeEventListener('click', this.activateTab);
            tab.removeEventListener('keydown', this.handleKeydown);
        });
    }
}

export default Tabs;
