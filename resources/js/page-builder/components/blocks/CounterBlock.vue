<template>
<div class="block-content">
    <div class="vc-counter text-center p-4">
        <div 
            ref="counterValue"
            :style="counterStyle"
            class="counter-value font-bold"
        >
            {{ displayValue }}
        </div>
        <div 
            v-if="settings.label"
            class="counter-label text-slate-500 mt-2"
        >
            {{ settings.label }}
        </div>
    </div>
</div>
</template>

<script>
export default {
    name: 'CounterBlock',
    props: {
        block: Object,
        settings: {
            type: Object,
            default: () => ({})
        },
        index: Number
    },
    data() {
        return {
            currentValue: 0,
            isAnimated: false
        };
    },
    computed: {
        endValue() {
            return parseInt(this.settings.end_value) || 100;
        },
        duration() {
            return parseInt(this.settings.duration) || 2000;
        },
        prefix() {
            return this.settings.prefix || '';
        },
        suffix() {
            return this.settings.suffix || '';
        },
        displayValue() {
            return `${this.prefix}${this.currentValue}${this.suffix}`;
        },
        counterStyle() {
            return {
                fontSize: this.settings.font_size || '3rem',
                color: this.settings.color || '#3b82f6'
            };
        }
    },
    mounted() {
        this.animateCounter();
    },
    methods: {
        animateCounter() {
            if (this.isAnimated) return;
            
            const startTime = Date.now();
            const startValue = 0;
            const change = this.endValue - startValue;
            
            const animate = () => {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / this.duration, 1);
                
                // Easing function
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                
                this.currentValue = Math.floor(startValue + change * easeOutQuart);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    this.currentValue = this.endValue;
                    this.isAnimated = true;
                }
            };
            
            requestAnimationFrame(animate);
        }
    }
};
</script>

<style scoped>
.counter-value {
    transition: all 0.1s ease;
}
</style>
