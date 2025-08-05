<template>
  <CustomerLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-slate-50 via-white to-slate-100 min-h-screen pt-16 lg:pt-20 overflow-hidden">
      <!-- Floating shapes -->
      <div
        ref="floatingShape1"
        class="absolute top-20 left-10 w-32 h-32 bg-gradient-to-br from-primary/10 to-primary/5 rounded-full blur-xl floating-shape opacity-0"
      ></div>
      <div
        ref="floatingShape2"
        class="absolute top-40 right-20 w-24 h-24 bg-gradient-to-br from-primary/15 to-primary/8 rounded-full blur-lg floating-shape opacity-0"
      ></div>
      <div
        ref="floatingShape3"
        class="absolute bottom-32 left-1/4 w-40 h-40 bg-gradient-to-br from-primary/8 to-primary/3 rounded-full blur-2xl floating-shape opacity-0"
      ></div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <!-- Text Content -->
          <div class="space-y-8">
            <div
              ref="heroTitle"
              class="space-y-4 hero-element opacity-0 translate-y-8"
            >
              <h1 class="text-5xl lg:text-7xl font-bold leading-tight">
                <span class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 bg-clip-text text-transparent">
                  Pesan Makanan
                </span>
                <span class="block bg-gradient-to-r from-primary via-primary/80 to-primary bg-clip-text text-transparent">
                  Dengan Mudah
                </span>
              </h1>
            </div>

            <div ref="heroSubtitle" class="hero-element opacity-0 translate-y-8">
              <p class="text-xl lg:text-2xl text-gray-600 leading-relaxed max-w-xl">
                Sistem pemesanan mandiri yang memudahkan Anda menikmati hidangan lezat
                tanpa perlu menunggu lama. Pesan sekarang, bayar mudah!
              </p>
            </div>

            <div ref="heroButtons" class="flex flex-col sm:flex-row gap-4 hero-element opacity-0 translate-y-8">
              <Button
                size="lg"
                class="group relative overflow-hidden bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                @click="router.visit('/menu')"
              >
                <span class="relative z-10 flex items-center">
                  <UtensilsCrossed class="h-5 w-5 mr-2 transition-transform group-hover:scale-110" />
                  Lihat Menu
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </Button>
              <Button
                size="lg"
                variant="outline"
                class="group border-2 border-gray-300 hover:border-primary hover:bg-primary/5 transition-all duration-300 transform hover:-translate-y-1"
                @click="scrollToHowItWorks"
              >
                <ArrowRight class="h-5 w-5 mr-2 transition-transform group-hover:translate-x-1" />
                Cara Pesan
              </Button>
            </div>

            <!-- Stats -->
            <div ref="heroStats" class="grid grid-cols-3 gap-6 pt-8 hero-element opacity-0 translate-y-8">
              <div class="text-center stats-item">
                <div class="text-3xl font-bold text-primary">500+</div>
                <div class="text-sm text-gray-600">Menu Tersedia</div>
              </div>
              <div class="text-center stats-item">
                <div class="text-3xl font-bold text-primary">24/7</div>
                <div class="text-sm text-gray-600">Layanan</div>
              </div>
              <div class="text-center stats-item">
                <div class="text-3xl font-bold text-primary">5★</div>
                <div class="text-sm text-gray-600">Rating</div>
              </div>
            </div>
          </div>

          <!-- Hero Visual dengan Lottie Animation -->
          <div ref="heroVisual" class="flex justify-center lg:justify-end hero-element opacity-0 translate-x-8">
            <div class="relative">
              <!-- Lottie Animation langsung tanpa container box -->
              <div class="relative w-96 h-96 lg:w-[500px] lg:h-[500px] flex items-center justify-center group">
                <!-- Show Lottie when data is available -->
                <Vue3Lottie
                  v-if="lottieAnimationData && !lottieError"
                  :animation-data="lottieAnimationData"
                  :height="400"
                  :width="400"
                  :loop="true"
                  :autoplay="true"
                  :speed="1"
                  class="transform group-hover:scale-105 transition-transform duration-700"
                  @onComplete="onLottieComplete"
                  @onLoopComplete="onLottieLoop"
                  @onError="onLottieError"
                />

                <!-- Enhanced Fallback/Default Visual -->
                <div v-else class="w-full h-full flex items-center justify-center">
                  <div class="text-center text-primary">
                    <!-- Animated Food Icons -->
                    <div class="relative mb-6">
                      <div class="animate-bounce">
                        <UtensilsCrossed class="h-24 w-24 mx-auto text-primary opacity-80" />
                      </div>
                      <!-- Floating Elements around Icon -->
                      <div class="absolute -top-2 -right-2 w-8 h-8 bg-primary/20 rounded-full animate-pulse"></div>
                      <div class="absolute -bottom-2 -left-2 w-6 h-6 bg-primary/30 rounded-full animate-pulse delay-300"></div>
                      <div class="absolute top-1/2 -right-4 w-4 h-4 bg-primary/25 rounded-full animate-pulse delay-150"></div>
                    </div>

                    <h3 class="text-2xl font-bold mb-2 bg-gradient-to-r from-primary to-primary/80 bg-clip-text text-transparent">
                      Hidangan Lezat
                    </h3>
                    <p class="text-lg opacity-70">
                      Siap Untuk Dipesan
                    </p>

                    <!-- Decorative Elements -->
                    <div class="flex justify-center mt-4 space-x-2">
                      <div class="w-2 h-2 bg-primary/40 rounded-full animate-pulse"></div>
                      <div class="w-2 h-2 bg-primary/60 rounded-full animate-pulse delay-100"></div>
                      <div class="w-2 h-2 bg-primary/40 rounded-full animate-pulse delay-200"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Enhanced Floating Cards -->
              <div
                ref="floatingCard1"
                class="absolute -top-6 -right-6 bg-white/90 backdrop-blur-sm rounded-xl shadow-xl p-4 transform rotate-12 hover:rotate-6 transition-all duration-500 floating-card opacity-0 translate-y-4 border border-white/20"
              >
                <div class="flex items-center space-x-3">
                  <div class="relative">
                    <div class="w-4 h-4 bg-green-500 rounded-full animate-pulse"></div>
                    <div class="absolute inset-0 w-4 h-4 bg-green-500 rounded-full animate-ping opacity-75"></div>
                  </div>
                  <div>
                    <span class="text-sm font-semibold text-gray-800">Order Ready!</span>
                    <p class="text-xs text-gray-500">Pesanan siap disajikan</p>
                  </div>
                </div>
              </div>

              <div
                ref="floatingCard2"
                class="absolute -bottom-8 -left-8 bg-white/90 backdrop-blur-sm rounded-xl shadow-xl p-4 transform -rotate-6 hover:-rotate-3 transition-all duration-500 floating-card opacity-0 translate-y-4 border border-white/20"
              >
                <div class="text-center">
                  <div class="text-2xl font-bold text-primary mb-1">15min</div>
                  <div class="text-xs text-gray-600">Rata-rata Waktu</div>
                  <div class="text-xs text-gray-500">Penyajian</div>
                </div>
              </div>

              <!-- Additional floating element -->
              <div
                ref="floatingCard3"
                class="absolute top-1/2 -left-4 bg-primary/90 backdrop-blur-sm rounded-full p-3 transform -translate-y-1/2 hover:scale-110 transition-all duration-500 floating-card opacity-0 shadow-lg"
              >
                <UtensilsCrossed class="h-5 w-5 text-white" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Items -->
    <section class="py-20 bg-gradient-to-b from-white to-slate-50">
      <div class="container mx-auto px-4">
        <div ref="featuredHeader" class="text-center mb-16">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-6">
            <UtensilsCrossed class="h-8 w-8 text-primary" />
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold mb-6 bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
            Menu Populer
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Hidangan favorit pelanggan yang paling sering dipesan dengan cita rasa yang tak terlupakan
          </p>
        </div>

        <div v-if="menuStore.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <SkeletonCard v-for="i in 6" :key="i" />
        </div>

        <div v-else ref="menuGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(menu, index) in menuStore.popularMenus"
            :key="menu.id"
            :ref="(el: any) => { if (el) menuCardRefs[index] = el as HTMLElement }"
            class="transform transition-all duration-500 hover:-translate-y-2"
          >
            <MenuCard
              :menu="menu"
              @add-to-cart="addToCart"
              class="h-full shadow-lg hover:shadow-2xl transition-shadow duration-300"
            />
          </div>
        </div>

        <div ref="viewAllButton" class="text-center mt-16">
          <Button
            size="lg"
            class="group bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 px-8 py-4"
            @click="router.visit('/menu')"
          >
            <span class="flex items-center">
              Lihat Semua Menu
              <ArrowRight class="h-5 w-5 ml-2 transition-transform group-hover:translate-x-1" />
            </span>
          </Button>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <div ref="howItWorksHeader" class="text-center mb-16">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-6">
            <ArrowRight class="h-8 w-8 text-primary" />
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold mb-6 bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
            Cara Pemesanan
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Proses pemesanan yang mudah dan cepat dalam 4 langkah sederhana
          </p>
        </div>

        <div ref="stepsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="group text-center relative">
            <!-- Step connector line -->
            <div class="hidden lg:block absolute top-8 left-full w-full h-0.5 bg-gradient-to-r from-primary/30 to-transparent transform translate-x-8"></div>

            <div class="relative">
              <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                <span class="text-2xl font-bold text-white">1</span>
              </div>
              <h3 class="text-xl font-semibold mb-3 text-gray-900">Pilih Menu</h3>
              <p class="text-gray-600 leading-relaxed">
                Jelajahi menu kami dan pilih hidangan favorit Anda dengan mudah
              </p>
            </div>
          </div>

          <div class="group text-center relative">
            <!-- Step connector line -->
            <div class="hidden lg:block absolute top-8 left-full w-full h-0.5 bg-gradient-to-r from-primary/30 to-transparent transform translate-x-8"></div>

            <div class="relative">
              <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                <span class="text-2xl font-bold text-white">2</span>
              </div>
              <h3 class="text-xl font-semibold mb-3 text-gray-900">Tambah ke Keranjang</h3>
              <p class="text-gray-600 leading-relaxed">
                Masukkan item ke keranjang dan atur jumlah sesuai keinginan
              </p>
            </div>
          </div>

          <div class="group text-center relative">
            <!-- Step connector line -->
            <div class="hidden lg:block absolute top-8 left-full w-full h-0.5 bg-gradient-to-r from-primary/30 to-transparent transform translate-x-8"></div>

            <div class="relative">
              <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                <span class="text-2xl font-bold text-white">3</span>
              </div>
              <h3 class="text-xl font-semibold mb-3 text-gray-900">Checkout</h3>
              <p class="text-gray-600 leading-relaxed">
                Isi data pesanan dan pilih metode pembayaran yang diinginkan
              </p>
            </div>
          </div>

          <div class="group text-center">
            <div class="relative">
              <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary/80 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                <span class="text-2xl font-bold text-white">4</span>
              </div>
              <h3 class="text-xl font-semibold mb-3 text-gray-900">Nikmati</h3>
              <p class="text-gray-600 leading-relaxed">
                Tunggu pesanan siap dan nikmati hidangan lezat Anda
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-primary via-primary/95 to-primary/90 relative overflow-hidden">
      <!-- Background decorations -->
      <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
        <div class="absolute bottom-10 right-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
        <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-white/8 rounded-full blur-lg"></div>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div ref="ctaSection" class="text-center text-white">
          <h2 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">
            Siap untuk Memesan?
          </h2>
          <p class="text-xl lg:text-2xl mb-12 opacity-90 max-w-3xl mx-auto leading-relaxed">
            Jangan tunggu lagi! Mulai jelajahi menu kami dan nikmati pengalaman
            pemesanan yang mudah dan cepat.
          </p>
          <Button
            size="lg"
            class="group bg-white text-primary hover:bg-gray-50 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 px-8 py-4 text-lg font-semibold"
            @click="router.visit('/menu')"
          >
            <span class="flex items-center">
              <UtensilsCrossed class="h-6 w-6 mr-3 transition-transform group-hover:scale-110" />
              Mulai Pesan Sekarang
            </span>
          </Button>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup lang="ts">
