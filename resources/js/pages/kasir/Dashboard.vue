<template>
  <KasirLayout>
    <div class="space-y-6">
      <!-- Welcome Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Dashboard Kasir</h1>
          <p class="text-muted-foreground">
            Selamat datang di dashboard kasir. Kelola pesanan dan pembayaran dengan mudah.
          </p>
        </div>
        <Button @click="refreshData" :disabled="loading">
          <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': loading }" />
          Refresh Data
        </Button>
      </div>

      <!-- Quick Stats -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Clock class="h-8 w-8 text-orange-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Pesanan Pending</p>
                <div class="flex items-center">
                  <p class="text-2xl font-bold">{{ pendingOrdersCount }}</p>
                  <Badge v-if="pendingOrdersCount > 0" variant="destructive" class="ml-2">
                    Urgent
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <ChefHat class="h-8 w-8 text-blue-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Sedang Dimasak</p>
                <p class="text-2xl font-bold">{{ preparingOrdersCount }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <Package class="h-8 w-8 text-green-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Siap Disajikan</p>
                <p class="text-2xl font-bold">{{ readyOrdersCount }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <CreditCard class="h-8 w-8 text-purple-600" />
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold">{{ formatCurrency(todayRevenue) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <ShoppingBag class="h-5 w-5 mr-2" />
              Kelola Pesanan
            </CardTitle>
            <CardDescription>
              Lihat dan kelola semua pesanan yang masuk
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button 
              class="w-full" 
              @click="router.visit('/kasir/orders')"
            >
              Lihat Pesanan
              <Badge v-if="pendingOrdersCount > 0" variant="destructive" class="ml-2">
                {{ pendingOrdersCount }}
              </Badge>
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <ChefHat class="h-5 w-5 mr-2" />
              Dapur
            </CardTitle>
            <CardDescription>
              Monitor status pesanan di dapur
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button 
              class="w-full" 
              variant="outline"
              @click="router.visit('/kasir/kitchen')"
            >
              Buka Dapur
              <Badge v-if="preparingOrdersCount > 0" variant="secondary" class="ml-2">
                {{ preparingOrdersCount }}
              </Badge>
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="flex items-center">
              <CreditCard class="h-5 w-5 mr-2" />
              Pembayaran
            </CardTitle>
            <CardDescription>
              Proses pembayaran dan transaksi
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button 
              class="w-full" 
              variant="outline"
              @click="router.visit('/kasir/payments')"
            >
              Kelola Pembayaran
            </Button>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Orders -->
      <Card>
        <CardHeader>
          <CardTitle>Pesanan Terbaru</CardTitle>
          <CardDescription>
            Pesanan yang baru masuk dan memerlukan perhatian
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="flex items-center space-x-4">
              <Skeleton class="h-12 w-12 rounded-full" />
              <div class="space-y-2">
                <Skeleton class="h-4 w-[200px]" />
                <Skeleton class="h-4 w-[100px]" />
              </div>
            </div>
          </div>

          <div v-else-if="recentOrders.length === 0" class="text-center py-8">
            <ShoppingBag class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <h3 class="text-lg font-semibold mb-2">Belum Ada Pesanan</h3>
            <p class="text-muted-foreground">
              Pesanan baru akan muncul di sini
            </p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="order in recentOrders" 
              :key="order.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
            >
              <div class="flex items-center space-x-4">
                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                  <span class="text-sm font-medium">#{{ order.order_number.slice(-3) }}</span>
                </div>
                <div>
                  <p class="font-medium">Pesanan #{{ order.order_number }}</p>
                  <p class="text-sm text-muted-foreground">
                    Meja {{ order.table_number }} • {{ formatCurrency(order.total_amount) }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <Badge :variant="getStatusVariant(order.status)">
                  {{ getStatusLabel(order.status) }}
                </Badge>
                <Button 
                  size="sm" 
                  variant="outline"
                  @click="viewOrderDetail(order)"
                >
                  <Eye class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </KasirLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import KasirLayout from '@/layouts/KasirLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { 
  RefreshCw, 
  Clock, 
  ChefHat, 
  Package, 
  CreditCard,
  ShoppingBag,
  Eye
} from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'

const adminStore = useAdminStore()
const loading = ref(false)

// Computed properties untuk stats
const pendingOrdersCount = computed(() => adminStore.pendingOrders.length)
const preparingOrdersCount = computed(() => adminStore.preparingOrders.length)
const readyOrdersCount = computed(() => adminStore.readyOrders.length)
const todayRevenue = computed(() => adminStore.dashboardStats?.today_revenue || 0)

// Recent orders (ambil 5 pesanan terbaru)
const recentOrders = computed(() => {
  return adminStore.orders
    .filter(order => ['pending', 'confirmed', 'preparing'].includes(order.status))
    .slice(0, 5)
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
    pending: 'destructive',
    confirmed: 'default',
    preparing: 'secondary',
    ready: 'default',
    completed: 'default',
    cancelled: 'destructive',
  }
  return variants[status] || 'secondary'
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Pending',
    confirmed: 'Dikonfirmasi',
    preparing: 'Dimasak',
    ready: 'Siap',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  }
  return labels[status] || status
}

const refreshData = async () => {
  loading.value = true
  try {
    await adminStore.fetchDashboardStats()
    await adminStore.fetchOrders()
  } finally {
    loading.value = false
  }
}

const viewOrderDetail = (order: AdminOrder) => {
  router.visit(`/admin/orders/${order.id}`)
}

onMounted(() => {
  refreshData()
})
</script>
