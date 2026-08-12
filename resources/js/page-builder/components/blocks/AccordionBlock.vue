<template>
<div class="block-content">
    <div class="vc-accordion space-y-2">
        <div 
            v-for="(item, index) in items" 
            :key="index"
            class="border border-slate-200 rounded-lg overflow-hidden"
        >
            <button
                @click="toggleItem(index)"
                class="w-full px-4 py-3 text-left bg-slate-50 hover:bg-slate-100 transition-colors flex items-center justify-between"
            >
                <span class="font-medium text-slate-800">{{ item.question || `Вопрос ${index + 1}` }}</span>
                <svg 
                    class="w-5 h-5 text-slate-500 transition-transform duration-200"
                    :class="{ 'rotate-180': openIndex === index }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div 
                v-show="openIndex === index"
                class="px-4 py-3 bg-white border-t border-slate-200"
            >
                <p class="text-slate-600">{{ item.answer || 'Ответ' }}</p>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    name: 'AccordionBlock',
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
            openIndex: null
        };
    },
    computed: {
        items() {
            return this.settings.items || [];
        }
    },
    methods: {
        toggleItem(index) {
            this.openIndex = this.openIndex === index ? null : index;
        }
    }
};
</script>

<style scoped>
.vc-accordion button {
    transition: all 0.2s ease;
}
</style>