import { onMounted, ref, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import CustomerLayout from '@/layouts/CustomerLayout.vue'
import { Button } from '@/components/ui/button'
import { UtensilsCrossed, ArrowRight } from 'lucide-vue-next'
import { useMenuStore } from '@/stores/menu'
import { useCartStore } from '@/stores/cart'
import MenuCard from '@/components/customer/MenuCard.vue'
import SkeletonCard from '@/components/ui/loading/SkeletonCard.vue'
import { Vue3Lottie } from 'vue3-lottie'

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger)

const menuStore = useMenuStore()
const cartStore = useCartStore()

// Lottie animation state
const lottieAnimationData = ref<any>(null)
const lottieError = ref(false)
const lottieLoaded = ref(false)

// Inline Lottie animation data sebagai fallback
const defaultLottieAnimation = {
  v: "5.7.4",
  fr: 30,
  ip: 0,
  op: 90,
  w: 400,
  h: 400,
  nm: "Food Animation",
  ddd: 0,
  assets: [],
  layers: [
    {
      ddd: 0,
      ind: 1,
      ty: 4,
      nm: "Food Icon",
      sr: 1,
      ks: {
        o: { a: 0, k: 100, ix: 11 },
        r: {
          a: 1,
          k: [
            { i: { x: [0.667], y: [1] }, o: { x: [0.333], y: [0] }, t: 0, s: [0] },
            { i: { x: [0.667], y: [1] }, o: { x: [0.333], y: [0] }, t: 45, s: [360] },
            { t: 90, s: [720] }
          ],
          ix: 10
        },
        p: { a: 0, k: [200, 200, 0], ix: 2 },
        a: { a: 0, k: [0, 0, 0], ix: 1 },
        s: {
          a: 1,
          k: [
            { i: { x: [0.667, 0.667], y: [1, 1] }, o: { x: [0.333, 0.333], y: [0, 0] }, t: 0, s: [80, 80] },
            { i: { x: [0.667, 0.667], y: [1, 1] }, o: { x: [0.333, 0.333], y: [0, 0] }, t: 30, s: [120, 120] },
            { i: { x: [0.667, 0.667], y: [1, 1] }, o: { x: [0.333, 0.333], y: [0, 0] }, t: 60, s: [100, 100] },
            { t: 90, s: [80, 80] }
          ],
          ix: 6
        }
      },
      ao: 0,
      shapes: [
        {
          ty: "gr",
          it: [
            {
              ty: "rc",
              d: 1,
              s: { a: 0, k: [60, 60], ix: 2 },
              p: { a: 0, k: [0, 0], ix: 3 },
              r: { a: 0, k: 10, ix: 4 },
              nm: "Rectangle Path 1",
              mn: "ADBE Vector Shape - Rect",
              hd: false
            },
            {
              ty: "fl",
              c: { a: 0, k: [0.2, 0.6, 1, 1], ix: 4 },
              o: { a: 0, k: 100, ix: 5 },
              r: 1,
              bm: 0,
              nm: "Fill 1",
              mn: "ADBE Vector Graphic - Fill",
              hd: false
            }
          ],
          nm: "Rectangle 1",
          np: 2,
          cix: 2,
          bm: 0,
          ix: 1,
          mn: "ADBE Vector Group",
          hd: false
        }
      ],
      ip: 0,
      op: 90,
      st: 0,
      bm: 0
    }
  ],
  markers: []
}

