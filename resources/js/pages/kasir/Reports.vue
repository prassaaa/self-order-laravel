<template>
  <KasirLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Sales Reports</h1>
          <p class="text-muted-foreground">
            View sales performance and transaction summaries
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" @click="refreshData">
            <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': loading }" />
            Refresh
          </Button>
          <Button @click="exportReport">
            <Download class="h-4 w-4 mr-2" />
            Export
          </Button>
        </div>
      </div>

      <!-- Date Filter -->
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center space-x-4">
            <div class="space-y-1">
              <Label for="date_from">From Date</Label>
              <Input 
                id="date_from"
                v-model="dateFilter.from"
                type="date"
                @change="applyDateFilter"
              />
            </div>
            <div class="space-y-1">
              <Label for="date_to">To Date</Label>
              <Input 
                id="date_to"
                v-model="dateFilter.to"
                type="date"
                @change="applyDateFilter"
              />
            </div>
            <div class="flex items-end space-x-2">
              <Button @click="setToday">Today</Button>
              <Button variant="outline" @click="setThisWeek">This Week</Button>
              <Button variant="outline" @click="setThisMonth">This Month</Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Summary Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold">{{ totalOrders }}</div>
            <div class="text-sm text-muted-foreground">Total Orders</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ formatCurrency(totalRevenue) }}</div>
            <div class="text-sm text-muted-foreground">Total Revenue</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold">{{ formatCurrency(averageOrderValue) }}</div>
            <div class="text-sm text-muted-foreground">Avg Order Value</div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-4 text-center">
            <div class="text-2xl font-bold">{{ completionRate }}%</div>
            <div class="text-sm text-muted-foreground">Completion Rate</div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Methods Breakdown -->
        <Card>
          <CardHeader>
            <CardTitle>Payment Methods</CardTitle>
            <CardDescription>
              Revenue breakdown by payment method
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div 
                v-for="method in paymentMethodStats" 
                :key="method.method"
                class="flex items-center justify-between"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="getMethodClasses(method.method)">
                    <component :is="getMethodIcon(method.method)" class="h-4 w-4" />
                  </div>
                  <div>
                    <p class="font-medium">{{ getMethodLabel(method.method) }}</p>
                    <p class="text-sm text-muted-foreground">{{ method.count }} transactions</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-semibold">{{ formatCurrency(method.total) }}</p>
                  <p class="text-sm text-muted-foreground">{{ method.percentage }}%</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Hourly Sales -->
        <Card>
          <CardHeader>
            <CardTitle>Hourly Sales</CardTitle>
            <CardDescription>
              Sales distribution throughout the day
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div 
                v-for="hour in hourlySales" 
                :key="hour.hour"
                class="flex items-center justify-between"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-12 text-sm font-medium">{{ hour.hour }}:00</div>
                  <div class="flex-1 bg-muted rounded-full h-2 relative">
                    <div 
                      class="bg-primary h-2 rounded-full transition-all"
                      :style="{ width: `${(hour.amount / maxHourlyAmount) * 100}%` }"
                    ></div>
                  </div>
                </div>
                <div class="text-sm font-medium w-20 text-right">
                  {{ formatCurrency(hour.amount) }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Top Selling Items -->
      <Card>
        <CardHeader>
          <CardTitle>Top Selling Items</CardTitle>
          <CardDescription>
            Most popular menu items in the selected period
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div 
              v-for="(item, index) in topSellingItems" 
              :key="item.id"
              class="flex items-center justify-between p-3 border rounded-lg"
            >
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-primary-foreground font-bold text-sm">
                  {{ index + 1 }}
                </div>
                <div>
                  <p class="font-medium">{{ item.name }}</p>
                  <p class="text-sm text-muted-foreground">{{ item.category }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="font-semibold">{{ item.quantity_sold }} sold</p>
                <p class="text-sm text-muted-foreground">{{ formatCurrency(item.revenue) }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </KasirLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import KasirLayout from '@/layouts/KasirLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { 
  RefreshCw, 
  Download,
  Banknote, 
  Smartphone, 
  Building2, 
  CreditCard 
} from 'lucide-vue-next'
import { useAdminStore } from '@/stores/admin'

const adminStore = useAdminStore()

const loading = ref(false)

const dateFilter = ref({
  from: new Date().toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0],
})

// Mock data - in real app this would come from API
const paymentMethodStats = ref([
  { method: 'cash', count: 45, total: 2250000, percentage: 60 },
  { method: 'qris', count: 20, total: 1200000, percentage: 25 },
  { method: 'e_wallet', count: 10, total: 600000, percentage: 10 },
  { method: 'bank_transfer', count: 5, total: 300000, percentage: 5 },
])

const hourlySales = ref([
  { hour: 8, amount: 150000 },
  { hour: 9, amount: 300000 },
  { hour: 10, amount: 450000 },
  { hour: 11, amount: 600000 },
  { hour: 12, amount: 800000 },
  { hour: 13, amount: 750000 },
  { hour: 14, amount: 500000 },
  { hour: 15, amount: 400000 },
  { hour: 16, amount: 350000 },
  { hour: 17, amount: 600000 },
  { hour: 18, amount: 700000 },
  { hour: 19, amount: 650000 },
  { hour: 20, amount: 400000 },
  { hour: 21, amount: 200000 },
])

const topSellingItems = ref([
  { id: 1, name: 'Nasi Goreng Spesial', category: 'Main Course', quantity_sold: 25, revenue: 625000 },
  { id: 2, name: 'Ayam Bakar', category: 'Main Course', quantity_sold: 20, revenue: 600000 },
  { id: 3, name: 'Es Teh Manis', category: 'Beverages', quantity_sold: 40, revenue: 200000 },
  { id: 4, name: 'Gado-gado', category: 'Main Course', quantity_sold: 15, revenue: 375000 },
  { id: 5, name: 'Jus Jeruk', category: 'Beverages', quantity_sold: 18, revenue: 270000 },
])

const allOrders = computed(() => adminStore.orders)

const filteredOrders = computed(() => {
  return allOrders.value.filter(order => {
    const orderDate = new Date(order.created_at).toISOString().split('T')[0]
    return orderDate >= dateFilter.value.from && orderDate <= dateFilter.value.to
  })
})

const totalOrders = computed(() => filteredOrders.value.length)

const totalRevenue = computed(() => {
  return filteredOrders.value
    .filter(order => order.status === 'completed')
    .reduce((total, order) => total + order.total_amount, 0)
})

const averageOrderValue = computed(() => {
  const completedOrders = filteredOrders.value.filter(order => order.status === 'completed')
  return completedOrders.length > 0 ? totalRevenue.value / completedOrders.length : 0
})

const completionRate = computed(() => {
  if (totalOrders.value === 0) return 0
  const completedOrders = filteredOrders.value.filter(order => order.status === 'completed').length
  return Math.round((completedOrders / totalOrders.value) * 100)
})

const maxHourlyAmount = computed(() => {
  return Math.max(...hourlySales.value.map(h => h.amount))
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const getMethodIcon = (method: string) => {
  const icons: Record<string, any> = {
    cash: Banknote,
    qris: Smartphone,
    bank_transfer: Building2,
    e_wallet: Smartphone,
  }
  return icons[method] || CreditCard
}

const getMethodLabel = (method: string) => {
  const labels: Record<string, string> = {
    cash: 'Cash',
    qris: 'QRIS',
    bank_transfer: 'Bank Transfer',
    e_wallet: 'E-Wallet',
  }
  return labels[method] || method
}

const getMethodClasses = (method: string) => {
  const classes: Record<string, string> = {
    cash: 'bg-green-100 text-green-600',
    qris: 'bg-blue-100 text-blue-600',
    bank_transfer: 'bg-purple-100 text-purple-600',
    e_wallet: 'bg-orange-100 text-orange-600',
  }
  return classes[method] || 'bg-gray-100 text-gray-600'
}

const setToday = () => {
  const today = new Date().toISOString().split('T')[0]
  dateFilter.value.from = today
  dateFilter.value.to = today
  applyDateFilter()
}

const setThisWeek = () => {
  const today = new Date()
  const firstDay = new Date(today.setDate(today.getDate() - today.getDay()))
  const lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 6))
  
  dateFilter.value.from = firstDay.toISOString().split('T')[0]
  dateFilter.value.to = lastDay.toISOString().split('T')[0]
  applyDateFilter()
}

const setThisMonth = () => {
  const today = new Date()
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
  const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0)
  
  dateFilter.value.from = firstDay.toISOString().split('T')[0]
  dateFilter.value.to = lastDay.toISOString().split('T')[0]
  applyDateFilter()
}

const applyDateFilter = () => {
  // In real app, this would trigger API call with date filters
  console.log('Applying date filter:', dateFilter.value)
}

const refreshData = async () => {
  loading.value = true
  try {
    await adminStore.fetchOrders()
  } catch (error) {
    console.error('Error refreshing data:', error)
  } finally {
    loading.value = false
  }
}

const exportReport = async () => {
  try {
    await adminStore.exportSalesReport(dateFilter.value.from, dateFilter.value.to)
  } catch (error) {
    console.error('Error exporting report:', error)
  }
}

onMounted(async () => {
  await refreshData()
})
</script>
