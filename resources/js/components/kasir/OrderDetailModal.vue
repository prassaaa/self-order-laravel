<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle v-if="order">
          Order {{ order.order_number }}
        </DialogTitle>
        <DialogDescription>
          Complete order details and management
        </DialogDescription>
      </DialogHeader>

      <div v-if="order" class="space-y-4">
        <!-- Order Status & Info -->
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Order Status</Label>
            <Badge :variant="getStatusVariant(order.status)" class="w-fit">
              {{ getStatusLabel(order.status) }}
            </Badge>
          </div>
          <div class="space-y-2">
            <Label>Payment Status</Label>
            <Badge :variant="getPaymentStatusVariant(order.payment_status)" class="w-fit">
              {{ getPaymentStatusLabel(order.payment_status) }}
            </Badge>
          </div>
        </div>

        <!-- Customer Info -->
        <div class="space-y-2">
          <Label>Customer Information</Label>
          <div class="bg-muted p-3 rounded-lg space-y-1">
            <div class="flex justify-between">
              <span class="text-sm text-muted-foreground">Name:</span>
              <span class="text-sm font-medium">{{ order.customer_name || 'Guest' }}</span>
            </div>
            <div v-if="order.customer_phone" class="flex justify-between">
              <span class="text-sm text-muted-foreground">Phone:</span>
              <span class="text-sm font-medium">{{ order.customer_phone }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-muted-foreground">Table:</span>
              <span class="text-sm font-medium">{{ order.table_number || 'Takeaway' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-muted-foreground">Time:</span>
              <span class="text-sm font-medium">{{ order.created_at_human }}</span>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="space-y-2">
          <Label>Order Items</Label>
          <div class="space-y-2">
            <div 
              v-for="item in order.order_items" 
              :key="item.id"
              class="flex items-center justify-between p-3 border rounded-lg"
            >
              <div class="flex-1">
                <p class="font-medium">{{ item.menu?.name }}</p>
                <p class="text-sm text-muted-foreground">
                  {{ item.quantity }}x {{ formatCurrency(item.price) }}
                </p>
                <p v-if="item.notes" class="text-xs text-muted-foreground mt-1">
                  Notes: {{ item.notes }}
                </p>
              </div>
              <div class="text-right">
                <p class="font-medium">{{ formatCurrency(item.subtotal) }}</p>
              </div>
            </div>
          </div>
          
          <div class="flex justify-between items-center pt-2 border-t font-semibold">
            <span>Total:</span>
            <span class="text-lg">{{ order.formatted_total }}</span>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="order.notes" class="space-y-2">
          <Label>Order Notes</Label>
          <div class="bg-muted p-3 rounded-lg">
            <p class="text-sm">{{ order.notes }}</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-3">
          <Label>Quick Actions</Label>
          
          <!-- Status Actions -->
          <div class="grid grid-cols-2 gap-2">
            <Button 
              v-if="order.status === 'pending'"
              size="sm"
              @click="updateStatus('confirmed')"
              :disabled="updating"
            >
              <CheckCircle class="h-4 w-4 mr-2" />
              Confirm Order
            </Button>
            
            <Button 
              v-if="order.status === 'ready'"
              size="sm"
              @click="updateStatus('completed')"
              :disabled="updating"
            >
              <Package class="h-4 w-4 mr-2" />
              Complete Order
            </Button>

            <Button 
              v-if="order.payment_status !== 'paid'"
              variant="outline"
              size="sm"
              @click="showPaymentModal = true"
              :disabled="updating"
            >
              <CreditCard class="h-4 w-4 mr-2" />
              Process Payment
            </Button>

            <Button 
              variant="outline"
              size="sm"
              @click="printReceipt"
            >
              <Printer class="h-4 w-4 mr-2" />
              Print Receipt
            </Button>
          </div>

          <!-- Cancel Button -->
          <Button 
            v-if="order.can_be_cancelled"
            variant="destructive"
            size="sm"
            class="w-full"
            @click="updateStatus('cancelled')"
            :disabled="updating"
          >
            <X class="h-4 w-4 mr-2" />
            Cancel Order
          </Button>
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="$emit('update:open', false)">
          Close
        </Button>
      </DialogFooter>
    </DialogContent>

    <!-- Payment Modal -->
    <QuickPaymentModal 
      v-model:open="showPaymentModal"
      :order="order"
      @payment-processed="handlePaymentProcessed"
    />
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { 
  Dialog, 
  DialogContent, 
  DialogDescription, 
  DialogFooter, 
  DialogHeader, 
  DialogTitle 
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { 
  CheckCircle, 
  Package, 
  CreditCard, 
  Printer,
  X
} from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'
import QuickPaymentModal from './QuickPaymentModal.vue'

interface Props {
  open: boolean
  order: AdminOrder | null
}

interface Emits {
  (e: 'update:open', value: boolean): void
  (e: 'status-updated'): void
  (e: 'payment-processed'): void
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
  if (!props.order) return

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

const handlePaymentProcessed = () => {
  showPaymentModal.value = false
  emit('payment-processed')
}

const printReceipt = () => {
  window.print()
}
</script>
