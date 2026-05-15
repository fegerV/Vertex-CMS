<template>
  <div class="hero-block p-6">
    <div v-if="modelValue.title || modelValue.subtitle || modelValue.background" class="relative overflow-hidden rounded-lg">
      <div v-if="modelValue.background" 
           class="absolute inset-0 bg-[url(':background')] bg-cover bg-center opacity-20" 
           :style="'background-image: url(' + modelValue.background + ')'">
      </div>
      <div class="relative z-10 text-center">
        <h1 v-if="modelValue.title" 
            class="text-3xl font-bold text-white mb-4" 
            :style="{ color: modelValue.titleColor || 'white' }">
          {{ modelValue.title }}
        </h1>
        <p v-if="modelValue.subtitle" 
           class="text-xl text-white/90 mb-6" 
           :style="{ color: modelValue.subtitleColor || 'white' }">
          {{ modelValue.subtitle }}
        </p>
        <div v-if="modelValue.buttonText && modelValue.buttonUrl" class="inline-block">
          <a 
            :href="modelValue.buttonUrl"
            :target="modelValue.buttonTarget || '_self'"
            class="btn btn-primary px-6 py-3 rounded-lg font-medium transition-colors"
            :style="{ 
              backgroundColor: modelValue.buttonBgColor || '#3b82f6',
              color: modelValue.buttonTextColor || 'white',
              borderColor: modelValue.buttonBorderColor || 'transparent'
            }">
            {{ modelValue.buttonText }}
          </a>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-12">
      <div class="hero-placeholder flex flex-col items-center gap-4">
        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-600">Герой блок</h3>
        <p class="text-sm text-gray-500">Добавьте заголовок, подзаголовок и фон</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      title: '',
      subtitle: '',
      background: '',
      titleColor: '',
      subtitleColor: '',
      buttonText: '',
      buttonUrl: '',
      buttonTarget: '_self',
      buttonBgColor: '#3b82f6',
      buttonTextColor: 'white',
      buttonBorderColor: 'transparent',
      paddingTop: 80,
      paddingBottom: 80
    })
  }
})
const emit = defineEmits(['update:modelValue'])

</script>