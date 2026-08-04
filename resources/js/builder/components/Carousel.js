/**
 * Carousel Component
 * Handles image/content carousel with navigation
 */
class Carousel {
    constructor(element) {
        this.element = element;
        this.track = element.querySelector('.carousel-track');
        this.slides = Array.from(element.querySelectorAll('.carousel-slide'));
        this.prevButton = element.querySelector('[data-carousel-prev]');
        this.nextButton = element.querySelector('[data-carousel-next]');
        this.dotsContainer = element.querySelector('.carousel-dots');
        
        this.currentIndex = 0;
        this.totalSlides = this.slides.length;
        this.autoplay = element.dataset.autoplay === 'true';
        this.autoplayInterval = parseInt(element.dataset.autoplayInterval) || 5000;
        this.loop = element.dataset.loop !== 'false';
        this.touchStartX = 0;
        this.touchEndX = 0;
        
        this.init();
    }

    init() {
        // Create dots if container exists
        if (this.dotsContainer && this.totalSlides > 1) {
            this.createDots();
        }

        // Navigation buttons
        if (this.prevButton) {
            this.prevButton.addEventListener('click', () => this.prev());
        }
        if (this.nextButton) {
            this.nextButton.addEventListener('click', () => this.next());
        }

        // Touch support
        this.track?.addEventListener('touchstart', (e) => {
            this.touchStartX = e.changedTouches[0].screenX;
        });

        this.track?.addEventListener('touchend', (e) => {
            this.touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        });

        // Keyboard navigation
        this.element.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                this.prev();
            } else if (e.key === 'ArrowRight') {
                this.next();
            }
        });

        // Autoplay
        if (this.autoplay && this.totalSlides > 1) {
            this.startAutoplay();
            
            // Pause on hover
            this.element.addEventListener('mouseenter', () => this.stopAutoplay());
            this.element.addEventListener('mouseleave', () => this.startAutoplay());
        }

        // Initial update
        this.updateSlidePosition();
        this.updateDots();
        this.updateButtons();
    }

    createDots() {
        this.dotsContainer.innerHTML = '';
        this.slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.className = `carousel-dot ${index === 0 ? 'active' : ''}`;
            dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
            dot.addEventListener('click', () => this.goToSlide(index));
            this.dotsContainer.appendChild(dot);
        });
        this.dots = Array.from(this.dotsContainer.querySelectorAll('.carousel-dot'));
    }

    next() {
        if (this.currentIndex >= this.totalSlides - 1) {
            if (this.loop) {
                this.goToSlide(0);
            }
        } else {
            this.goToSlide(this.currentIndex + 1);
        }
    }

    prev() {
        if (this.currentIndex <= 0) {
            if (this.loop) {
                this.goToSlide(this.totalSlides - 1);
            }
        } else {
            this.goToSlide(this.currentIndex - 1);
        }
    }

    goToSlide(index) {
        if (index < 0 || index >= this.totalSlides) return;
        
        this.currentIndex = index;
        this.updateSlidePosition();
        this.updateDots();
        this.updateButtons();

        this.element.dispatchEvent(new CustomEvent('carousel-change', {
            detail: { index, slide: this.slides[index] }
        }));
    }

    updateSlidePosition() {
        if (!this.track) return;
        
        const offset = -this.currentIndex * 100;
        this.track.style.transform = `translateX(${offset}%)`;
        this.track.style.transition = 'transform 0.5s ease-in-out';
    }

    updateDots() {
        if (this.dots) {
            this.dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === this.currentIndex);
            });
        }
    }

    updateButtons() {
        if (this.prevButton) {
            this.prevButton.disabled = this.currentIndex === 0 && !this.loop;
            this.prevButton.style.opacity = this.currentIndex === 0 && !this.loop ? '0.5' : '1';
        }
        if (this.nextButton) {
            this.nextButton.disabled = this.currentIndex === this.totalSlides - 1 && !this.loop;
            this.nextButton.style.opacity = this.currentIndex === this.totalSlides - 1 && !this.loop ? '0.5' : '1';
        }
    }

    handleSwipe() {
        const swipeDistance = this.touchEndX - this.touchStartX;
        const threshold = 50;

        if (swipeDistance > threshold) {
            this.prev();
        } else if (swipeDistance < -threshold) {
            this.next();
        }
    }

    startAutoplay() {
        if (this.autoplayTimer) {
            clearInterval(this.autoplayTimer);
        }
        this.autoplayTimer = setInterval(() => this.next(), this.autoplayInterval);
    }

    stopAutoplay() {
        if (this.autoplayTimer) {
            clearInterval(this.autoplayTimer);
            this.autoplayTimer = null;
        }
    }

    destroy() {
        this.stopAutoplay();
        if (this.dotsContainer) {
            this.dotsContainer.innerHTML = '';
        }
    }
}

export default Carousel;
