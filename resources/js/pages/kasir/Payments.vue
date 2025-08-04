<template>
  <KasirLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Payment Management</h1>
          <p class="text-muted-foreground">
            Process payments and manage transactions
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" @click="refreshData">
            <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': loading }" />
            Refresh
          </Button>
        </div>
      </div>

      <!-- Payment Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold text-red-600">{{ pendingPaymentsCount }}</div>
            <div class="text-sm text-muted-foreground">Pending Payments</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ formatCurrency(todayRevenue) }}</div>
            <div class="text-sm text-muted-foreground">Today's Revenue</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold">{{ todayTransactions }}</div>
            <div class="text-sm text-muted-foreground">Today's Transactions</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold">{{ formatCurrency(averageOrderValue) }}</div>
            <div class="text-sm text-muted-foreground">Avg Order Value</div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Payment Section -->
      <Card>
        <CardHeader>
          <CardTitle>Quick Payment</CardTitle>
          <CardDescription>
            Process payment by order number
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="flex space-x-2">
            <Input 
              v-model="quickPaymentOrderNumber"
              placeholder="Enter order number (e.g., ORD-001)"
              class="flex-1"
              @keyup.enter="searchOrder"
            />
            <Button @click="searchOrder" :disabled="!quickPaymentOrderNumber.trim() || searching">
              <Search class="h-4 w-4 mr-2" />
              {{ searching ? 'Searching...' : 'Search' }}
            </Button>
          </div>
          
          <div v-if="searchError" class="mt-2 text-sm text-red-600">
            {{ searchError }}
          </div>
        </CardContent>
      </Card>

      <!-- Pending Payments -->
      <Card>
        <CardHeader>
          <CardTitle>Pending Payments</CardTitle>
          <CardDescription>
            Orders waiting for payment processing
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="loading" class="space-y-4">
            <Skeleton v-for="i in 3" :key="i" class="h-20 w-full" />
          </div>

          <div v-else-if="pendingPaymentOrders.length === 0" class="text-center py-8">
            <CreditCard class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <h3 class="text-lg font-semibold mb-2">No Pending Payments</h3>
            <p class="text-muted-foreground">All orders have been paid</p>
          </div>

          <div v-else class="space-y-3">
            <PaymentOrderCard 
              v-for="order in pendingPaymentOrders" 
              :key="order.id"
              :order="order"
              @payment-processed="handlePaymentProcessed"
              @view-details="viewOrderDetails"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Recent Transactions -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Recent Transactions</CardTitle>
              <CardDescription>
                Latest payment transactions
              </CardDescription>
            </div>
            <Button variant="outline" size="sm" @click="exportTransactions">
              <Download class="h-4 w-4 mr-2" />
              Export
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="recentTransactions.length === 0" class="text-center py-8">
            <FileText class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <p class="text-muted-foreground">No recent transactions</p>
          </div>

          <div v-else class="space-y-3">
            <TransactionCard 
              v-for="transaction in recentTransactions" 
              :key="transaction.id"
              :transaction="transaction"
            />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Order Detail Modal -->
    <OrderDetailModal 
      v-model:open="showOrderDetail"
      :order="selectedOrder"
      @status-updated="handleStatusUpdate"
      @payment-processed="handlePaymentProcessed"
    />

    <!-- Quick Payment Modal -->
    <QuickPaymentModal 
      v-model:open="showQuickPayment"
      :order="quickPaymentOrder"
      @payment-processed="handleQuickPaymentProcessed"
    />
  </KasirLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import KasirLayout from '@/layouts/KasirLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Skeleton } from '@/components/ui/skeleton'
import { 
  RefreshCw, 
  Search, 
  CreditCard, 
  Download, 
  FileText 
} from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'
import PaymentOrderCard from '@/components/kasir/PaymentOrderCard.vue'
import TransactionCard from '@/components/kasir/TransactionCard.vue'
import OrderDetailModal from '@/components/kasir/OrderDetailModal.vue'
import QuickPaymentModal from '@/components/kasir/QuickPaymentModal.vue'

const adminStore = useAdminStore()

const loading = ref(false)
const searching = ref(false)
const searchError = ref('')
const quickPaymentOrderNumber = ref('')
const quickPaymentOrder = ref<AdminOrder | null>(null)
const showOrderDetail = ref(false)
const showQuickPayment = ref(false)
const selectedOrder = ref<AdminOrder | null>(null)

// Mock data for transactions - in real app this would come from API
const recentTransactions = ref([
  {
    id: 1,
    order_number: 'ORD-001',
    amount: 75000,
    method: 'cash',
    status: 'completed',
    processed_at: new Date().toISOString(),
    customer_name: 'John Doe'
  },
  {
    id: 2,
    order_number: 'ORD-002',
    amount: 120000,
    method: 'qris',
    status: 'completed',
    processed_at: new Date(Date.now() - 300000).toISOString(),
    customer_name: 'Jane Smith'
  }
])

const allOrders = computed(() => adminStore.orders)

const pendingPaymentOrders = computed(() => {
  return allOrders.value
    .filter(order => order.payment_status === 'pending')
    .sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
})

const pendingPaymentsCount = computed(() => pendingPaymentOrders.value.length)

const todayRevenue = computed(() => {
  // This would come from dashboard stats in real app
  return adminStore.dashboardStats?.today_revenue || 0
})

const todayTransactions = computed(() => {
  // Mock calculation - in real app this would come from API
  return recentTransactions.value.length
})

const averageOrderValue = computed(() => {
  const completedOrders = allOrders.value.filter(order => order.status === 'completed')
  if (completedOrders.length === 0) return 0
  
  const total = completedOrders.reduce((sum, order) => sum + order.total_amount, 0)
  return total / completedOrders.length
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const refreshData = async () => {
  loading.value = true
  try {
    await Promise.all([
      adminStore.fetchOrders(),
      adminStore.fetchDashboardStats()
    ])
  } catch (error) {
    console.error('Error refreshing data:', error)
  } finally {
    loading.value = false
  }
}

const searchOrder = async () => {
  if (!quickPaymentOrderNumber.value.trim()) return

  searching.value = true
  searchError.value = ''
  
  try {
    // Find order by order number
    const order = allOrders.value.find(o => 
      o.order_number.toLowerCase() === quickPaymentOrderNumber.value.toLowerCase()
    )
    
    if (order) {
      quickPaymentOrder.value = order
      showQuickPayment.value = true
      quickPaymentOrderNumber.value = ''
    } else {
      searchError.value = 'Order not found'
    }
  } catch (error) {
    searchError.value = 'Error searching for order'
    console.error('Error searching order:', error)
  } finally {
    searching.value = false
  }
}

const handlePaymentProcessed = async () => {
  await adminStore.fetchOrders()
}

const handleQuickPaymentProcessed = () => {
  showQuickPayment.value = false
  quickPaymentOrder.value = null
  refreshData()
}

const handleStatusUpdate = async () => {
  await adminStore.fetchOrders()
}

const viewOrderDetails = (order: AdminOrder) => {
  selectedOrder.value = order
  showOrderDetail.value = true
}

const exportTransactions = async () => {
  try {
    // This would call the export API
    console.log('Exporting transactions...')
  } catch (error) {
    console.error('Error exporting transactions:', error)
  }
}

let updateInterval: number | null = null

onMounted(async () => {
  await refreshData()
  
  // Setup auto-refresh every 30 seconds
  updateInterval = window.setInterval(refreshData, 30000)
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>
