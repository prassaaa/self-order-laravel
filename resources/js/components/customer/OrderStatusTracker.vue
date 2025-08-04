<template>
  <div class="space-y-4">
    <div 
      v-for="(step, index) in statusSteps" 
      :key="step.status"
      class="flex items-start space-x-4"
    >
      <!-- Step Icon -->
      <div class="flex flex-col items-center">
        <div 
          class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-colors"
          :class="getStepClasses(step, index)"
        >
          <component 
            :is="step.icon" 
            class="h-5 w-5"
            :class="getIconClasses(step)"
          />
        </div>
        
        <!-- Connector Line -->
        <div 
          v-if="index < statusSteps.length - 1"
          class="w-0.5 h-8 mt-2 transition-colors"
          :class="isStepCompleted(step) ? 'bg-primary' : 'bg-muted'"
        />
      </div>

      <!-- Step Content -->
      <div class="flex-1 pb-8">
        <h3 
          class="font-medium transition-colors"
          :class="isStepActive(step) ? 'text-primary' : isStepCompleted(step) ? 'text-foreground' : 'text-muted-foreground'"
        >
          {{ step.title }}
        </h3>
        <p 
          class="text-sm mt-1 transition-colors"
          :class="isStepActive(step) ? 'text-primary/80' : isStepCompleted(step) ? 'text-muted-foreground' : 'text-muted-foreground/60'"
        >
          {{ step.description }}
        </p>
        
        <!-- Estimated Time -->
        <p 
          v-if="isStepActive(step) && step.estimatedTime"
          class="text-xs text-primary mt-2"
        >
          Estimasi: {{ step.estimatedTime }}
        </p>
      </div>
    </div>

    <!-- Current Status Summary -->
    <div class="mt-6 p-4 bg-muted/50 rounded-lg">
      <div class="flex items-center space-x-2">
        <component 
          :is="currentStep?.icon" 
          class="h-5 w-5 text-primary"
        />
        <div>
          <p class="font-medium">{{ currentStep?.title }}</p>
          <p class="text-sm text-muted-foreground">{{ currentStep?.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { 
  Clock, 
  CheckCircle, 
  ChefHat, 
  Bell, 
  Package,
  XCircle
} from 'lucide-vue-next'

interface Props {
  status: 'pending' | 'confirmed' | 'preparing' | 'ready' | 'completed' | 'cancelled'
}

const props = defineProps<Props>()

const statusSteps = [
  {
    status: 'pending',
    title: 'Pesanan Diterima',
    description: 'Pesanan Anda telah diterima dan menunggu konfirmasi',
    icon: Clock,
    estimatedTime: '2-3 menit',
  },
  {
    status: 'confirmed',
    title: 'Pesanan Dikonfirmasi',
    description: 'Pesanan telah dikonfirmasi dan akan segera diproses',
    icon: CheckCircle,
    estimatedTime: '1-2 menit',
  },
  {
    status: 'preparing',
    title: 'Sedang Diproses',
    description: 'Chef sedang menyiapkan pesanan Anda dengan teliti',
    icon: ChefHat,
    estimatedTime: '10-15 menit',
  },
  {
    status: 'ready',
    title: 'Siap Diambil',
    description: 'Pesanan sudah siap! Silakan ambil di counter',
    icon: Bell,
    estimatedTime: null,
  },
  {
    status: 'completed',
    title: 'Selesai',
    description: 'Pesanan telah diselesaikan. Terima kasih!',
    icon: Package,
    estimatedTime: null,
  },
]

const currentStep = computed(() => {
  if (props.status === 'cancelled') {
    return {
      status: 'cancelled',
      title: 'Pesanan Dibatalkan',
      description: 'Pesanan telah dibatalkan',
      icon: XCircle,
      estimatedTime: null,
    }
  }
  
  return statusSteps.find(step => step.status === props.status)
})

const currentStepIndex = computed(() => {
  if (props.status === 'cancelled') return -1
  return statusSteps.findIndex(step => step.status === props.status)
})

const isStepCompleted = (step: any) => {
  if (props.status === 'cancelled') return false
  const stepIndex = statusSteps.findIndex(s => s.status === step.status)
  return stepIndex < currentStepIndex.value
}

const isStepActive = (step: any) => {
  if (props.status === 'cancelled') return false
  return step.status === props.status
}

const getStepClasses = (step: any, index: number) => {
  if (props.status === 'cancelled') {
    return 'border-muted bg-muted'
  }
  
  if (isStepActive(step)) {
    return 'border-primary bg-primary text-primary-foreground'
  }
  
  if (isStepCompleted(step)) {
    return 'border-primary bg-primary text-primary-foreground'
  }
  
  return 'border-muted bg-background'
}

const getIconClasses = (step: any) => {
  if (props.status === 'cancelled') {
    return 'text-muted-foreground'
  }
  
  if (isStepActive(step) || isStepCompleted(step)) {
    return 'text-primary-foreground'
  }
  
  return 'text-muted-foreground'
}
</script>
