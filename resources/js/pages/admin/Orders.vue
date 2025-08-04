<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Orders</h1>
          <p class="text-muted-foreground">
            Manage and track all customer orders
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" @click="refreshOrders">
            <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': adminStore.loading }" />
            Refresh
          </Button>
          <Button variant="outline" @click="exportOrders">
            <Download class="h-4 w-4 mr-2" />
            Export
          </Button>
        </div>
      </div>

      <!-- Filters -->
      <Card>
        <CardContent class="p-6">
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <div class="space-y-2">
              <Label for="search">Search</Label>
              <Input 
                id="search"
                v-model="filters.search"
                placeholder="Order number, customer name..."
                @input="debouncedSearch"
              />
            </div>
            
            <div class="space-y-2">
              <Label for="status">Status</Label>
              <Select v-model="filters.status" @update:model-value="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All statuses</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="confirmed">Confirmed</SelectItem>
                  <SelectItem value="preparing">Preparing</SelectItem>
                  <SelectItem value="ready">Ready</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="payment_status">Payment Status</Label>
              <Select v-model="filters.payment_status" @update:model-value="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="All payments" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All payments</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="paid">Paid</SelectItem>
                  <SelectItem value="failed">Failed</SelectItem>
                  <SelectItem value="refunded">Refunded</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="date_from">Date From</Label>
              <Input 
                id="date_from"
                v-model="filters.date_from"
                type="date"
                @change="applyFilters"
              />
            </div>

            <div class="space-y-2">
              <Label for="date_to">Date To</Label>
              <Input 
                id="date_to"
                v-model="filters.date_to"
                type="date"
                @change="applyFilters"
              />
            </div>
          </div>

          <div class="flex items-center justify-between mt-4">
            <Button variant="outline" size="sm" @click="clearFilters">
              Clear Filters
            </Button>
            <p class="text-sm text-muted-foreground">
              {{ orders.length }} orders found
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Orders Table -->
      <Card>
        <CardContent class="p-0">
          <div v-if="adminStore.loading" class="p-6">
            <div class="space-y-4">
              <Skeleton v-for="i in 5" :key="i" class="h-16 w-full" />
            </div>
          </div>

          <div v-else-if="orders.length === 0" class="text-center py-12">
            <ShoppingBag class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <h3 class="text-lg font-semibold mb-2">No Orders Found</h3>
            <p class="text-muted-foreground">
              {{ filters.search || filters.status ? 'No orders match your filters' : 'No orders have been placed yet' }}
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead class="border-b">
                <tr>
                  <th class="text-left p-4 font-medium">Order</th>
                  <th class="text-left p-4 font-medium">Customer</th>
                  <th class="text-left p-4 font-medium">Items</th>
                  <th class="text-left p-4 font-medium">Total</th>
                  <th class="text-left p-4 font-medium">Status</th>
                  <th class="text-left p-4 font-medium">Payment</th>
                  <th class="text-left p-4 font-medium">Date</th>
                  <th class="text-left p-4 font-medium">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="order in orders" 
                  :key="order.id"
                  class="border-b hover:bg-muted/50 transition-colors"
                >
                  <td class="p-4">
                    <div>
                      <p class="font-medium">{{ order.order_number }}</p>
                      <p v-if="order.table_number" class="text-sm text-muted-foreground">
                        Table: {{ order.table_number }}
                      </p>
                    </div>
                  </td>
                  <td class="p-4">
                    <div>
                      <p class="font-medium">{{ order.customer_name || 'Guest' }}</p>
                      <p v-if="order.customer_phone" class="text-sm text-muted-foreground">
                        {{ order.customer_phone }}
                      </p>
                    </div>
                  </td>
                  <td class="p-4">
                    <p class="text-sm">{{ order.order_items.length }} items</p>
                  </td>
                  <td class="p-4">
                    <p class="font-medium">{{ order.formatted_total }}</p>
                  </td>
                  <td class="p-4">
                    <Badge :variant="getStatusVariant(order.status)">
                      {{ getStatusLabel(order.status) }}
                    </Badge>
                  </td>
                  <td class="p-4">
                    <Badge :variant="getPaymentStatusVariant(order.payment_status)">
                      {{ getPaymentStatusLabel(order.payment_status) }}
                    </Badge>
                  </td>
                  <td class="p-4">
                    <p class="text-sm">{{ order.created_at_human }}</p>
                  </td>
                  <td class="p-4">
                    <div class="flex items-center space-x-2">
                      <Button 
                        variant="ghost" 
                        size="sm"
                        @click="viewOrder(order)"
                      >
                        <Eye class="h-4 w-4" />
                      </Button>
                      <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                          <Button variant="ghost" size="sm">
                            <MoreHorizontal class="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem @click="updateStatus(order, 'confirmed')" :disabled="order.status !== 'pending'">
                            Confirm Order
                          </DropdownMenuItem>
                          <DropdownMenuItem @click="updateStatus(order, 'preparing')" :disabled="!['confirmed'].includes(order.status)">
                            Start Preparing
                          </DropdownMenuItem>
                          <DropdownMenuItem @click="updateStatus(order, 'ready')" :disabled="order.status !== 'preparing'">
                            Mark Ready
                          </DropdownMenuItem>
                          <DropdownMenuItem @click="updateStatus(order, 'completed')" :disabled="order.status !== 'ready'">
                            Complete Order
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem @click="processPayment(order)" :disabled="order.payment_status === 'paid'">
                            Process Payment
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem @click="updateStatus(order, 'cancelled')" :disabled="['completed', 'cancelled'].includes(order.status)" class="text-destructive">
                            Cancel Order
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Order Detail Dialog -->
    <OrderDetailDialog 
      v-model:open="showOrderDetail"
      :order="selectedOrder"
      @status-updated="handleStatusUpdate"
      @payment-processed="handlePaymentProcessed"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { debounce } from 'lodash-es'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { 
  RefreshCw, 
  Download, 
  ShoppingBag,
  Eye,
  MoreHorizontal
} from 'lucide-vue-next'
import { useAdminStore, type AdminOrder } from '@/stores/admin'
import OrderDetailDialog from '@/components/admin/OrderDetailDialog.vue'

