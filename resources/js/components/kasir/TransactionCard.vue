<template>
  <Card>
    <CardContent class="p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="getMethodClasses()">
            <component :is="getMethodIcon()" class="h-5 w-5" />
          </div>
          <div>
            <h3 class="font-medium">{{ transaction.order_number }}</h3>
            <p class="text-sm text-muted-foreground">
              {{ transaction.customer_name || 'Guest' }}
            </p>
            <p class="text-xs text-muted-foreground">
              {{ formatTime(transaction.processed_at) }}
            </p>
          </div>
        </div>

        <div class="text-right">
          <div class="font-semibold">{{ formatCurrency(transaction.amount) }}</div>
          <div class="flex items-center space-x-2">
            <Badge :variant="getStatusVariant()" class="text-xs">
              {{ getMethodLabel() }}
            </Badge>
            <Badge :variant="getStatusVariant()" class="text-xs">
              {{ transaction.status }}
            </Badge>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { 
  Banknote, 
  Smartphone, 
  Building2, 
  CreditCard 
} from 'lucide-vue-next'

interface Transaction {
  id: number
  order_number: string
  amount: number
  method: string
  status: string
  processed_at: string
  customer_name?: string
}

interface Props {
  transaction: Transaction
}

const props = defineProps<Props>()

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getMethodIcon = () => {
  const icons: Record<string, any> = {
    cash: Banknote,
    qris: Smartphone,
    bank_transfer: Building2,
    e_wallet: Smartphone,
  }
  return icons[props.transaction.method] || CreditCard
}

const getMethodLabel = () => {
  const labels: Record<string, string> = {
    cash: 'Cash',
    qris: 'QRIS',
    bank_transfer: 'Transfer',
    e_wallet: 'E-Wallet',
  }
  return labels[props.transaction.method] || props.transaction.method
}

const getMethodClasses = () => {
  const classes: Record<string, string> = {
    cash: 'bg-green-100 text-green-600',
    qris: 'bg-blue-100 text-blue-600',
    bank_transfer: 'bg-purple-100 text-purple-600',
    e_wallet: 'bg-orange-100 text-orange-600',
  }
  return classes[props.transaction.method] || 'bg-gray-100 text-gray-600'
}

const getStatusVariant = () => {
  const variants: Record<string, string> = {
    completed: 'default',
    pending: 'secondary',
    failed: 'destructive',
    refunded: 'outline',
  }
  return variants[props.transaction.status] || 'secondary'
}
</script>
