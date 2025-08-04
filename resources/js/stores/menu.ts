import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface Category {
  id: number
  name: string
  slug: string
  image?: string
  image_url?: string
  is_active: boolean
  sort_order: number
  menus_count?: number
}

export interface Menu {
  id: number
  category_id: number
  name: string
  description?: string
  price: number
  formatted_price: string
  image?: string
  image_url?: string
  is_available: boolean
  sort_order: number
  category?: Category
}

export const useMenuStore = defineStore('menu', () => {
  // State
  const categories = ref<Category[]>([])
  const menus = ref<Menu[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  
  // Filters
  const selectedCategoryId = ref<number | null>(null)
  const searchQuery = ref('')
  const priceRange = ref<{ min: number; max: number }>({ min: 0, max: 1000000 })

  // Getters
  const activeCategories = computed(() => {
    return categories.value.filter(category => category.is_active)
  })

  const availableMenus = computed(() => {
    return menus.value.filter(menu => menu.is_available)
  })

  const filteredMenus = computed(() => {
    let filtered = availableMenus.value

    // Filter by category
    if (selectedCategoryId.value) {
      filtered = filtered.filter(menu => menu.category_id === selectedCategoryId.value)
    }

    // Filter by search query
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim()
      filtered = filtered.filter(menu => 
        menu.name.toLowerCase().includes(query) ||
        menu.description?.toLowerCase().includes(query) ||
        menu.category?.name.toLowerCase().includes(query)
      )
    }

    // Filter by price range
    filtered = filtered.filter(menu => 
      menu.price >= priceRange.value.min && menu.price <= priceRange.value.max
    )

    return filtered
  })

  const menusByCategory = computed(() => {
    const grouped: Record<number, Menu[]> = {}
    
    filteredMenus.value.forEach(menu => {
      if (!grouped[menu.category_id]) {
        grouped[menu.category_id] = []
      }
      grouped[menu.category_id].push(menu)
    })

    return grouped
  })

  const popularMenus = computed(() => {
    // This would typically come from API with order statistics
    // For now, we'll just return first 6 available menus
    return availableMenus.value.slice(0, 6)
  })

  // Actions
  const fetchCategories = async () => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get('/api/v1/categories', {
        params: {
          active: true,
          with_menu_count: true,
        }
      })
      
      categories.value = response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch categories'
      console.error('Error fetching categories:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchMenus = async (params: any = {}) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get('/api/v1/menus', {
        params: {
          available: true,
          per_page: 100, // Get all available menus
          ...params,
        }
      })
      
      menus.value = response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch menus'
      console.error('Error fetching menus:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchMenusByCategory = async (categoryId: number) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get(`/api/v1/categories/${categoryId}/menus`, {
        params: {
          available: true,
        }
      })
      
      // Update menus for this category
      const categoryMenus = response.data.data
      
      // Remove existing menus for this category and add new ones
      menus.value = menus.value.filter(menu => menu.category_id !== categoryId)
      menus.value.push(...categoryMenus)
      
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch category menus'
      console.error('Error fetching category menus:', err)
    } finally {
      loading.value = false
    }
  }

  const getMenuById = (id: number): Menu | undefined => {
    return menus.value.find(menu => menu.id === id)
  }

  const getCategoryById = (id: number): Category | undefined => {
    return categories.value.find(category => category.id === id)
  }

  // Filter actions
  const setSelectedCategory = (categoryId: number | null) => {
    selectedCategoryId.value = categoryId
  }

  const setSearchQuery = (query: string) => {
    searchQuery.value = query
  }

  const setPriceRange = (min: number, max: number) => {
    priceRange.value = { min, max }
  }

  const clearFilters = () => {
    selectedCategoryId.value = null
    searchQuery.value = ''
    priceRange.value = { min: 0, max: 1000000 }
  }

  // Search functionality
  const searchMenus = async (query: string) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get('/api/v1/menus', {
        params: {
          available: true,
          search: query,
          per_page: 50,
        }
      })
      
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to search menus'
      console.error('Error searching menus:', err)
      return []
    } finally {
      loading.value = false
    }
  }

  // Initialize data
  const initializeData = async () => {
    await Promise.all([
      fetchCategories(),
      fetchMenus(),
    ])
  }

  return {
    // State
    categories,
    menus,
    loading,
    error,
    selectedCategoryId,
    searchQuery,
    priceRange,
    
    // Getters
    activeCategories,
    availableMenus,
    filteredMenus,
    menusByCategory,
    popularMenus,
    
    // Actions
    fetchCategories,
    fetchMenus,
    fetchMenusByCategory,
    getMenuById,
    getCategoryById,
    setSelectedCategory,
    setSearchQuery,
    setPriceRange,
    clearFilters,
    searchMenus,
    initializeData,
  }
})