const adminStore = useAdminStore()

const filters = ref({
  search: '',
  status: '',
  payment_status: '',
  date_from: '',
  date_to: '',
})

const showOrderDetail = ref(false)
const selectedOrder = ref<AdminOrder | null>(null)

const orders = computed(() => adminStore.orders)

const debouncedSearch = debounce(() => {
  applyFilters()
}, 500)

const applyFilters = async () => {
  adminStore.setOrderFilters(filters.value)
  await adminStore.fetchOrders()
}

const clearFilters = async () => {
  filters.value = {
    search: '',
    status: '',
    payment_status: '',
    date_from: '',
    date_to: '',
  }
  adminStore.clearOrderFilters()
  await adminStore.fetchOrders()
}

const refreshOrders = async () => {
  await adminStore.fetchOrders()
}

const exportOrders = async () => {
  try {
    await adminStore.exportOrders()
  } catch (error) {
    console.error('Error exporting orders:', error)
  }
}

const viewOrder = (order: AdminOrder) => {
  selectedOrder.value = order
  showOrderDetail.value = true
}

const updateStatus = async (order: AdminOrder, status: string) => {
  try {
    await adminStore.updateOrderStatus(order.id, status)
  } catch (error) {
    console.error('Error updating order status:', error)
  }
}

const processPayment = (order: AdminOrder) => {
  selectedOrder.value = order
  showOrderDetail.value = true
  // The dialog will handle payment processing
}

const handleStatusUpdate = async () => {
  await adminStore.fetchOrders()
}

const handlePaymentProcessed = async () => {
  await adminStore.fetchOrders()
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

const getPaymentStatusVariant = (status: string) => {
  const variants: Record<string, string> = {
    pending: 'secondary',
    paid: 'default',
    failed: 'destructive',
    refunded: 'secondary',
  }
  return variants[status] || 'secondary'
}

const getPaymentStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Pending',
    paid: 'Paid',
    failed: 'Failed',
    refunded: 'Refunded',
  }
  return labels[status] || status
}

let updateInterval: number | null = null

onMounted(async () => {
  await adminStore.fetchOrders()
  
  // Setup auto-refresh every 30 seconds
  updateInterval = window.setInterval(() => {
    adminStore.fetchOrders()
  }, 30000)
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>
