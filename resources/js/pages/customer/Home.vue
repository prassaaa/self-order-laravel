<template>
  <CustomerLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-primary to-primary/80 text-primary-foreground">
      <div class="container mx-auto px-4 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
          <div>
            <h1 class="text-4xl lg:text-6xl font-bold mb-6">
              Pesan Makanan
              <span class="block">Dengan Mudah</span>
            </h1>
            <p class="text-lg lg:text-xl mb-8 opacity-90">
              Sistem pemesanan mandiri yang memudahkan Anda menikmati hidangan lezat 
              tanpa perlu menunggu lama. Pesan sekarang, bayar mudah!
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <Button 
                size="lg" 
                variant="secondary"
                @click="router.visit('/menu')"
              >
                <UtensilsCrossed class="h-5 w-5 mr-2" />
                Lihat Menu
              </Button>
              <Button 
                size="lg" 
                variant="outline"
                class="border-primary-foreground text-primary-foreground hover:bg-primary-foreground hover:text-primary"
                @click="scrollToHowItWorks"
              >
                Cara Pemesanan
              </Button>
            </div>
          </div>
          <div class="hidden lg:block">
            <div class="relative">
              <div class="w-full h-96 bg-primary-foreground/10 rounded-2xl flex items-center justify-center">
                <UtensilsCrossed class="h-32 w-32 text-primary-foreground/50" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Items -->
    <section class="py-16">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold mb-4">Menu Populer</h2>
          <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
            Hidangan favorit pelanggan yang paling sering dipesan
          </p>
        </div>

        <div v-if="menuStore.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="space-y-3">
            <Skeleton class="h-48 w-full rounded-lg" />
            <Skeleton class="h-4 w-3/4" />
            <Skeleton class="h-4 w-1/2" />
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <MenuCard 
            v-for="menu in menuStore.popularMenus" 
            :key="menu.id"
            :menu="menu"
            @add-to-cart="addToCart"
          />
        </div>

        <div class="text-center mt-12">
          <Button 
            size="lg" 
            variant="outline"
            @click="router.visit('/menu')"
          >
            Lihat Semua Menu
            <ArrowRight class="h-4 w-4 ml-2" />
          </Button>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-16 bg-muted/50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold mb-4">Cara Pemesanan</h2>
          <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
            Proses pemesanan yang mudah dan cepat dalam 4 langkah sederhana
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-primary-foreground">1</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Pilih Menu</h3>
            <p class="text-muted-foreground">
              Jelajahi menu kami dan pilih hidangan favorit Anda
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-primary-foreground">2</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Tambah ke Keranjang</h3>
            <p class="text-muted-foreground">
              Masukkan item ke keranjang dan atur jumlah sesuai keinginan
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-primary-foreground">3</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Checkout</h3>
            <p class="text-muted-foreground">
              Isi data pesanan dan pilih metode pembayaran yang diinginkan
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-primary-foreground">4</span>
            </div>
            <h3 class="text-xl font-semibold mb-2">Nikmati</h3>
            <p class="text-muted-foreground">
              Tunggu pesanan siap dan nikmati hidangan lezat Anda
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16">
      <div class="container mx-auto px-4">
        <div class="bg-primary rounded-2xl p-8 lg:p-12 text-center text-primary-foreground">
          <h2 class="text-3xl lg:text-4xl font-bold mb-4">
            Siap untuk Memesan?
          </h2>
          <p class="text-lg lg:text-xl mb-8 opacity-90 max-w-2xl mx-auto">
            Jangan tunggu lagi! Mulai jelajahi menu kami dan nikmati pengalaman 
            pemesanan yang mudah dan cepat.
          </p>
          <Button 
            size="lg" 
            variant="secondary"
            @click="router.visit('/menu')"
          >
            <UtensilsCrossed class="h-5 w-5 mr-2" />
            Mulai Pesan Sekarang
          </Button>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/CustomerLayout.vue'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { UtensilsCrossed, ArrowRight } from 'lucide-vue-next'
import { useMenuStore } from '@/stores/menu'
import { useCartStore } from '@/stores/cart'
import MenuCard from '@/components/customer/MenuCard.vue'

const menuStore = useMenuStore()
const cartStore = useCartStore()

const scrollToHowItWorks = () => {
  document.getElementById('how-it-works')?.scrollIntoView({ 
    behavior: 'smooth' 
  })
}

const addToCart = (menu: any, quantity: number = 1, notes?: string) => {
  cartStore.addItem(menu, quantity, notes)
}

onMounted(async () => {
  await menuStore.initializeData()
})
</script>