// Load Lottie animation dengan multiple fallback strategies
const loadLottieAnimation = async () => {
  try {
    // Strategy 1: Try loading from correct path
    const response = await fetch('/assets/animation/hero-section.json')
    if (response.ok) {
      const data = await response.json()
      if (data && (data.layers || data.v)) { // Check for valid Lottie structure
        lottieAnimationData.value = data
        lottieLoaded.value = true
        console.log('✅ Lottie loaded successfully from /assets/animation/hero-section.json')
        return
      }
    }
  } catch (error) {
    console.warn('⚠️ Failed to load Lottie from main path:', error)
  }

  try {
    // Strategy 2: Try alternative paths just in case
    const altPaths = [
      '/public/assets/animation/hero-section.json',
      '/assets/animations/hero-section.json'
    ]

    for (const path of altPaths) {
      const response = await fetch(path)
      if (response.ok) {
        const data = await response.json()
        if (data && (data.layers || data.v)) {
          lottieAnimationData.value = data
          lottieLoaded.value = true
          console.log(`✅ Lottie loaded from alternative path: ${path}`)
          return
        }
      }
    }
  } catch (error) {
    console.warn('⚠️ Failed to load from alternative paths:', error)
  }

  // Strategy 3: Use default inline animation as last resort
  console.log('📝 Using default inline animation as fallback')
  lottieAnimationData.value = defaultLottieAnimation
  lottieLoaded.value = true
}

