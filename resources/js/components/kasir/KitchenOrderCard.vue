<template>
  <Card class="hover:shadow-md transition-shadow" :class="getCardClasses()">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <div>
          <CardTitle class="text-lg">{{ order.order_number }}</CardTitle>
          <CardDescription class="flex items-center space-x-2">
            <Clock class="h-3 w-3" />
            <span>{{ waitTime }} ago</span>
            <span v-if="order.table_number" class="text-xs">• Table {{ order.table_number }}</span>
          </CardDescription>
        </div>
        <div class="text-right">
          <div class="text-sm font-medium">{{ order.order_items.length }} items</div>
          <div class="text-xs text-muted-foreground">{{ order.formatted_total }}</div>
        </div>
      </div>
    </CardHeader>

    <CardContent class="space-y-3">
      <!-- Order Items -->
      <div class="space-y-2">
        <div 
          v-for="item in order.order_items" 
          :key="item.id"
          class="flex items-center justify-between text-sm"
        >
          <div class="flex-1">
            <span class="font-medium">{{ item.quantity }}x {{ item.menu?.name }}</span>
            <p v-if="item.notes" class="text-xs text-muted-foreground mt-1">
              <strong>Notes:</strong> {{ item.notes }}
            </p>
          </div>
        </div>
      </div>

      <!-- Customer Info -->
      <div v-if="order.customer_name || order.notes" class="pt-2 border-t">
        <div v-if="order.customer_name" class="text-sm">
          <span class="text-muted-foreground">Customer:</span>
          <span class="font-medium ml-1">{{ order.customer_name }}</span>
        </div>
        <div v-if="order.notes" class="text-xs text-muted-foreground mt-1 bg-muted p-2 rounded">
          <strong>Order Notes:</strong> {{ order.notes }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="pt-3 space-y-2">
        <div class="grid grid-cols-2 gap-2">
          <!-- Primary Action -->
          <Button 
            v-if="order.status === 'confirmed'"
            size="sm"
            @click="$emit('start-preparing', order)"
            :disabled="updating"
          >
            <ChefHat class="h-3 w-3 mr-1" />
            Start Preparing
          </Button>

          <Button 
            v-if="order.status === 'preparing'"
            size="sm"
            @click="$emit('mark-ready', order)"
            :disabled="updating"
          >
            <Bell class="h-3 w-3 mr-1" />
            Mark Ready
          </Button>

          <Button 
            v-if="order.status === 'ready'"
            size="sm"
            @click="$emit('complete-order', order)"
            :disabled="updating"
          >
            <Package class="h-3 w-3 mr-1" />
            Complete
          </Button>

          <!-- View Details -->
          <Button 
            variant="outline"
            size="sm"
            @click="$emit('view-details', order)"
          >
            <Eye class="h-3 w-3 mr-1" />
            Details
          </Button>
        </div>

        <!-- Priority Indicator -->
        <div v-if="isUrgent" class="flex items-center justify-center text-xs text-red-600 bg-red-50 p-1 rounded">
          <AlertTriangle class="h-3 w-3 mr-1" />
          Urgent - {{ waitTimeMinutes }}+ minutes
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { 
  Clock, 
  ChefHat, 
  Bell, 
  Package, 
  Eye,
  AlertTriangle
} from 'lucide-vue-next'
import { type AdminOrder } from '@/stores/admin'

interface Props {
  order: AdminOrder
  statusColor: 'blue' | 'yellow' | 'green'
}

interface Emits {
  (e: 'start-preparing', order: AdminOrder): void
  (e: 'mark-ready', order: AdminOrder): void
  (e: 'complete-order', order: AdminOrder): void
  (e: 'view-details', order: AdminOrder): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const updating = ref(false)

const orderTime = computed(() => new Date(props.order.created_at))
const now = computed(() => new Date())

const waitTimeMinutes = computed(() => {
  return Math.floor((now.value.getTime() - orderTime.value.getTime()) / (1000 * 60))
})

const waitTime = computed(() => {
  const minutes = waitTimeMinutes.value
  
  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m`
  
  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60
  return `${hours}h ${remainingMinutes}m`
})

const isUrgent = computed(() => {
  // Mark as urgent if waiting more than 15 minutes
  return waitTimeMinutes.value > 15
})

const getCardClasses = () => {
  const baseClasses = 'relative'
  
  // Add urgency styling
  if (isUrgent.value) {
    return `${baseClasses} border-l-4 border-l-red-500 bg-red-50/50`
  }
  
  // Status-based styling
  switch (props.statusColor) {
    case 'blue':
      return `${baseClasses} border-l-4 border-l-blue-500`
    case 'yellow':
      return `${baseClasses} border-l-4 border-l-yellow-500`
    case 'green':
      return `${baseClasses} border-l-4 border-l-green-500`
    default:
      return baseClasses
  }
}
</script>
