<template>
  <div class="min-h-screen bg-background">
    <!-- Header -->
    <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div class="container mx-auto px-4">
        <div class="flex h-16 items-center justify-between">
          <!-- Logo -->
          <Link :href="route('home')" class="flex items-center space-x-2">
            <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
              <span class="text-primary-foreground font-bold text-sm">SO</span>
            </div>
            <span class="font-bold text-xl">Self Order</span>
          </Link>

          <!-- Navigation -->
          <nav class="hidden md:flex items-center space-x-6">
            <Link
              :href="route('home')"
              class="text-sm font-medium transition-colors hover:text-primary"
              :class="{ 'text-primary': route().current('home') }"
            >
              Beranda
            </Link>
            <Link
              :href="route('menu')"
              class="text-sm font-medium transition-colors hover:text-primary"
              :class="{ 'text-primary': route().current('menu') }"
            >
              Menu
            </Link>
            <Link
              :href="route('about')"
              class="text-sm font-medium transition-colors hover:text-primary"
              :class="{ 'text-primary': route().current('about') }"
            >
              Tentang
            </Link>
          </nav>

          <!-- Cart & Mobile Menu -->
          <div class="flex items-center space-x-4">
            <!-- Cart Button -->
            <Button
              variant="outline"
              size="sm"
              class="relative"
              @click="openCart"
            >
              <ShoppingCart class="h-4 w-4" />
              <Badge
                v-if="cartStore.itemCount > 0"
                class="absolute -top-2 -right-2 h-5 w-5 rounded-full p-0 flex items-center justify-center text-xs"
              >
                {{ cartStore.itemCount }}
              </Badge>
            </Button>

            <!-- Mobile Menu Button -->
            <Button
              variant="ghost"
              size="sm"
              class="md:hidden"
              @click="toggleMobileMenu"
            >
              <Menu class="h-4 w-4" />
            </Button>
          </div>
        </div>

        <!-- Mobile Navigation -->
        <div v-if="showMobileMenu" class="md:hidden py-4 border-t">
          <nav class="flex flex-col space-y-2">
            <Link
              :href="route('home')"
              class="px-2 py-1 text-sm font-medium transition-colors hover:text-primary"
              :class="{ 'text-primary': route().current('home') }"
              @click="closeMobileMenu"
            >
              Beranda
            </Link>
            <Link
              :href="route('menu')"
              class="px-2 py-1 text-sm font-medium transition-colors hover:text-primary"
              :class="{ 'text-primary': route().current('menu') }"
              @click="closeMobileMenu"
            >
              Menu
            </Link>
            <Link
              :href="route('about')"
              class="px-2 py-1 text-sm font-medium transition-colors hover:text-primary"
              :class="{ 'text-primary': route().current('about') }"
              @click="closeMobileMenu"
            >
              Tentang
            </Link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/50">
      <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Company Info -->
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                <span class="text-primary-foreground font-bold text-sm">SO</span>
              </div>
              <span class="font-bold text-xl">Self Order</span>
            </div>
            <p class="text-sm text-muted-foreground">
              Sistem pemesanan mandiri untuk UMKM. Pesan dengan mudah, bayar dengan cepat.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="font-semibold mb-4">Menu Cepat</h3>
            <div class="space-y-2">
              <Link :href="route('home')" class="block text-sm text-muted-foreground hover:text-primary">
                Beranda
              </Link>
              <Link :href="route('menu')" class="block text-sm text-muted-foreground hover:text-primary">
                Menu
              </Link>
              <Link :href="route('about')" class="block text-sm text-muted-foreground hover:text-primary">
                Tentang Kami
              </Link>
            </div>
          </div>

          <!-- Contact Info -->
          <div>
            <h3 class="font-semibold mb-4">Kontak</h3>
            <div class="space-y-2 text-sm text-muted-foreground">
              <div class="flex items-center space-x-2">
                <Phone class="h-4 w-4" />
                <span>(021) 1234-5678</span>
              </div>
              <div class="flex items-center space-x-2">
                <MapPin class="h-4 w-4" />
                <span>Jl. Contoh No. 123, Jakarta</span>
              </div>
              <div class="flex items-center space-x-2">
                <Clock class="h-4 w-4" />
                <span>Buka: 08:00 - 22:00</span>
              </div>
            </div>
          </div>
        </div>

        <Separator class="my-6" />

        <div class="text-center text-sm text-muted-foreground">
          © {{ new Date().getFullYear() }} Self Order. All rights reserved.
        </div>
      </div>
    </footer>

    <!-- Cart Sidebar -->
    <CartSidebar />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { ShoppingCart, Menu, Phone, MapPin, Clock } from 'lucide-vue-next'
import { useCartStore } from '@/stores/cart'
import CartSidebar from '@/components/customer/CartSidebar.vue'

const cartStore = useCartStore()
const showMobileMenu = ref(false)

const toggleMobileMenu = () => {
  showMobileMenu.value = !showMobileMenu.value
}

const closeMobileMenu = () => {
  showMobileMenu.value = false
}

const openCart = () => {
  cartStore.toggleSidebar()
}
</script>
