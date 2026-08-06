/**
 * Page Builder Components Initializer
 * Auto-initializes all interactive components on page load
 */
import Accordion from './components/Accordion.js';
import Tabs from './components/Tabs.js';
import Modal from './components/Modal.js';
import Tooltip from './components/Tooltip.js';
import Counter from './components/Counter.js';
import Lightbox from './components/Lightbox.js';
import Carousel from './components/Carousel.js';

class PageBuilderComponents {
    constructor() {
        this.components = {
            accordion: [],
            tabs: [],
            modal: [],
            tooltip: [],
            counter: [],
            lightbox: [],
            carousel: []
        };
        
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeAll());
        } else {
            this.initializeAll();
        }
    }

    initializeAll() {
        this.initializeAccordions();
        this.initializeTabs();
        this.initializeModals();
        this.initializeTooltips();
        this.initializeCounters();
        this.initializeLightboxes();
        this.initializeCarousels();

        console.log('Page Builder Components initialized:', this.countComponents());
    }

    initializeAccordions() {
        const accordions = document.querySelectorAll('.accordion[data-pb-component="accordion"]');
        accordions.forEach(element => {
            const accordion = new Accordion(element);
            this.components.accordion.push(accordion);
        });
    }

    initializeTabs() {
        const tabsContainers = document.querySelectorAll('.tabs[data-pb-component="tabs"]');
        tabsContainers.forEach(element => {
            const tabs = new Tabs(element);
            this.components.tabs.push(tabs);
        });
    }

    initializeModals() {
        const modals = document.querySelectorAll('.modal[data-pb-component="modal"]');
        modals.forEach(element => {
            const modal = new Modal(element);
            this.components.modal.push(modal);
        });
    }

    initializeTooltips() {
        const tooltips = document.querySelectorAll('.tooltip[data-pb-component="tooltip"]');
        tooltips.forEach(element => {
            const tooltip = new Tooltip(element);
            this.components.tooltip.push(tooltip);
        });
    }

    initializeCounters() {
        const counters = document.querySelectorAll('.counter[data-pb-component="counter"]');
        counters.forEach(element => {
            const counter = new Counter(element);
            this.components.counter.push(counter);
        });
    }

    initializeLightboxes() {
        const lightboxes = document.querySelectorAll('[data-pb-component="lightbox"], .gallery[data-lightbox]');
        lightboxes.forEach(element => {
            const lightbox = new Lightbox(element);
            this.components.lightbox.push(lightbox);
        });
    }

    initializeCarousels() {
        const carousels = document.querySelectorAll('.carousel[data-pb-component="carousel"]');
        carousels.forEach(element => {
            const carousel = new Carousel(element);
            this.components.carousel.push(carousel);
        });
    }

    countComponents() {
        let total = 0;
        for (const key in this.components) {
            total += this.components[key].length;
        }
        return total;
    }

    // Public API to get component instances
    getComponent(type, index = 0) {
        return this.components[type]?.[index] || null;
    }

    getAllComponents(type) {
        return this.components[type] || [];
    }

    // Destroy all components (useful for SPA navigation)
    destroyAll() {
        for (const type in this.components) {
            this.components[type].forEach(component => {
                if (component.destroy) {
                    component.destroy();
                }
            });
            this.components[type] = [];
        }
    }

    // Reinitialize after dynamic content load
    reinitialize() {
        this.destroyAll();
        this.initializeAll();
    }
}

// Initialize and expose globally
const pageBuilderComponents = new PageBuilderComponents();

// Export for module usage
export { 
    PageBuilderComponents, 
    Accordion, 
    Tabs, 
    Modal, 
    Tooltip, 
    Counter, 
    Lightbox, 
    Carousel 
};

// Also attach to window for non-module usage
window.PageBuilderComponents = pageBuilderComponents;
