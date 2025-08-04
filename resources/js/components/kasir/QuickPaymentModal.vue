<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Process Payment</DialogTitle>
        <DialogDescription>
          Order {{ order?.order_number }} - {{ order?.formatted_total }}
        </DialogDescription>
      </DialogHeader>

      <div v-if="order" class="space-y-4">
        <!-- Payment Method Selection -->
        <div class="space-y-3">
          <Label>Payment Method</Label>
          <div class="grid grid-cols-2 gap-2">
            <Button 
              v-for="method in paymentMethods" 
              :key="method.value"
              variant="outline"
              class="h-16 flex-col space-y-1"
              :class="{ 'border-primary bg-primary/5': paymentForm.method === method.value }"
              @click="selectPaymentMethod(method.value)"
            >
              <component :is="method.icon" class="h-5 w-5" />
              <span class="text-xs">{{ method.label }}</span>
            </Button>
          </div>
        </div>

        <!-- Amount Input -->
        <div class="space-y-2">
          <Label for="amount">Amount</Label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-sm text-muted-foreground">
              Rp
            </span>
            <Input 
              id="amount"
              v-model="paymentForm.amount"
              type="number"
              class="pl-8"
              :placeholder="order.total_amount.toString()"
            />
          </div>
          <div class="flex gap-2">
            <Button 
              variant="outline" 
              size="sm"
              @click="setExactAmount"
            >
              Exact
            </Button>
            <Button 
              variant="outline" 
              size="sm"
              @click="setAmount(order.total_amount + 5000)"
            >
              +5K
            </Button>
            <Button 
              variant="outline" 
              size="sm"
              @click="setAmount(order.total_amount + 10000)"
            >
              +10K
            </Button>
          </div>
        </div>

        <!-- Transaction ID (for digital payments) -->
        <div v-if="needsTransactionId" class="space-y-2">
          <Label for="transaction_id">Transaction ID</Label>
          <Input 
            id="transaction_id"
            v-model="paymentForm.transaction_id"
            placeholder="Enter transaction ID"
          />
        </div>

        <!-- Change Calculation (for cash) -->
        <div v-if="paymentForm.method === 'cash' && paymentAmount > 0" class="space-y-2">
          <div class="flex justify-between items-center p-3 bg-muted rounded-lg">
            <span class="text-sm">Change:</span>
            <span class="font-bold text-lg" :class="changeAmount >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatCurrency(changeAmount) }}
            </span>
          </div>
        </div>

        <!-- Notes -->
        <div class="space-y-2">
          <Label for="notes">Notes (Optional)</Label>
          <Textarea 
            id="notes"
            v-model="paymentForm.notes"
            placeholder="Payment notes..."
            rows="2"
          />
        </div>
      </div>

      <DialogFooter class="gap-2">
        <Button 
          variant="outline" 
          @click="$emit('update:open', false)"
        >
          Cancel
        </Button>
        <Button 
          @click="processPayment"
          :disabled="!canProcessPayment || processing"
        >
          <Loader2 v-if="processing" class="h-4 w-4 mr-2 animate-spin" />
          <CreditCard v-else class="h-4 w-4 mr-2" />
          Process Payment
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { 
  Dialog, 
  DialogContent, 
  DialogDescription, 
  DialogFooter, 
  DialogHeader, 
  DialogTitle 
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { 
  Banknote, 
  Smartphone, 
  Building2, 
  CreditCard,
  Loader2
} from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'

interface Props {
  open: boolean
  order: AdminOrder | null
}

interface Emits {
  (e: 'update:open', value: boolean): void
  (e: 'payment-processed'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const adminStore = useAdminStore()

const processing = ref(false)

const paymentForm = ref({
  method: 'cash',
  amount: '',
  transaction_id: '',
  notes: '',
})

const paymentMethods = [
  {
    value: 'cash',
    label: 'Cash',
    icon: Banknote,
  },
  {
    value: 'qris',
    label: 'QRIS',
    icon: Smartphone,
  },
  {
    value: 'bank_transfer',
    label: 'Transfer',
    icon: Building2,
  },
  {
    value: 'e_wallet',
    label: 'E-Wallet',
    icon: Smartphone,
  },
]

const paymentAmount = computed(() => parseFloat(paymentForm.value.amount) || 0)

const changeAmount = computed(() => {
  if (!props.order || paymentForm.value.method !== 'cash') return 0
  return paymentAmount.value - props.order.total_amount
})

const needsTransactionId = computed(() => {
  return ['qris', 'bank_transfer', 'e_wallet'].includes(paymentForm.value.method)
})

const canProcessPayment = computed(() => {
  if (!props.order || !paymentForm.value.method || paymentAmount.value <= 0) return false
  
  if (needsTransactionId.value && !paymentForm.value.transaction_id.trim()) return false
  
  if (paymentForm.value.method === 'cash' && paymentAmount.value < props.order.total_amount) return false
  
  return true
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const selectPaymentMethod = (method: string) => {
  paymentForm.value.method = method
  
  // Clear transaction ID when switching to cash
  if (method === 'cash') {
    paymentForm.value.transaction_id = ''
  }
}

const setExactAmount = () => {
  if (props.order) {
    paymentForm.value.amount = props.order.total_amount.toString()
  }
}

const setAmount = (amount: number) => {
  paymentForm.value.amount = amount.toString()
}

const processPayment = async () => {
  if (!props.order || !canProcessPayment.value) return

  processing.value = true
  try {
    await adminStore.processPayment(props.order.id, {
      amount: paymentAmount.value,
      method: paymentForm.value.method,
      transaction_id: paymentForm.value.transaction_id || null,
      notes: paymentForm.value.notes || null,
    })
    
    // Reset form
    resetForm()
    
    emit('payment-processed')
  } catch (error) {
    console.error('Error processing payment:', error)
  } finally {
    processing.value = false
  }
}

const resetForm = () => {
  paymentForm.value = {
    method: 'cash',
    amount: '',
    transaction_id: '',
    notes: '',
  }
}

// Reset form when order changes
watch(() => props.order, (newOrder) => {
  if (newOrder) {
    paymentForm.value.amount = newOrder.total_amount.toString()
  }
})

// Reset form when modal closes
watch(() => props.open, (isOpen) => {
  if (!isOpen) {
    resetForm()
  }
})
</script>
