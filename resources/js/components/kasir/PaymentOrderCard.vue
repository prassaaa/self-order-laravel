<template>
  <Card class="hover:shadow-md transition-shadow border-l-4 border-l-red-500">
    <CardContent class="p-4">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <div class="flex items-center space-x-3">
            <div>
              <h3 class="font-semibold">{{ order.order_number }}</h3>
              <p class="text-sm text-muted-foreground">
                {{ order.customer_name || 'Guest' }}
                <span v-if="order.table_number"> • Table {{ order.table_number }}</span>
              </p>
              <p class="text-xs text-muted-foreground">{{ order.created_at_human }}</p>
            </div>
          </div>
        </div>

        <div class="text-right">
          <div class="text-lg font-bold">{{ order.formatted_total }}</div>
          <div class="text-sm text-muted-foreground">{{ order.order_items.length }} items</div>
        </div>

        <div class="ml-4 flex space-x-2">
          <Button 
            size="sm"
            @click="$emit('payment-processed')"
            @click.stop="processPayment"
          >
            <CreditCard class="h-4 w-4 mr-1" />
            Pay
          </Button>
          <Button 
            variant="outline"
            size="sm"
            @click="$emit('view-details', order)"
          >
            <Eye class="h-4 w-4" />
          </Button>
        </div>
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
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { CreditCard, Eye } from 'lucide-vue-next'
import { type AdminOrder } from '@/stores/admin'
import QuickPaymentModal from './QuickPaymentModal.vue'

interface Props {
  order: AdminOrder
}

interface Emits {
  (e: 'payment-processed'): void
  (e: 'view-details', order: AdminOrder): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const showPaymentModal = ref(false)

const processPayment = () => {
  showPaymentModal.value = true
}

const handlePaymentProcessed = () => {
  showPaymentModal.value = false
  emit('payment-processed')
}
</script>