// Lottie event handlers
const onLottieComplete = () => {
  console.log('🎬 Lottie animation completed')
}

const onLottieLoop = () => {
  console.log('🔄 Lottie animation looped')
}

const onLottieError = (error: any) => {
  console.error('❌ Lottie animation error:', error)
  lottieError.value = true
}

// Template refs - tetap sama seperti sebelumnya
const heroTitle = ref<HTMLElement>()
const heroSubtitle = ref<HTMLElement>()
const heroButtons = ref<HTMLElement>()
const heroStats = ref<HTMLElement>()
const heroVisual = ref<HTMLElement>()
const floatingShape1 = ref<HTMLElement>()
const floatingShape2 = ref<HTMLElement>()
const floatingShape3 = ref<HTMLElement>()
const floatingCard1 = ref<HTMLElement>()
const floatingCard2 = ref<HTMLElement>()
const floatingCard3 = ref<HTMLElement>()

// Menu card refs for animations
const menuCardRefs = ref<HTMLElement[]>([])

const scrollToHowItWorks = () => {
  document.getElementById('how-it-works')?.scrollIntoView({
    behavior: 'smooth'
  })
}

const addToCart = (menu: any, quantity: number = 1, notes?: string) => {
  cartStore.addItem(menu, quantity, notes)
  cartStore.openSidebar()
}

