<template>
  <Card class="hover:shadow-md transition-shadow" :class="getCardClasses()">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <div>
          <CardTitle class="text-lg">{{ order.order_number }}</CardTitle>
          <CardDescription>
            {{ order.created_at_human }}
          </CardDescription>
        </div>
        <Badge :variant="getStatusVariant(order.status)" class="text-xs">
          {{ getStatusLabel(order.status) }}
        </Badge>
      </div>
    </CardHeader>

    <CardContent class="space-y-4">
      <!-- Customer Info -->
      <div class="space-y-1">
        <div class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Customer:</span>
          <span class="font-medium">{{ order.customer_name || 'Guest' }}</span>
        </div>
        <div v-if="order.table_number" class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Table:</span>
          <span class="font-medium">{{ order.table_number }}</span>
        </div>
        <div v-if="order.customer_phone" class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Phone:</span>
          <span class="font-medium">{{ order.customer_phone }}</span>
        </div>
      </div>

      <!-- Order Items Summary -->
      <div class="space-y-2">
        <div class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Items:</span>
          <span class="font-medium">{{ order.order_items.length }} items</span>
        </div>
        <div class="space-y-1">
          <div 
            v-for="item in order.order_items.slice(0, 2)" 
            :key="item.id"
            class="flex items-center justify-between text-xs"
          >
            <span class="truncate">{{ item.quantity }}x {{ item.menu?.name }}</span>
            <span class="font-medium">{{ formatCurrency(item.subtotal) }}</span>
          </div>
          <div v-if="order.order_items.length > 2" class="text-xs text-muted-foreground">
            +{{ order.order_items.length - 2 }} more items
          </div>
        </div>
      </div>

      <!-- Total & Payment Status -->
      <div class="space-y-2 pt-2 border-t">
        <div class="flex items-center justify-between">
          <span class="font-medium">Total:</span>
          <span class="font-bold text-lg">{{ order.formatted_total }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Payment:</span>
          <Badge :variant="getPaymentStatusVariant(order.payment_status)" class="text-xs">
            {{ getPaymentStatusLabel(order.payment_status) }}
          </Badge>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="order.notes" class="text-xs text-muted-foreground bg-muted p-2 rounded">
        <strong>Notes:</strong> {{ order.notes }}
      </div>

      <!-- Action Buttons -->
      <div class="space-y-2 pt-2">
        <!-- Status Actions -->
        <div class="grid grid-cols-2 gap-2">
          <Button 
            v-if="order.status === 'pending'"
            size="sm"
            @click="updateStatus('confirmed')"
            :disabled="updating"
          >
            <CheckCircle class="h-3 w-3 mr-1" />
            Confirm
          </Button>
          
          <Button 
            v-if="order.status === 'ready'"
            size="sm"
            @click="updateStatus('completed')"
            :disabled="updating"
          >
            <Package class="h-3 w-3 mr-1" />
            Complete
          </Button>

          <!-- Payment Action -->
          <Button 
            v-if="order.payment_status !== 'paid'"
            variant="outline"
            size="sm"
            @click="processPayment"
            :disabled="updating"
          >
            <CreditCard class="h-3 w-3 mr-1" />
            Payment
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

        <!-- Cancel Button (if applicable) -->
        <Button 
          v-if="order.can_be_cancelled"
          variant="destructive"
          size="sm"
          class="w-full"
          @click="updateStatus('cancelled')"
          :disabled="updating"
        >
          <X class="h-3 w-3 mr-1" />
          Cancel Order
        </Button>
      </div>
    </CardContent>

    <!-- Quick Payment Modal -->
    <QuickPaymentModal 
      v-model:open="showPaymentModal"
      :order="order"
      @payment-processed="handlePaymentProcessed"
    />
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { 
  CheckCircle, 
  Package, 
  CreditCard, 
  Eye, 
  X 
} from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'
import QuickPaymentModal from './QuickPaymentModal.vue'

interface Props {
  order: AdminOrder
}

interface Emits {
  (e: 'status-updated'): void
  (e: 'payment-processed'): void
  (e: 'view-details', order: AdminOrder): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const adminStore = useAdminStore()

const updating = ref(false)
const showPaymentModal = ref(false)

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const getCardClasses = () => {
  const baseClasses = 'relative'
  
  switch (props.order.status) {
    case 'pending':
      return `${baseClasses} border-l-4 border-l-red-500`
    case 'confirmed':
      return `${baseClasses} border-l-4 border-l-blue-500`
    case 'preparing':
      return `${baseClasses} border-l-4 border-l-yellow-500`
    case 'ready':
      return `${baseClasses} border-l-4 border-l-green-500`
    case 'completed':
      return `${baseClasses} border-l-4 border-l-gray-500`
    case 'cancelled':
      return `${baseClasses} border-l-4 border-l-red-800 opacity-75`
    default:
      return baseClasses
  }
}

const getStatusVariant = (status: string) => {
  const variants: Record<string, string> = {
    pending: 'destructive',
    confirmed: 'default',
    preparing: 'secondary',
    ready: 'default',
    completed: 'outline',
    cancelled: 'destructive',
  }
  return variants[status] || 'secondary'
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    preparing: 'Preparing',
    ready: 'Ready',
    completed: 'Completed',
    cancelled: 'Cancelled',
  }
  return labels[status] || status
}

const getPaymentStatusVariant = (status: string) => {
  const variants: Record<string, string> = {
    pending: 'secondary',
    paid: 'default',
    failed: 'destructive',
    refunded: 'outline',
  }
  return variants[status] || 'secondary'
}

const getPaymentStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Pending',
    paid: 'Paid',
    failed: 'Failed',
    refunded: 'Refunded',
  }
  return labels[status] || status
}

const updateStatus = async (status: string) => {
  updating.value = true
  try {
    await adminStore.updateOrderStatus(props.order.id, status)
    emit('status-updated')
  } catch (error) {
    console.error('Error updating status:', error)
  } finally {
    updating.value = false
  }
}

const processPayment = () => {
  showPaymentModal.value = true
}

const handlePaymentProcessed = () => {
  showPaymentModal.value = false
  emit('payment-processed')
}
</script>
