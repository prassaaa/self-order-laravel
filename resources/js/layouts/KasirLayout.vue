<template>
  <div class="min-h-screen bg-background">
    <!-- Top Header -->
    <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div class="container mx-auto px-4">
        <div class="flex h-16 items-center justify-between">
          <!-- Logo & Title -->
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                <UtensilsCrossed class="h-4 w-4 text-primary-foreground" />
              </div>
              <div>
                <h1 class="font-bold text-lg">Self Order</h1>
                <p class="text-xs text-muted-foreground">Kasir Dashboard</p>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="hidden md:flex items-center space-x-6">
            <div class="text-center">
              <p class="text-sm font-medium">{{ pendingOrdersCount }}</p>
              <p class="text-xs text-muted-foreground">Pending</p>
            </div>
            <div class="text-center">
              <p class="text-sm font-medium">{{ preparingOrdersCount }}</p>
              <p class="text-xs text-muted-foreground">Preparing</p>
            </div>
            <div class="text-center">
              <p class="text-sm font-medium">{{ readyOrdersCount }}</p>
              <p class="text-xs text-muted-foreground">Ready</p>
            </div>
            <div class="text-center">
              <p class="text-sm font-medium">{{ formatCurrency(todayRevenue) }}</p>
              <p class="text-xs text-muted-foreground">Today's Sales</p>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-2">
            <!-- Notifications -->
            <Button variant="ghost" size="sm" class="relative">
              <Bell class="h-4 w-4" />
              <Badge
                v-if="notificationCount > 0"
                class="absolute -top-1 -right-1 h-5 w-5 rounded-full p-0 flex items-center justify-center text-xs"
              >
                {{ notificationCount }}
              </Badge>
            </Button>

            <!-- User Dropdown -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm" class="relative h-8 w-8 rounded-full">
                  <Avatar class="h-8 w-8">
                    <AvatarImage :src="user?.avatar || ''" :alt="user?.name || 'User'" />
                    <AvatarFallback>{{ userInitials }}</AvatarFallback>
                  </Avatar>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56" align="end" forceMount>
                <DropdownMenuLabel class="font-normal">
                  <div class="flex flex-col space-y-1">
                    <p class="text-sm font-medium leading-none">{{ user?.name }}</p>
                    <p class="text-xs leading-none text-muted-foreground">
                      {{ user?.email }}
                    </p>
                  </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="router.visit('/dashboard')" v-if="canAccessAdmin">
                  <BarChart3 class="mr-2 h-4 w-4" />
                  <span>Admin Dashboard</span>
                </DropdownMenuItem>
                <DropdownMenuItem @click="router.visit('/profile')">
                  <User class="mr-2 h-4 w-4" />
                  <span>Profile</span>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="logout">
                  <LogOut class="mr-2 h-4 w-4" />
                  <span>Log out</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="border-b bg-muted/30">
      <div class="container mx-auto px-4">
        <div class="flex space-x-8">
          <Link
            :href="route('kasir.dashboard')"
            class="flex items-center space-x-2 py-4 px-1 border-b-2 text-sm font-medium transition-colors"
            :class="route().current('kasir.dashboard')
              ? 'border-primary text-primary'
              : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground'"
          >
            <BarChart3 class="h-4 w-4" />
            <span>Dashboard</span>
          </Link>

          <Link
            :href="route('kasir.orders')"
            class="flex items-center space-x-2 py-4 px-1 border-b-2 text-sm font-medium transition-colors"
            :class="route().current('kasir.orders')
              ? 'border-primary text-primary'
              : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground'"
          >
            <ShoppingBag class="h-4 w-4" />
            <span>Orders</span>
            <Badge v-if="pendingOrdersCount > 0" variant="destructive" class="ml-1">
              {{ pendingOrdersCount }}
            </Badge>
          </Link>

          <Link
            :href="route('kasir.kitchen')"
            class="flex items-center space-x-2 py-4 px-1 border-b-2 text-sm font-medium transition-colors"
            :class="route().current('kasir.kitchen')
              ? 'border-primary text-primary'
              : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground'"
          >
            <ChefHat class="h-4 w-4" />
            <span>Kitchen</span>
            <Badge v-if="preparingOrdersCount > 0" variant="secondary" class="ml-1">
              {{ preparingOrdersCount }}
            </Badge>
          </Link>

          <Link
            :href="route('kasir.payments')"
            class="flex items-center space-x-2 py-4 px-1 border-b-2 text-sm font-medium transition-colors"
            :class="route().current('kasir.payments')
              ? 'border-primary text-primary'
              : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground'"
          >
            <CreditCard class="h-4 w-4" />
            <span>Payments</span>
          </Link>

          <Link
            :href="route('kasir.reports')"
            class="flex items-center space-x-2 py-4 px-1 border-b-2 text-sm font-medium transition-colors"
            :class="route().current('kasir.reports')
              ? 'border-primary text-primary'
              : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground'"
          >
            <FileText class="h-4 w-4" />
            <span>Reports</span>
          </Link>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/50 mt-auto">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between text-sm text-muted-foreground">
          <p>© {{ new Date().getFullYear() }} Self Order - Kasir Dashboard</p>
          <div class="flex items-center space-x-4">
            <span>Last updated: {{ lastUpdated }}</span>
            <Button variant="ghost" size="sm" @click="refreshData">
              <RefreshCw class="h-3 w-3 mr-1" :class="{ 'animate-spin': refreshing }" />
              Refresh
            </Button>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  UtensilsCrossed,
  Bell,
  BarChart3,
  LogOut,
  ShoppingBag,
  ChefHat,
  CreditCard,
  FileText,
  RefreshCw
} from 'lucide-vue-next'
import { useAdminStore } from '@/stores/admin'
import type { User } from '@/types'
import { useRealTimeKasir } from '@/composables/useRealTime'

const page = usePage()
const adminStore = useAdminStore()

const user = computed((): User | undefined => (page.props as any).auth?.user)

// Setup real-time features for kasir
const { isConnected } = useRealTimeKasir()
const refreshing = ref(false)
const lastUpdated = ref(new Date().toLocaleTimeString())

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map((n: string) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const canAccessAdmin = computed(() => {
  return user.value?.roles?.includes('admin')
})

// Stats from admin store
const pendingOrdersCount = computed(() => adminStore.pendingOrders.length)
const preparingOrdersCount = computed(() => adminStore.preparingOrders.length)
const readyOrdersCount = computed(() => adminStore.readyOrders.length)
const todayRevenue = computed(() => adminStore.todayRevenue)
const notificationCount = computed(() => pendingOrdersCount.value + preparingOrdersCount.value)

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const refreshData = async () => {
  refreshing.value = true
  try {
    await Promise.all([
      adminStore.fetchOrders(),
      adminStore.fetchDashboardStats()
    ])
    lastUpdated.value = new Date().toLocaleTimeString()
  } catch (error) {
    console.error('Error refreshing data:', error)
  } finally {
    refreshing.value = false
  }
}

const logout = () => {
  router.post('/logout')
}

let updateInterval: number | null = null

onMounted(async () => {
  // Initial data load
  await refreshData()

  // Setup auto-refresh every 15 seconds for kasir (more frequent than admin)
  updateInterval = window.setInterval(refreshData, 15000)
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>
