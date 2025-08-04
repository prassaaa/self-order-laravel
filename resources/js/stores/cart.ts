import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export interface CartItem {
  id: number
  menu_id: number
  name: string
  price: number
  quantity: number
  notes?: string
  image?: string
  category?: string
}

export interface CartState {
  items: CartItem[]
  isOpen: boolean
}

export const useCartStore = defineStore('cart', () => {
  // State
  const items = ref<CartItem[]>([])
  const isOpen = ref(false)

  // Getters
  const itemCount = computed(() => {
    return items.value.reduce((total, item) => total + item.quantity, 0)
  })

  const totalAmount = computed(() => {
    return items.value.reduce((total, item) => total + (item.price * item.quantity), 0)
  })

  const formattedTotal = computed(() => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(totalAmount.value)
  })

  const isEmpty = computed(() => items.value.length === 0)

  // Actions
  const addItem = (menu: any, quantity: number = 1, notes?: string) => {
    const existingItemIndex = items.value.findIndex(item => 
      item.menu_id === menu.id && item.notes === notes
    )

    if (existingItemIndex > -1) {
      // Update existing item quantity
      items.value[existingItemIndex].quantity += quantity
    } else {
      // Add new item
      const newItem: CartItem = {
        id: Date.now(), // Simple ID generation
        menu_id: menu.id,
        name: menu.name,
        price: menu.price,
        quantity,
        notes,
        image: menu.image_url,
        category: menu.category?.name,
      }
      items.value.push(newItem)
    }

    // Save to localStorage
    saveToStorage()
  }

  const removeItem = (itemId: number) => {
    const index = items.value.findIndex(item => item.id === itemId)
    if (index > -1) {
      items.value.splice(index, 1)
      saveToStorage()
    }
  }

  const updateQuantity = (itemId: number, quantity: number) => {
    const item = items.value.find(item => item.id === itemId)
    if (item) {
      if (quantity <= 0) {
        removeItem(itemId)
      } else {
        item.quantity = quantity
        saveToStorage()
      }
    }
  }

  const updateNotes = (itemId: number, notes: string) => {
    const item = items.value.find(item => item.id === itemId)
    if (item) {
      item.notes = notes
      saveToStorage()
    }
  }

  const clearCart = () => {
    items.value = []
    saveToStorage()
  }

  const toggleSidebar = () => {
    isOpen.value = !isOpen.value
  }

  const openSidebar = () => {
    isOpen.value = true
  }

  const closeSidebar = () => {
    isOpen.value = false
  }

  // Storage functions
  const saveToStorage = () => {
    try {
      localStorage.setItem('cart', JSON.stringify(items.value))
    } catch (error) {
      console.error('Failed to save cart to localStorage:', error)
    }
  }

  const loadFromStorage = () => {
    try {
      const stored = localStorage.getItem('cart')
      if (stored) {
        items.value = JSON.parse(stored)
      }
    } catch (error) {
      console.error('Failed to load cart from localStorage:', error)
      items.value = []
    }
  }

  // Order preparation
  const prepareOrderData = () => {
    return {
      items: items.value.map(item => ({
        menu_id: item.menu_id,
        quantity: item.quantity,
        notes: item.notes || null,
      })),
      total_amount: totalAmount.value,
    }
  }

  // Validation
  const validateCart = () => {
    const errors: string[] = []

    if (isEmpty.value) {
      errors.push('Keranjang kosong')
    }

    if (totalAmount.value < 10000) {
      errors.push('Minimum pemesanan Rp 10.000')
    }

    if (totalAmount.value > 1000000) {
      errors.push('Maksimum pemesanan Rp 1.000.000')
    }

    return {
      isValid: errors.length === 0,
      errors,
    }
  }

  // Initialize cart from storage
  loadFromStorage()

  return {
    // State
    items,
    isOpen,
    
    // Getters
    itemCount,
    totalAmount,
    formattedTotal,
    isEmpty,
    
    // Actions
    addItem,
    removeItem,
    updateQuantity,
    updateNotes,
    clearCart,
    toggleSidebar,
    openSidebar,
    closeSidebar,
    prepareOrderData,
    validateCart,
    loadFromStorage,
  }
})
