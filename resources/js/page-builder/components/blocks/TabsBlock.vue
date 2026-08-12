<template>
<div class="block-content">
    <div class="vc-tabs">
        <div class="tabs-header border-b border-slate-200 flex gap-1 mb-4">
            <button
                v-for="(tab, index) in tabs"
                :key="index"
                @click="activeTab = index"
                :class="{
                    'border-blue-500 text-blue-600': activeTab === index,
                    'border-transparent text-slate-600 hover:text-slate-800': activeTab !== index
                }"
                class="px-4 py-2 font-medium text-sm border-b-2 transition-colors"
            >
                {{ tab.title || `Вкладка ${index + 1}` }}
            </button>
        </div>
        <div class="tabs-content">
            <div 
                v-for="(tab, index) in tabs"
                :key="index"
                v-show="activeTab === index"
                class="tab-panel animate-fade-in"
            >
                <div v-html="tab.content || 'Содержимое вкладки'"></div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    name: 'TabsBlock',
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
            activeTab: 0
        };
    },
    computed: {
        tabs() {
            return this.settings.tabs || [];
        }
    }
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
