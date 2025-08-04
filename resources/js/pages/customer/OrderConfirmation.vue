<template>
  <CustomerLayout>
    <div class="container mx-auto px-4 py-8 max-w-2xl">
      <!-- Loading State -->
      <div v-if="orderStore.loading" class="text-center py-12">
        <Loader2 class="h-12 w-12 mx-auto animate-spin text-primary mb-4" />
        <p class="text-muted-foreground">Memuat detail pesanan...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="orderStore.error" class="text-center py-12">
        <AlertCircle class="h-12 w-12 mx-auto text-destructive mb-4" />
        <h2 class="text-2xl font-semibold mb-2">Pesanan Tidak Ditemukan</h2>
        <p class="text-muted-foreground mb-6">{{ orderStore.error }}</p>
        <Button @click="router.visit('/menu')">
          <UtensilsCrossed class="h-4 w-4 mr-2" />
          Kembali ke Menu
        </Button>
      </div>

      <!-- Order Details -->
      <div v-else-if="order" class="space-y-6">
        <!-- Success Header -->
        <div class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircle class="h-8 w-8 text-green-600" />
          </div>
          <h1 class="text-3xl font-bold mb-2">Pesanan Berhasil!</h1>
          <p class="text-muted-foreground">
            Terima kasih atas pesanan Anda. Berikut adalah detail pesanan:
          </p>
        </div>

        <!-- Order Info Card -->
        <Card>
          <CardHeader>
            <div class="flex justify-between items-start">
              <div>
                <CardTitle class="text-xl">{{ order.order_number }}</CardTitle>
                <CardDescription>{{ order.created_at_human }}</CardDescription>
              </div>
              <Badge 
                :variant="getStatusVariant(order.status)"
                class="text-sm"
              >
                {{ orderStore.orderStatusLabel }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Customer Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <p class="font-medium text-muted-foreground">Nama Pelanggan</p>
                <p>{{ order.customer_name || 'Tidak ada' }}</p>
              </div>
              <div>
                <p class="font-medium text-muted-foreground">Nomor Telepon</p>
                <p>{{ order.customer_phone || 'Tidak ada' }}</p>
              </div>
              <div>
                <p class="font-medium text-muted-foreground">Nomor Meja</p>
                <p>{{ order.table_number || 'Takeaway' }}</p>
              </div>
              <div>
                <p class="font-medium text-muted-foreground">Status Pembayaran</p>
                <Badge :variant="getPaymentStatusVariant(order.payment_status)">
                  {{ orderStore.paymentStatusLabel }}
                </Badge>
              </div>
            </div>

            <div v-if="order.notes">
              <p class="font-medium text-muted-foreground text-sm">Catatan</p>
              <p class="text-sm">{{ order.notes }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Order Items -->
        <Card>
          <CardHeader>
            <CardTitle>Detail Pesanan</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div 
                v-for="item in order.order_items" 
                :key="item.id"
                class="flex justify-between items-start"
              >
                <div class="flex-1">
                  <p class="font-medium">{{ item.menu?.name }}</p>
                  <p class="text-sm text-muted-foreground">
                    {{ item.quantity }}x {{ formatCurrency(item.price) }}
                  </p>
                  <p v-if="item.notes" class="text-xs text-muted-foreground">
                    Catatan: {{ item.notes }}
                  </p>
                </div>
                <p class="font-medium">
                  {{ formatCurrency(item.subtotal) }}
                </p>
              </div>
            </div>

            <Separator class="my-4" />

            <div class="flex justify-between items-center text-lg font-semibold">
              <span>Total:</span>
              <span>{{ order.formatted_total }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- Order Status Tracking -->
        <Card>
          <CardHeader>
            <CardTitle>Status Pesanan</CardTitle>
            <CardDescription>
              Pantau progress pesanan Anda secara real-time
            </CardDescription>
          </CardHeader>
          <CardContent>
            <OrderStatusTracker :status="order.status" />
          </CardContent>
        </Card>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4">
          <Button 
            variant="outline" 
            class="flex-1"
            @click="printReceipt"
          >
            <Printer class="h-4 w-4 mr-2" />
            Cetak Struk
          </Button>
          
          <Button 
            v-if="orderStore.canTrackOrder"
            variant="outline" 
            class="flex-1"
            @click="trackOrder"
            :disabled="tracking"
          >
            <RefreshCw 
              class="h-4 w-4 mr-2" 
              :class="{ 'animate-spin': tracking }"
            />
            {{ tracking ? 'Memperbarui...' : 'Perbarui Status' }}
          </Button>

          <Button 
            class="flex-1"
            @click="router.visit('/menu')"
          >
            <UtensilsCrossed class="h-4 w-4 mr-2" />
            Pesan Lagi
          </Button>
        </div>

        <!-- Payment Instructions -->
        <Card v-if="order.payment_status === 'pending'" class="border-orange-200 bg-orange-50">
          <CardContent class="pt-6">
            <div class="flex items-start space-x-3">
              <AlertTriangle class="h-5 w-5 text-orange-600 mt-0.5" />
              <div>
                <h3 class="font-semibold text-orange-800">Menunggu Pembayaran</h3>
                <p class="text-sm text-orange-700 mt-1">
                  Silakan lakukan pembayaran di kasir untuk memproses pesanan Anda.
                  Tunjukkan nomor pesanan: <strong>{{ order.order_number }}</strong>
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/CustomerLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { 
  CheckCircle, 
  AlertCircle, 
  AlertTriangle,
  UtensilsCrossed, 
  Printer, 
  RefreshCw,
  Loader2
} from 'lucide-vue-next'
import { useOrderStore } from '@/stores/order'
import OrderStatusTracker from '@/components/customer/OrderStatusTracker.vue'

interface Props {
  orderId: number
}

const props = defineProps<Props>()
const orderStore = useOrderStore()

const tracking = ref(false)
const trackingInterval = ref<number | null>(null)

const order = computed(() => orderStore.currentOrder)

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

const getPaymentStatusVariant = (status: string) => {
  const variants: Record<string, string> = {
    pending: 'secondary',
    paid: 'default',
    failed: 'destructive',
    refunded: 'secondary',
  }
  return variants[status] || 'secondary'
}

const trackOrder = async () => {
  tracking.value = true
  try {
    await orderStore.trackOrder()
  } catch (error) {
    console.error('Error tracking order:', error)
  } finally {
    tracking.value = false
  }
}

const printReceipt = () => {
  window.print()
}

const setupAutoTracking = () => {
  if (orderStore.canTrackOrder) {
    trackingInterval.value = window.setInterval(async () => {
      await orderStore.trackOrder()
    }, 30000) // Update every 30 seconds
  }
}

onMounted(async () => {
  try {
    await orderStore.fetchOrder(props.orderId)
    setupAutoTracking()
  } catch (error) {
    console.error('Error loading order:', error)
  }
})

onUnmounted(() => {
  if (trackingInterval.value) {
    clearInterval(trackingInterval.value)
  }
})
</script>

<style>
@media print {
  .no-print {
    display: none !important;
  }
  
  body {
    font-size: 12px;
  }
  
  .container {
    max-width: none;
    padding: 0;
  }
}
</style>
