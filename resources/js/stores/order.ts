import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface OrderItem {
  id: number
  menu_id: number
  quantity: number
  price: number
  subtotal: number
  notes?: string
  menu?: {
    id: number
    name: string
    image_url?: string
  }
}

export interface Order {
  id: number
  order_number: string
  table_number?: string
  status: 'pending' | 'confirmed' | 'preparing' | 'ready' | 'completed' | 'cancelled'
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded'
  total_amount: number
  formatted_total: string
  customer_name?: string
  customer_phone?: string
  notes?: string
  created_at: string
  updated_at: string
  order_items: OrderItem[]
  is_paid: boolean
  can_be_cancelled: boolean
  created_at_human: string
}

export const useOrderStore = defineStore('order', () => {
  // State
  const currentOrder = ref<Order | null>(null)
  const orderHistory = ref<Order[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const hasCurrentOrder = computed(() => currentOrder.value !== null)

  const orderStatusLabel = computed(() => {
    if (!currentOrder.value) return ''
    
    const statusLabels = {
      pending: 'Menunggu Konfirmasi',
      confirmed: 'Dikonfirmasi',
      preparing: 'Sedang Diproses',
      ready: 'Siap Diambil',
      completed: 'Selesai',
      cancelled: 'Dibatalkan',
    }
    
    return statusLabels[currentOrder.value.status] || currentOrder.value.status
  })

  const paymentStatusLabel = computed(() => {
    if (!currentOrder.value) return ''
    
    const statusLabels = {
      pending: 'Menunggu Pembayaran',
      paid: 'Sudah Dibayar',
      failed: 'Pembayaran Gagal',
      refunded: 'Dikembalikan',
    }
    
    return statusLabels[currentOrder.value.payment_status] || currentOrder.value.payment_status
  })

  const canTrackOrder = computed(() => {
    return currentOrder.value && 
           !['completed', 'cancelled'].includes(currentOrder.value.status)
  })

  // Actions
  const createOrder = async (orderData: any) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.post('/api/v1/orders', orderData)
      
      currentOrder.value = response.data.data
      
      // Add to history
      orderHistory.value.unshift(response.data.data)
      
      // Save order number to localStorage for tracking
      localStorage.setItem('current_order_id', response.data.data.id.toString())
      
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to create order'
      console.error('Error creating order:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchOrder = async (orderId: number) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get(`/api/v1/orders/${orderId}`)
      
      currentOrder.value = response.data.data
      
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch order'
      console.error('Error fetching order:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchOrderByNumber = async (orderNumber: string) => {
    try {
      loading.value = true
      error.value = null
      
      // This would require a new API endpoint or search functionality
      const response = await axios.get('/api/v1/orders', {
        params: {
          search: orderNumber,
          per_page: 1,
        }
      })
      
      if (response.data.data.length > 0) {
        currentOrder.value = response.data.data[0]
        return response.data.data[0]
      } else {
        throw new Error('Order not found')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Order not found'
      console.error('Error fetching order by number:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const trackOrder = async () => {
    if (!currentOrder.value) return
    
    try {
      const updatedOrder = await fetchOrder(currentOrder.value.id)
      currentOrder.value = updatedOrder
      
      // Update in history if exists
      const historyIndex = orderHistory.value.findIndex(order => order.id === updatedOrder.id)
      if (historyIndex > -1) {
        orderHistory.value[historyIndex] = updatedOrder
      }
      
      return updatedOrder
    } catch (err) {
      console.error('Error tracking order:', err)
    }
  }

  const clearCurrentOrder = () => {
    currentOrder.value = null
    localStorage.removeItem('current_order_id')
  }

  const loadCurrentOrderFromStorage = async () => {
    try {
      const orderId = localStorage.getItem('current_order_id')
      if (orderId) {
        await fetchOrder(parseInt(orderId))
      }
    } catch (err) {
      console.error('Error loading order from storage:', err)
      localStorage.removeItem('current_order_id')
    }
  }

  // Real-time order updates
  const setupOrderTracking = () => {
    if (!currentOrder.value) return

    // This would integrate with Pusher for real-time updates
    // For now, we'll use polling
    const interval = setInterval(async () => {
      if (!currentOrder.value || ['completed', 'cancelled'].includes(currentOrder.value.status)) {
        clearInterval(interval)
        return
      }
      
      await trackOrder()
    }, 30000) // Poll every 30 seconds

    return interval
  }

  // Order validation
  const validateOrderData = (orderData: any) => {
    const errors: string[] = []

    if (!orderData.items || orderData.items.length === 0) {
      errors.push('Tidak ada item dalam pesanan')
    }

    if (orderData.items) {
      orderData.items.forEach((item: any, index: number) => {
        if (!item.menu_id) {
          errors.push(`Item ${index + 1}: Menu ID diperlukan`)
        }
        if (!item.quantity || item.quantity < 1) {
          errors.push(`Item ${index + 1}: Quantity harus minimal 1`)
        }
      })
    }

    if (orderData.customer_phone && !/^[\+]?[0-9\-\(\)\s]+$/.test(orderData.customer_phone)) {
      errors.push('Format nomor telepon tidak valid')
    }

    return {
      isValid: errors.length === 0,
      errors,
    }
  }

  // Initialize
  const initialize = async () => {
    await loadCurrentOrderFromStorage()
  }

  return {
    // State
    currentOrder,
    orderHistory,
    loading,
    error,
    
    // Getters
    hasCurrentOrder,
    orderStatusLabel,
    paymentStatusLabel,
    canTrackOrder,
    
    // Actions
    createOrder,
    fetchOrder,
    fetchOrderByNumber,
    trackOrder,
    clearCurrentOrder,
    loadCurrentOrderFromStorage,
    setupOrderTracking,
    validateOrderData,
    initialize,
  }
})
