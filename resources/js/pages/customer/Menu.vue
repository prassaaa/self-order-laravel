<template>
  <CustomerLayout>
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl lg:text-4xl font-bold mb-4">Menu Kami</h1>
        <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
          Jelajahi berbagai pilihan hidangan lezat yang tersedia
        </p>
      </div>

      <!-- Filters -->
      <div class="mb-8 space-y-4">
        <!-- Search -->
        <div class="relative max-w-md mx-auto">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input 
            v-model="searchQuery"
            placeholder="Cari menu..."
            class="pl-10"
            @input="handleSearch"
          />
        </div>

        <!-- Category Filter -->
        <div class="flex flex-wrap justify-center gap-2">
          <Button 
            variant="outline"
            size="sm"
            :class="{ 'bg-primary text-primary-foreground': selectedCategoryId === null }"
            @click="selectCategory(null)"
          >
            Semua
          </Button>
          <Button 
            v-for="category in menuStore.activeCategories" 
            :key="category.id"
            variant="outline"
            size="sm"
            :class="{ 'bg-primary text-primary-foreground': selectedCategoryId === category.id }"
            @click="selectCategory(category.id)"
          >
            {{ category.name }}
            <Badge v-if="category.menus_count" variant="secondary" class="ml-2">
              {{ category.menus_count }}
            </Badge>
          </Button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="menuStore.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="i in 8" :key="i" class="space-y-3">
          <Skeleton class="h-48 w-full rounded-lg" />
          <Skeleton class="h-4 w-3/4" />
          <Skeleton class="h-4 w-1/2" />
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="menuStore.error" class="text-center py-12">
        <AlertCircle class="h-12 w-12 mx-auto text-destructive mb-4" />
        <h3 class="text-lg font-semibold mb-2">Gagal Memuat Menu</h3>
        <p class="text-muted-foreground mb-4">{{ menuStore.error }}</p>
        <Button @click="retryLoad">
          <RefreshCw class="h-4 w-4 mr-2" />
          Coba Lagi
        </Button>
      </div>

      <!-- Menu Grid -->
      <div v-else-if="filteredMenus.length > 0">
        <!-- Category Sections -->
        <div v-if="selectedCategoryId === null" class="space-y-12">
          <div 
            v-for="category in categoriesWithMenus" 
            :key="category.id"
            class="space-y-6"
          >
            <div class="flex items-center justify-between">
              <h2 class="text-2xl font-bold">{{ category.name }}</h2>
              <Badge variant="secondary">
                {{ menusByCategory[category.id]?.length || 0 }} item
              </Badge>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <MenuCard 
                v-for="menu in menusByCategory[category.id]" 
                :key="menu.id"
                :menu="menu"
                @add-to-cart="addToCart"
              />
            </div>
          </div>
        </div>

        <!-- Single Category or Search Results -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <MenuCard 
            v-for="menu in filteredMenus" 
            :key="menu.id"
            :menu="menu"
            @add-to-cart="addToCart"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <UtensilsCrossed class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">Tidak Ada Menu</h3>
        <p class="text-muted-foreground mb-4">
          {{ searchQuery ? 'Tidak ditemukan menu yang sesuai dengan pencarian' : 'Belum ada menu yang tersedia' }}
        </p>
        <Button 
          v-if="searchQuery || selectedCategoryId"
          variant="outline"
          @click="clearFilters"
        >
          Hapus Filter
        </Button>
      </div>

      <!-- Floating Cart Button (Mobile) -->
      <div 
        v-if="cartStore.itemCount > 0"
        class="fixed bottom-4 right-4 z-40 md:hidden"
      >
        <Button 
          size="lg"
          class="rounded-full shadow-lg"
          @click="cartStore.openSidebar"
        >
          <ShoppingCart class="h-5 w-5 mr-2" />
          {{ cartStore.itemCount }}
          <Badge class="ml-2 bg-background text-foreground">
            {{ cartStore.formattedTotal }}
          </Badge>
        </Button>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import CustomerLayout from '@/layouts/CustomerLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { 
  Search, 
  AlertCircle, 
  RefreshCw, 
  UtensilsCrossed, 
  ShoppingCart 
} from 'lucide-vue-next'
import { useMenuStore } from '@/stores/menu'
import { useCartStore } from '@/stores/cart'
import MenuCard from '@/components/customer/MenuCard.vue'

const menuStore = useMenuStore()
const cartStore = useCartStore()

const searchQuery = ref('')
const selectedCategoryId = ref<number | null>(null)

const filteredMenus = computed(() => menuStore.filteredMenus)
const menusByCategory = computed(() => menuStore.menusByCategory)

const categoriesWithMenus = computed(() => {
  return menuStore.activeCategories.filter(category => 
    menusByCategory.value[category.id]?.length > 0
  )
})

const selectCategory = (categoryId: number | null) => {
  selectedCategoryId.value = categoryId
  menuStore.setSelectedCategory(categoryId)
}

const handleSearch = () => {
  menuStore.setSearchQuery(searchQuery.value)
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategoryId.value = null
  menuStore.clearFilters()
}

const addToCart = (menu: any, quantity: number = 1, notes?: string) => {
  cartStore.addItem(menu, quantity, notes)
}

const retryLoad = async () => {
  await menuStore.initializeData()
}

// Watch for search query changes
watch(searchQuery, (newQuery) => {
  menuStore.setSearchQuery(newQuery)
})

onMounted(async () => {
  if (menuStore.categories.length === 0 || menuStore.menus.length === 0) {
    await menuStore.initializeData()
  }
})
</script>
