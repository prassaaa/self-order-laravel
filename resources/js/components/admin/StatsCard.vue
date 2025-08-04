<template>
  <Card>
    <CardContent class="p-6">
      <div class="flex items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">{{ title }}</CardTitle>
        <component :is="iconComponent" class="h-4 w-4 text-muted-foreground" />
      </div>
      <div>
        <div class="text-2xl font-bold">{{ value }}</div>
        <p v-if="change !== undefined" class="text-xs text-muted-foreground">
          <span 
            :class="{
              'text-green-600': changeType === 'positive',
              'text-red-600': changeType === 'negative',
              'text-muted-foreground': changeType === 'neutral'
            }"
          >
            {{ change > 0 ? '+' : '' }}{{ change }}%
          </span>
          from last month
        </p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardTitle } from '@/components/ui/card'
import { 
  ShoppingBag, 
  Clock, 
  DollarSign, 
  TrendingUp, 
  Users,
  Package,
  Star,
  AlertCircle
} from 'lucide-vue-next'

interface Props {
  title: string
  value: string | number
  change?: number
  changeType?: 'positive' | 'negative' | 'neutral'
  icon: string
}

const props = defineProps<Props>()

const iconComponent = computed(() => {
  const icons: Record<string, any> = {
    ShoppingBag,
    Clock,
    DollarSign,
    TrendingUp,
    Users,
    Package,
    Star,
    AlertCircle,
  }
  return icons[props.icon] || ShoppingBag
})
</script>
