<template>
  <KasirLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Kitchen Display</h1>
          <p class="text-muted-foreground">
            Monitor and manage order preparation
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" @click="refreshOrders">
            <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': adminStore.loading }" />
            Refresh
          </Button>
        </div>
      </div>

      <!-- Kitchen Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ confirmedOrdersCount }}</div>
            <div class="text-sm text-muted-foreground">Confirmed</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ preparingOrdersCount }}</div>
            <div class="text-sm text-muted-foreground">Preparing</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ readyOrdersCount }}</div>
            <div class="text-sm text-muted-foreground">Ready</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold">{{ averageWaitTime }}m</div>
            <div class="text-sm text-muted-foreground">Avg Wait Time</div>
          </CardContent>
        </Card>
      </div>

      <!-- Kitchen Workflow -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Confirmed Orders (To Start) -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-blue-600">To Start</h2>
            <Badge variant="secondary">{{ confirmedOrders.length }}</Badge>
          </div>
          
          <div class="space-y-3 max-h-[600px] overflow-y-auto">
            <KitchenOrderCard 
              v-for="order in confirmedOrders" 
              :key="order.id"
              :order="order"
              status-color="blue"
              @start-preparing="startPreparing"
              @view-details="viewOrderDetails"
            />
            
            <div v-if="confirmedOrders.length === 0" class="text-center py-8 text-muted-foreground">
              <ChefHat class="h-8 w-8 mx-auto mb-2" />
              <p>No orders to start</p>
            </div>
          </div>
        </div>

        <!-- Preparing Orders (In Progress) -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-yellow-600">Preparing</h2>
            <Badge variant="secondary">{{ preparingOrders.length }}</Badge>
          </div>
          
          <div class="space-y-3 max-h-[600px] overflow-y-auto">
            <KitchenOrderCard 
              v-for="order in preparingOrders" 
              :key="order.id"
              :order="order"
              status-color="yellow"
              @mark-ready="markReady"
              @view-details="viewOrderDetails"
            />
            
            <div v-if="preparingOrders.length === 0" class="text-center py-8 text-muted-foreground">
              <Clock class="h-8 w-8 mx-auto mb-2" />
              <p>No orders in preparation</p>
            </div>
          </div>
        </div>

        <!-- Ready Orders (To Serve) -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-green-600">Ready to Serve</h2>
            <Badge variant="secondary">{{ readyOrders.length }}</Badge>
          </div>
          
          <div class="space-y-3 max-h-[600px] overflow-y-auto">
            <KitchenOrderCard 
              v-for="order in readyOrders" 
              :key="order.id"
              :order="order"
              status-color="green"
              @complete-order="completeOrder"
              @view-details="viewOrderDetails"
            />
            
            <div v-if="readyOrders.length === 0" class="text-center py-8 text-muted-foreground">
              <Package class="h-8 w-8 mx-auto mb-2" />
              <p>No orders ready</p>
            </div>
          </div>
        </div>
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
import { RefreshCw, ChefHat, Clock, Package } from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'
import KitchenOrderCard from '@/components/kasir/KitchenOrderCard.vue'
import OrderDetailModal from '@/components/kasir/OrderDetailModal.vue'

const adminStore = useAdminStore()

const showOrderDetail = ref(false)
const selectedOrder = ref<AdminOrder | null>(null)

const allOrders = computed(() => adminStore.orders)

const confirmedOrders = computed(() => {
  return allOrders.value
    .filter(order => order.status === 'confirmed')
    .sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
})

const preparingOrders = computed(() => {
  return allOrders.value
    .filter(order => order.status === 'preparing')
    .sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
})

const readyOrders = computed(() => {
  return allOrders.value
    .filter(order => order.status === 'ready')
    .sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
})

// Stats
const confirmedOrdersCount = computed(() => confirmedOrders.value.length)
const preparingOrdersCount = computed(() => preparingOrders.value.length)
const readyOrdersCount = computed(() => readyOrders.value.length)

const averageWaitTime = computed(() => {
  const activeOrders = [...preparingOrders.value, ...readyOrders.value]
  if (activeOrders.length === 0) return 0
  
  const totalMinutes = activeOrders.reduce((total, order) => {
    const orderTime = new Date(order.created_at).getTime()
    const now = new Date().getTime()
    const minutes = Math.floor((now - orderTime) / (1000 * 60))
    return total + minutes
  }, 0)
  
  return Math.round(totalMinutes / activeOrders.length)
})

const refreshOrders = async () => {
  await adminStore.fetchOrders()
}

const startPreparing = async (order: AdminOrder) => {
  try {
    await adminStore.updateOrderStatus(order.id, 'preparing')
  } catch (error) {
    console.error('Error starting preparation:', error)
  }
}

const markReady = async (order: AdminOrder) => {
  try {
    await adminStore.updateOrderStatus(order.id, 'ready')
  } catch (error) {
    console.error('Error marking ready:', error)
  }
}

const completeOrder = async (order: AdminOrder) => {
  try {
    await adminStore.updateOrderStatus(order.id, 'completed')
  } catch (error) {
    console.error('Error completing order:', error)
  }
}

const viewOrderDetails = (order: AdminOrder) => {
  selectedOrder.value = order
  showOrderDetail.value = true
}

const handleStatusUpdate = async () => {
  await adminStore.fetchOrders()
}

const handlePaymentProcessed = async () => {
  await adminStore.fetchOrders()
}

let updateInterval: number | null = null

onMounted(async () => {
  await adminStore.fetchOrders()
  
  // Setup auto-refresh every 10 seconds for kitchen (more frequent)
  updateInterval = window.setInterval(() => {
    adminStore.fetchOrders()
  }, 10000)
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>
