/**
 * Counter Component
 * Handles animated number counters
 */
class Counter {
    constructor(element) {
        this.element = element;
        this.target = parseInt(element.dataset.target) || 0;
        this.duration = parseInt(element.dataset.duration) || 2000;
        this.prefix = element.dataset.prefix || '';
        this.suffix = element.dataset.suffix || '';
        this.startOnScroll = element.dataset.startOnScroll !== 'false';
        this.hasStarted = false;
        this.currentValue = 0;
        
        this.init();
    }

    init() {
        this.displayElement = this.element.querySelector('.counter-value') || this.element;
        
        if (this.startOnScroll) {
            // Use Intersection Observer for scroll-triggered animation
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !this.hasStarted) {
                        this.hasStarted = true;
                        this.animate();
                    }
                });
            }, { threshold: 0.5 });
            
            this.observer.observe(this.element);
        } else {
            // Start immediately
            this.animate();
        }
    }

    animate() {
        const startTime = performance.now();
        const startValue = 0;
        const change = this.target - startValue;

        const updateCounter = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / this.duration, 1);
            
            // Easing function (easeOutQuart)
            const easeProgress = 1 - Math.pow(1 - progress, 4);
            
            this.currentValue = Math.floor(startValue + change * easeProgress);
            this.updateDisplay();

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                this.currentValue = this.target;
                this.updateDisplay();
                
                this.element.dispatchEvent(new CustomEvent('counter-complete', {
                    detail: { value: this.target }
                }));
            }
        };

        requestAnimationFrame(updateCounter);
    }

    updateDisplay() {
        if (this.displayElement) {
            this.displayElement.textContent = `${this.prefix}${this.currentValue}${this.suffix}`;
        }
    }

    setValue(value) {
        this.target = value;
        this.hasStarted = false;
        this.currentValue = 0;
        this.animate();
    }

    reset() {
        this.currentValue = 0;
        this.updateDisplay();
        this.hasStarted = false;
    }

    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}

export default Counter;
