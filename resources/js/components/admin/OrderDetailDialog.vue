<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle v-if="order">
          Order {{ order.order_number }}
        </DialogTitle>
        <DialogDescription>
          View and manage order details
        </DialogDescription>
      </DialogHeader>

      <div v-if="order" class="space-y-6">
        <!-- Order Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Order Information</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Order Number:</span>
                <span class="font-medium">{{ order.order_number }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Table Number:</span>
                <span class="font-medium">{{ order.table_number || 'Takeaway' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Status:</span>
                <Badge :variant="getStatusVariant(order.status)">
                  {{ getStatusLabel(order.status) }}
                </Badge>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Payment Status:</span>
                <Badge :variant="getPaymentStatusVariant(order.payment_status)">
                  {{ getPaymentStatusLabel(order.payment_status) }}
                </Badge>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Created:</span>
                <span class="font-medium">{{ order.created_at_human }}</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Customer Information</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Name:</span>
                <span class="font-medium">{{ order.customer_name || 'Guest' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Phone:</span>
                <span class="font-medium">{{ order.customer_phone || 'Not provided' }}</span>
              </div>
              <div v-if="order.notes">
                <span class="text-muted-foreground">Notes:</span>
                <p class="mt-1 text-sm">{{ order.notes }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Order Items -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Order Items</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div 
                v-for="item in order.order_items" 
                :key="item.id"
                class="flex items-center justify-between p-3 border rounded-lg"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 bg-muted rounded-lg flex items-center justify-center">
                    <UtensilsCrossed class="h-6 w-6 text-muted-foreground" />
                  </div>
                  <div>
                    <p class="font-medium">{{ item.menu?.name }}</p>
                    <p class="text-sm text-muted-foreground">
                      {{ item.quantity }}x {{ formatCurrency(item.price) }}
                    </p>
                    <p v-if="item.notes" class="text-xs text-muted-foreground">
                      Notes: {{ item.notes }}
                    </p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(item.subtotal) }}</p>
                </div>
              </div>
            </div>

            <Separator class="my-4" />

            <div class="flex justify-between items-center text-lg font-semibold">
              <span>Total:</span>
              <span>{{ order.formatted_total }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- Status Management -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Order Management</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
              <Button 
                variant="outline" 
                size="sm"
                :disabled="order.status !== 'pending' || updating"
                @click="updateStatus('confirmed')"
              >
                Confirm
              </Button>
              <Button 
                variant="outline" 
                size="sm"
                :disabled="order.status !== 'confirmed' || updating"
                @click="updateStatus('preparing')"
              >
                Start Preparing
              </Button>
              <Button 
                variant="outline" 
                size="sm"
                :disabled="order.status !== 'preparing' || updating"
                @click="updateStatus('ready')"
              >
                Mark Ready
              </Button>
              <Button 
                variant="outline" 
                size="sm"
                :disabled="order.status !== 'ready' || updating"
                @click="updateStatus('completed')"
              >
                Complete
              </Button>
            </div>

            <div class="mt-4 pt-4 border-t">
              <Button 
                variant="destructive" 
                size="sm"
                :disabled="['completed', 'cancelled'].includes(order.status) || updating"
                @click="updateStatus('cancelled')"
              >
                Cancel Order
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Payment Management -->
        <Card v-if="order.payment_status !== 'paid'">
          <CardHeader>
            <CardTitle class="text-lg">Payment Processing</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="payment_method">Payment Method</Label>
                  <Select v-model="paymentForm.method">
                    <SelectTrigger>
                      <SelectValue placeholder="Select payment method" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="cash">Cash</SelectItem>
                      <SelectItem value="qris">QRIS</SelectItem>
                      <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                      <SelectItem value="e_wallet">E-Wallet</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-2">
                  <Label for="payment_amount">Amount</Label>
                  <Input 
                    id="payment_amount"
                    v-model="paymentForm.amount"
                    type="number"
                    :placeholder="order.total_amount.toString()"
                  />
                </div>
              </div>

              <div v-if="paymentForm.method !== 'cash'" class="space-y-2">
                <Label for="transaction_id">Transaction ID</Label>
                <Input 
                  id="transaction_id"
                  v-model="paymentForm.transaction_id"
                  placeholder="Enter transaction ID"
                />
              </div>

              <div class="space-y-2">
                <Label for="payment_notes">Notes (Optional)</Label>
                <Textarea 
                  id="payment_notes"
                  v-model="paymentForm.notes"
                  placeholder="Payment notes..."
                  rows="2"
                />
              </div>

              <Button 
                @click="processPayment"
                :disabled="!paymentForm.method || !paymentForm.amount || processing"
                class="w-full"
              >
                <Loader2 v-if="processing" class="h-4 w-4 mr-2 animate-spin" />
                Process Payment
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="$emit('update:open', false)">
          Close
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { 
  Dialog, 
  DialogContent, 
  DialogDescription, 
  DialogFooter, 
  DialogHeader, 
  DialogTitle 
} from '@/components/ui/dialog'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import { Separator } from '@/components/ui/separator'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { UtensilsCrossed, Loader2 } from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'

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
const processing = ref(false)

const paymentForm = ref({
  method: '',
  amount: '',
  transaction_id: '',
  notes: '',
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const getStatusVariant = (status: string) => {
  const variants: Record<string, string> = {
    pending: 'secondary',
    confirmed: 'default',
    preparing: 'default',
    ready: 'default',
    completed: 'default',
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
    refunded: 'secondary',
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

const processPayment = async () => {
  if (!props.order) return

  processing.value = true
  try {
    await adminStore.processPayment(props.order.id, {
      amount: parseFloat(paymentForm.value.amount),
      method: paymentForm.value.method,
      transaction_id: paymentForm.value.transaction_id || null,
      notes: paymentForm.value.notes || null,
    })
    
    // Reset form
    paymentForm.value = {
      method: '',
      amount: '',
      transaction_id: '',
      notes: '',
    }
    
    emit('payment-processed')
  } catch (error) {
    console.error('Error processing payment:', error)
  } finally {
    processing.value = false
  }
}

// Reset form when order changes
watch(() => props.order, (newOrder) => {
  if (newOrder) {
    paymentForm.value.amount = newOrder.total_amount.toString()
  }
})
</script>