// Initialize hero animations - tetap sama
const initializeHeroAnimations = () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

  if (prefersReducedMotion) {
    const elements = [
      heroTitle.value,
      heroSubtitle.value,
      heroButtons.value,
      heroStats.value,
      heroVisual.value,
      floatingShape1.value,
      floatingShape2.value,
      floatingShape3.value,
      floatingCard1.value,
      floatingCard2.value,
      floatingCard3.value
    ]

    elements.forEach(el => {
      if (el) {
        gsap.set(el, { opacity: 1, x: 0, y: 0 })
      }
    })
    return
  }

  const tl = gsap.timeline({
    defaults: {
      ease: "power3.out",
      duration: 0.8
    }
  })

  if (heroTitle.value) {
    tl.to(heroTitle.value, {
      opacity: 1,
      y: 0,
      duration: 1
    })
  }

  if (heroSubtitle.value) {
    tl.to(heroSubtitle.value, {
      opacity: 1,
      y: 0,
      duration: 0.8
    }, "-=0.6")
  }

  if (heroButtons.value) {
    tl.to(heroButtons.value, {
      opacity: 1,
      y: 0,
      duration: 0.8
    }, "-=0.4")
  }

  if (heroStats.value) {
    tl.to(heroStats.value, {
      opacity: 1,
      y: 0,
      duration: 0.8
    }, "-=0.4")

    const statsItems = heroStats.value.querySelectorAll('.stats-item')
    if (statsItems.length > 0) {
      tl.to(statsItems, {
        opacity: 1,
        y: 0,
        duration: 0.6,
        stagger: 0.1
      }, "-=0.4")
    }
  }

  if (heroVisual.value) {
    tl.to(heroVisual.value, {
      opacity: 1,
      x: 0,
      duration: 1
    }, "-=0.8")
  }

  const floatingShapes = [floatingShape1.value, floatingShape2.value, floatingShape3.value].filter(Boolean)
  if (floatingShapes.length > 0) {
    tl.to(floatingShapes, {
      opacity: 0.7,
      duration: 1.5,
      stagger: 0.2,
      ease: "power2.out"
    }, "-=1")
  }

  if (floatingCard1.value) {
    tl.to(floatingCard1.value, {
      opacity: 1,
      y: 0,
      duration: 0.8,
      ease: "back.out(1.7)"
    }, "-=0.5")
  }

  if (floatingCard2.value) {
    tl.to(floatingCard2.value, {
      opacity: 1,
      y: 0,
      duration: 0.8,
      ease: "back.out(1.7)"
    }, "-=0.6")
  }

  if (floatingCard3.value) {
    tl.to(floatingCard3.value, {
      opacity: 1,
      scale: 1,
      duration: 0.6,
      ease: "back.out(1.7)"
    }, "-=0.4")
  }

  return tl
}

