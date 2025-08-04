import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface DashboardStats {
  total_orders: number
  pending_orders: number
  completed_orders: number
  total_revenue: number
  today_orders: number
  today_revenue: number
  popular_items: Array<{
    id: number
    name: string
    total_ordered: number
    revenue: number
  }>
  recent_orders: Array<{
    id: number
    order_number: string
    customer_name: string
    total_amount: number
    status: string
    created_at: string
  }>
}

export interface AdminOrder {
  id: number
  order_number: string
  table_number?: string
  status: string
  payment_status: string
  total_amount: number
  formatted_total: string
  customer_name?: string
  customer_phone?: string
  notes?: string
  created_at: string
  updated_at: string
  order_items: Array<{
    id: number
    menu_id: number
    quantity: number
    price: number
    subtotal: number
    notes?: string
    menu: {
      id: number
      name: string
      image_url?: string
    }
  }>
  is_paid: boolean
  can_be_cancelled: boolean
  created_at_human: string
}

export const useAdminStore = defineStore('admin', () => {
  // State
  const dashboardStats = ref<DashboardStats | null>(null)
  const orders = ref<AdminOrder[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Filters
  const orderFilters = ref({
    status: '',
    payment_status: '',
    search: '',
    date_from: '',
    date_to: '',
    per_page: 15,
  })

  // Getters
  const pendingOrders = computed(() => {
    return orders.value.filter(order => order.status === 'pending')
  })

  const preparingOrders = computed(() => {
    return orders.value.filter(order => order.status === 'preparing')
  })

  const readyOrders = computed(() => {
    return orders.value.filter(order => order.status === 'ready')
  })

  const todayRevenue = computed(() => {
    return dashboardStats.value?.today_revenue || 0
  })

  const totalOrders = computed(() => {
    return dashboardStats.value?.total_orders || 0
  })

  // Actions
  const fetchDashboardStats = async () => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get('/api/v1/admin/dashboard/stats')
      dashboardStats.value = response.data.data
      
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard stats'
      console.error('Error fetching dashboard stats:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchOrders = async (filters: any = {}) => {
    try {
      loading.value = true
      error.value = null
      
      const params = { ...orderFilters.value, ...filters }
      const response = await axios.get('/api/v1/admin/orders', { params })
      
      orders.value = response.data.data
      
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch orders'
      console.error('Error fetching orders:', err)
    } finally {
      loading.value = false
    }
  }

  const updateOrderStatus = async (orderId: number, status: string) => {
    try {
      const response = await axios.patch(`/api/v1/admin/orders/${orderId}/status`, {
        status
      })
      
      // Update order in local state
      const orderIndex = orders.value.findIndex(order => order.id === orderId)
      if (orderIndex > -1) {
        orders.value[orderIndex] = response.data.data
      }
      
      // Refresh dashboard stats
      await fetchDashboardStats()
      
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to update order status'
      console.error('Error updating order status:', err)
      throw err
    }
  }

  const processPayment = async (orderId: number, paymentData: any) => {
    try {
      const response = await axios.post(`/api/v1/admin/orders/${orderId}/payments`, paymentData)
      
      // Update order in local state
      const orderIndex = orders.value.findIndex(order => order.id === orderId)
      if (orderIndex > -1) {
        // Refresh the order data
        const orderResponse = await axios.get(`/api/v1/admin/orders/${orderId}`)
        orders.value[orderIndex] = orderResponse.data.data
      }
      
      // Refresh dashboard stats
      await fetchDashboardStats()
      
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to process payment'
      console.error('Error processing payment:', err)
      throw err
    }
  }

  const getOrderById = async (orderId: number) => {
    try {
      const response = await axios.get(`/api/v1/admin/orders/${orderId}`)
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch order'
      console.error('Error fetching order:', err)
      throw err
    }
  }

  // Filter actions
  const setOrderFilters = (filters: Partial<typeof orderFilters.value>) => {
    orderFilters.value = { ...orderFilters.value, ...filters }
  }

  const clearOrderFilters = () => {
    orderFilters.value = {
      status: '',
      payment_status: '',
      search: '',
      date_from: '',
      date_to: '',
      per_page: 15,
    }
  }

  // Real-time updates
  const setupOrderUpdates = () => {
    // This would integrate with Pusher for real-time updates
    // For now, we'll use polling
    const interval = setInterval(async () => {
      await fetchOrders()
      await fetchDashboardStats()
    }, 30000) // Poll every 30 seconds

    return interval
  }

  // Export functions
  const exportOrders = async (filters: any = {}) => {
    try {
      const params = { ...orderFilters.value, ...filters, export: 'excel' }
      const response = await axios.get('/api/v1/admin/orders/export', {
        params,
        responseType: 'blob'
      })
      
      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `orders-${new Date().toISOString().split('T')[0]}.xlsx`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to export orders'
      console.error('Error exporting orders:', err)
      throw err
    }
  }

  const exportSalesReport = async (dateFrom: string, dateTo: string) => {
    try {
      const response = await axios.get('/api/v1/admin/reports/sales/export', {
        params: { date_from: dateFrom, date_to: dateTo },
        responseType: 'blob'
      })
      
      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `sales-report-${dateFrom}-${dateTo}.pdf`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to export sales report'
      console.error('Error exporting sales report:', err)
      throw err
    }
  }

  return {
    // State
    dashboardStats,
    orders,
    loading,
    error,
    orderFilters,
    
    // Getters
    pendingOrders,
    preparingOrders,
    readyOrders,
    todayRevenue,
    totalOrders,
    
    // Actions
    fetchDashboardStats,
    fetchOrders,
    updateOrderStatus,
    processPayment,
    getOrderById,
    setOrderFilters,
    clearOrderFilters,
    setupOrderUpdates,
    exportOrders,
    exportSalesReport,
  }
})
