<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
          <p class="text-muted-foreground">
            Overview of your restaurant's performance
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" @click="refreshData">
            <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': adminStore.loading }" />
            Refresh
          </Button>
          <Button @click="exportReport">
            <Download class="h-4 w-4 mr-2" />
            Export Report
          </Button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="adminStore.loading && !stats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card v-for="i in 4" :key="i">
          <CardContent class="p-6">
            <Skeleton class="h-4 w-[100px] mb-2" />
            <Skeleton class="h-8 w-[60px] mb-2" />
            <Skeleton class="h-3 w-[80px]" />
          </CardContent>
        </Card>
      </div>

      <!-- Stats Cards -->
      <div v-else-if="stats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatsCard
          title="Total Orders"
          :value="stats.total_orders"
          :change="+12.5"
          change-type="positive"
          icon="ShoppingBag"
        />
        <StatsCard
          title="Pending Orders"
          :value="stats.pending_orders"
          :change="-2.1"
          change-type="negative"
          icon="Clock"
        />
        <StatsCard
          title="Total Revenue"
          :value="formatCurrency(stats.total_revenue)"
          :change="+8.3"
          change-type="positive"
          icon="DollarSign"
        />
        <StatsCard
          title="Today's Revenue"
          :value="formatCurrency(stats.today_revenue)"
          :change="+15.2"
          change-type="positive"
          icon="TrendingUp"
        />
      </div>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <!-- Recent Orders -->
        <Card class="col-span-4">
          <CardHeader>
            <CardTitle>Recent Orders</CardTitle>
            <CardDescription>
              Latest orders from customers
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="stats?.recent_orders?.length" class="space-y-4">
              <div 
                v-for="order in stats.recent_orders" 
                :key="order.id"
                class="flex items-center justify-between p-3 border rounded-lg hover:bg-muted/50 transition-colors"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                    <ShoppingBag class="h-5 w-5 text-primary" />
                  </div>
                  <div>
                    <p class="font-medium">{{ order.order_number }}</p>
                    <p class="text-sm text-muted-foreground">
                      {{ order.customer_name || 'Guest' }}
                    </p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(order.total_amount) }}</p>
                  <Badge :variant="getStatusVariant(order.status)">
                    {{ getStatusLabel(order.status) }}
                  </Badge>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              No recent orders
            </div>
          </CardContent>
        </Card>

        <!-- Popular Items -->
        <Card class="col-span-3">
          <CardHeader>
            <CardTitle>Popular Items</CardTitle>
            <CardDescription>
              Most ordered menu items
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="stats?.popular_items?.length" class="space-y-4">
              <div 
                v-for="(item, index) in stats.popular_items" 
                :key="item.id"
                class="flex items-center space-x-3"
              >
                <div class="w-8 h-8 bg-muted rounded-full flex items-center justify-center text-sm font-medium">
                  {{ index + 1 }}
                </div>
                <div class="flex-1">
                  <p class="font-medium">{{ item.name }}</p>
                  <p class="text-sm text-muted-foreground">
                    {{ item.total_ordered }} orders
                  </p>
                </div>
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(item.revenue) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              No data available
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
          <CardDescription>
            Common tasks and shortcuts
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Button 
              variant="outline" 
              class="h-20 flex-col space-y-2"
              @click="router.visit('/admin/orders')"
            >
              <ShoppingBag class="h-6 w-6" />
              <span>View Orders</span>
            </Button>
            <Button 
              variant="outline" 
              class="h-20 flex-col space-y-2"
              @click="router.visit('/admin/menus')"
            >
              <UtensilsCrossed class="h-6 w-6" />
              <span>Manage Menu</span>
            </Button>
            <Button 
              variant="outline" 
              class="h-20 flex-col space-y-2"
              @click="router.visit('/admin/reports')"
            >
              <FileText class="h-6 w-6" />
              <span>View Reports</span>
            </Button>
            <Button 
              variant="outline" 
              class="h-20 flex-col space-y-2"
              @click="router.visit('/admin/settings')"
            >
              <Settings class="h-6 w-6" />
              <span>Settings</span>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { 
  RefreshCw, 
  Download, 
  ShoppingBag, 
  Clock,
  UtensilsCrossed,
  FileText,
  Settings
} from 'lucide-vue-next'
import { useAdminStore } from '@/stores/admin'
import StatsCard from '@/components/admin/StatsCard.vue'

const adminStore = useAdminStore()

const stats = computed(() => adminStore.dashboardStats)

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

const refreshData = async () => {
  await adminStore.fetchDashboardStats()
}

const exportReport = async () => {
  try {
    const today = new Date().toISOString().split('T')[0]
    const lastMonth = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    await adminStore.exportSalesReport(lastMonth, today)
  } catch (error) {
    console.error('Error exporting report:', error)
  }
}

let updateInterval: number | null = null

onMounted(async () => {
  await adminStore.fetchDashboardStats()
  
  // Setup auto-refresh every 30 seconds
  updateInterval = window.setInterval(() => {
    adminStore.fetchDashboardStats()
  }, 30000)
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>