onMounted(async () => {
  try {
    // Load Lottie animation first (synchronously to avoid loading state)
    await loadLottieAnimation()

    // Initialize menu data
    await menuStore.initializeData()

    // Wait for DOM to be fully ready
    await nextTick()

    // Initialize animations immediately after Lottie is loaded
    setTimeout(() => {
      initializeHeroAnimations()
      // initializeScrollAnimations() - tambahkan jika diperlukan
    }, 50) // Reduced delay

  } catch (error) {
    console.error('❌ Animation initialization error:', error)

    // Ensure fallback is shown
    lottieError.value = true

    // Make all elements visible
    const allElements = [
      heroTitle.value,
      heroSubtitle.value,
      heroButtons.value,
      heroStats.value,
      heroVisual.value,
      floatingShape1.value,
      floatingShape2.value,
      floatingShape3.value,
      floatingCard1.value,
      floatingCard2.value,
      floatingCard3.value
    ]

    allElements.forEach(el => {
      if (el) {
        el.style.opacity = '1'
        el.style.transform = 'translate(0, 0)'
      }
    })
  }
})
</script>

<style scoped>
/* Hero elements - start hidden for animation */
.hero-element {
  opacity: 0;
  transform: translateY(2rem);
}

/* Stats items - start hidden */
.stats-item {
  opacity: 0;
  transform: translateY(1rem);
}

/* Floating elements - start hidden */
.floating-shape {
  opacity: 0;
}

.floating-card {
  opacity: 0;
  transform: translateY(1rem);
}

/* Third floating card starts scaled down */
[ref="floatingCard3"] {
  opacity: 0;
  transform: scale(0.8);
}

/* Enhanced fallback animations */
@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0,0,0);
  }
  40%, 43% {
    transform: translate3d(0,-15px,0);
  }
  70% {
    transform: translate3d(0,-7px,0);
  }
  90% {
    transform: translate3d(0,-2px,0);
  }
}

@keyframes pulse {
  0% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
  100% {
    opacity: 1;
  }
}

/* Custom animations for continuous floating effect */
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

@keyframes float-delayed {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-8px);
  }
}

.floating-shape:nth-child(1) {
  animation: float 6s ease-in-out infinite;
  animation-delay: 2s;
}

.floating-shape:nth-child(2) {
  animation: float 6s ease-in-out infinite;
  animation-delay: 4s;
}

.floating-shape:nth-child(3) {
  animation: float-delayed 8s ease-in-out infinite;
  animation-delay: 3s;
}

.floating-card {
  animation: float 4s ease-in-out infinite;
  animation-delay: 3s;
}

.floating-card:nth-child(2) {
  animation-delay: 4s;
}

/* Enhanced hover effects */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.group:hover .group-hover\:translate-x-1 {
  transform: translateX(0.25rem);
}

/* Smooth transitions */
.transition-all {
  transition-property: transform, opacity, box-shadow, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Accessibility: Respect reduced motion preference */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }

  .hero-element,
  .stats-item,
  .floating-shape,
  .floating-card {
    opacity: 1 !important;
    transform: translate(0, 0) !important;
  }
}
</style>
