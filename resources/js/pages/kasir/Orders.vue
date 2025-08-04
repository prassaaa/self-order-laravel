<template>
  <KasirLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Order Management</h1>
          <p class="text-muted-foreground">
            Process customer orders and manage payments
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" @click="refreshOrders">
            <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': adminStore.loading }" />
            Refresh
          </Button>
        </div>
      </div>

      <!-- Quick Filters -->
      <div class="flex flex-wrap gap-2">
        <Button 
          variant="outline" 
          size="sm"
          :class="{ 'bg-primary text-primary-foreground': activeFilter === 'all' }"
          @click="setFilter('all')"
        >
          All Orders
          <Badge variant="secondary" class="ml-2">{{ allOrdersCount }}</Badge>
        </Button>
        <Button 
          variant="outline" 
          size="sm"
          :class="{ 'bg-primary text-primary-foreground': activeFilter === 'pending' }"
          @click="setFilter('pending')"
        >
          Pending
          <Badge variant="destructive" class="ml-2">{{ pendingOrdersCount }}</Badge>
        </Button>
        <Button 
          variant="outline" 
          size="sm"
          :class="{ 'bg-primary text-primary-foreground': activeFilter === 'confirmed' }"
          @click="setFilter('confirmed')"
        >
          Confirmed
          <Badge variant="secondary" class="ml-2">{{ confirmedOrdersCount }}</Badge>
        </Button>
        <Button 
          variant="outline" 
          size="sm"
          :class="{ 'bg-primary text-primary-foreground': activeFilter === 'ready' }"
          @click="setFilter('ready')"
        >
          Ready
          <Badge variant="default" class="ml-2">{{ readyOrdersCount }}</Badge>
        </Button>
      </div>

      <!-- Orders Grid -->
      <div v-if="adminStore.loading && filteredOrders.length === 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="i in 6" :key="i">
          <CardContent class="p-6">
            <Skeleton class="h-4 w-[100px] mb-2" />
            <Skeleton class="h-8 w-[60px] mb-2" />
            <Skeleton class="h-3 w-[80px]" />
          </CardContent>
        </Card>
      </div>

      <div v-else-if="filteredOrders.length === 0" class="text-center py-12">
        <ShoppingBag class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">No Orders Found</h3>
        <p class="text-muted-foreground">
          {{ activeFilter === 'all' ? 'No orders have been placed yet' : `No ${activeFilter} orders found` }}
        </p>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <OrderCard 
          v-for="order in filteredOrders" 
          :key="order.id"
          :order="order"
          @status-updated="handleStatusUpdate"
          @payment-processed="handlePaymentProcessed"
          @view-details="viewOrderDetails"
        />
      </div>

      <!-- Pagination -->
      <div v-if="filteredOrders.length > 0" class="flex items-center justify-center space-x-2">
        <Button variant="outline" size="sm" :disabled="currentPage === 1" @click="previousPage">
          Previous
        </Button>
        <span class="text-sm text-muted-foreground">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <Button variant="outline" size="sm" :disabled="currentPage === totalPages" @click="nextPage">
          Next
        </Button>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <OrderDetailModal 
      v-model:open="showOrderDetail"
      :order="selectedOrder"
      @status-updated="handleStatusUpdate"
      @payment-processed="handlePaymentProcessed"
    />
  </KasirLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import KasirLayout from '@/layouts/KasirLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { RefreshCw, ShoppingBag } from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'
import OrderCard from '@/components/kasir/OrderCard.vue'
import OrderDetailModal from '@/components/kasir/OrderDetailModal.vue'

const adminStore = useAdminStore()

const activeFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = 12
const showOrderDetail = ref(false)
const selectedOrder = ref<AdminOrder | null>(null)

const allOrders = computed(() => adminStore.orders)

const filteredOrders = computed(() => {
  let orders = allOrders.value

  if (activeFilter.value !== 'all') {
    orders = orders.filter(order => order.status === activeFilter.value)
  }

  // Sort by priority: pending first, then by created date
  orders = orders.sort((a, b) => {
    const priorityOrder = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled']
    const aPriority = priorityOrder.indexOf(a.status)
    const bPriority = priorityOrder.indexOf(b.status)
    
    if (aPriority !== bPriority) {
      return aPriority - bPriority
    }
    
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  })

  // Pagination
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return orders.slice(start, end)
})

const totalPages = computed(() => {
  const totalItems = activeFilter.value === 'all' 
    ? allOrders.value.length 
    : allOrders.value.filter(order => order.status === activeFilter.value).length
  return Math.ceil(totalItems / itemsPerPage)
})

// Counts for badges
const allOrdersCount = computed(() => allOrders.value.length)
const pendingOrdersCount = computed(() => allOrders.value.filter(order => order.status === 'pending').length)
const confirmedOrdersCount = computed(() => allOrders.value.filter(order => order.status === 'confirmed').length)
const readyOrdersCount = computed(() => allOrders.value.filter(order => order.status === 'ready').length)

const setFilter = (filter: string) => {
  activeFilter.value = filter
  currentPage.value = 1
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const refreshOrders = async () => {
  await adminStore.fetchOrders()
}

const handleStatusUpdate = async () => {
  await adminStore.fetchOrders()
}

const handlePaymentProcessed = async () => {
  await adminStore.fetchOrders()
}

const viewOrderDetails = (order: AdminOrder) => {
  selectedOrder.value = order
  showOrderDetail.value = true
}

let updateInterval: number | null = null

onMounted(async () => {
  await adminStore.fetchOrders()
  
  // Setup auto-refresh every 15 seconds
  updateInterval = window.setInterval(() => {
    adminStore.fetchOrders()
  }, 15000)
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>
