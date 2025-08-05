<template>
  <div 
    class="inline-flex items-center justify-center"
    :class="sizeClasses"
  >
    <div 
      ref="spinnerRef"
      class="relative"
    >
      <!-- Modern spinner with gradient -->
      <div 
        class="animate-spin rounded-full border-4 border-transparent bg-gradient-to-r from-primary to-primary/30 bg-clip-border"
        :class="[sizeClasses, 'border-t-primary']"
        style="mask: radial-gradient(farthest-side, transparent calc(100% - 4px), black calc(100% - 4px))"
      ></div>
      
      <!-- Inner glow effect -->
      <div 
        class="absolute inset-0 rounded-full bg-gradient-to-r from-primary/20 to-transparent animate-pulse"
      ></div>
    </div>
    
    <!-- Optional text -->
    <span 
      v-if="text" 
      class="ml-3 text-sm font-medium text-muted-foreground animate-pulse"
    >
      {{ text }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAnimations } from '@/composables/useAnimations'

interface Props {
  size?: 'sm' | 'md' | 'lg' | 'xl'
  text?: string
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
})

const spinnerRef = ref<HTMLElement>()
const { createLoadingAnimation } = useAnimations()

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
  }
  return sizes[props.size]
})

onMounted(() => {
  if (spinnerRef.value) {
    createLoadingAnimation(spinnerRef.value)
  }
})
</script>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
}
</style>
