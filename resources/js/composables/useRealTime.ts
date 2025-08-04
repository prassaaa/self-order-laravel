import { ref, onMounted, onUnmounted } from 'vue'
import Echo from '@/echo'

export function useRealTimeOrders() {
  const isConnected = ref(false)
  const lastUpdate = ref<Date | null>(null)
  
  let channel: any = null

  const connect = () => {
    try {
      // Listen to orders channel
      channel = Echo.channel('orders')
        .listen('.order.created', (e: any) => {
          console.log('New order created:', e)
          lastUpdate.value = new Date()
          // Emit custom event for components to listen
          window.dispatchEvent(new CustomEvent('order-created', { detail: e }))
        })
        .listen('.order.status.updated', (e: any) => {
          console.log('Order status updated:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('order-status-updated', { detail: e }))
        })
        .listen('.payment.processed', (e: any) => {
          console.log('Payment processed:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('payment-processed', { detail: e }))
        })

      isConnected.value = true
      console.log('Connected to orders channel')
    } catch (error) {
      console.error('Failed to connect to real-time channel:', error)
      isConnected.value = false
    }
  }

  const disconnect = () => {
    if (channel) {
      Echo.leaveChannel('orders')
      channel = null
      isConnected.value = false
      console.log('Disconnected from orders channel')
    }
  }

  onMounted(() => {
    connect()
  })

  onUnmounted(() => {
    disconnect()
  })

  return {
    isConnected,
    lastUpdate,
    connect,
    disconnect
  }
}

export function useRealTimeKitchen() {
  const isConnected = ref(false)
  const lastUpdate = ref<Date | null>(null)
  
  let channel: any = null

  const connect = () => {
    try {
      // Listen to kitchen channel
      channel = Echo.channel('kitchen')
        .listen('.order.created', (e: any) => {
          console.log('New order for kitchen:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('kitchen-new-order', { detail: e }))
        })
        .listen('.order.status.updated', (e: any) => {
          console.log('Kitchen order status updated:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('kitchen-order-updated', { detail: e }))
        })

      isConnected.value = true
      console.log('Connected to kitchen channel')
    } catch (error) {
      console.error('Failed to connect to kitchen channel:', error)
      isConnected.value = false
    }
  }

  const disconnect = () => {
    if (channel) {
      Echo.leaveChannel('kitchen')
      channel = null
      isConnected.value = false
      console.log('Disconnected from kitchen channel')
    }
  }

  onMounted(() => {
    connect()
  })

  onUnmounted(() => {
    disconnect()
  })

  return {
    isConnected,
    lastUpdate,
    connect,
    disconnect
  }
}

export function useRealTimeAdmin() {
  const isConnected = ref(false)
  const lastUpdate = ref<Date | null>(null)
  
  let channel: any = null

  const connect = () => {
    try {
      // Listen to admin channel
      channel = Echo.channel('admin')
        .listen('.order.created', (e: any) => {
          console.log('Admin: New order:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('admin-new-order', { detail: e }))
        })
        .listen('.order.status.updated', (e: any) => {
          console.log('Admin: Order status updated:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('admin-order-updated', { detail: e }))
        })
        .listen('.payment.processed', (e: any) => {
          console.log('Admin: Payment processed:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('admin-payment-processed', { detail: e }))
        })

      isConnected.value = true
      console.log('Connected to admin channel')
    } catch (error) {
      console.error('Failed to connect to admin channel:', error)
      isConnected.value = false
    }
  }

  const disconnect = () => {
    if (channel) {
      Echo.leaveChannel('admin')
      channel = null
      isConnected.value = false
      console.log('Disconnected from admin channel')
    }
  }

  onMounted(() => {
    connect()
  })

  onUnmounted(() => {
    disconnect()
  })

  return {
    isConnected,
    lastUpdate,
    connect,
    disconnect
  }
}

export function useRealTimeKasir() {
  const isConnected = ref(false)
  const lastUpdate = ref<Date | null>(null)
  
  let channel: any = null

  const connect = () => {
    try {
      // Listen to kasir channel
      channel = Echo.channel('kasir')
        .listen('.order.created', (e: any) => {
          console.log('Kasir: New order:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('kasir-new-order', { detail: e }))
        })
        .listen('.payment.processed', (e: any) => {
          console.log('Kasir: Payment processed:', e)
          lastUpdate.value = new Date()
          window.dispatchEvent(new CustomEvent('kasir-payment-processed', { detail: e }))
        })

      isConnected.value = true
      console.log('Connected to kasir channel')
    } catch (error) {
      console.error('Failed to connect to kasir channel:', error)
      isConnected.value = false
    }
  }

  const disconnect = () => {
    if (channel) {
      Echo.leaveChannel('kasir')
      channel = null
      isConnected.value = false
      console.log('Disconnected from kasir channel')
    }
  }

  onMounted(() => {
    connect()
  })

  onUnmounted(() => {
    disconnect()
  })

  return {
    isConnected,
    lastUpdate,
    connect,
    disconnect
  }
}

// Utility function to show notifications
export function useRealTimeNotifications() {
  const showNotification = (title: string, message: string, type: 'info' | 'success' | 'warning' | 'error' = 'info') => {
    // This would integrate with your notification system
    console.log(`[${type.toUpperCase()}] ${title}: ${message}`)
    
    // You can integrate with toast notifications here
    // For example: toast.add({ title, description: message, variant: type })
  }

  return {
    showNotification
  }
}
