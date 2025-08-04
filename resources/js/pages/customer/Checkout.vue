<template>
  <CustomerLayout>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <!-- Page Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold mb-2">Checkout</h1>
        <p class="text-muted-foreground">
          Review pesanan Anda dan lengkapi informasi untuk melanjutkan
        </p>
      </div>

      <!-- Redirect if cart is empty -->
      <div v-if="cartStore.isEmpty" class="text-center py-12">
        <ShoppingCart class="h-16 w-16 mx-auto text-muted-foreground mb-4" />
        <h2 class="text-2xl font-semibold mb-2">Keranjang Kosong</h2>
        <p class="text-muted-foreground mb-6">
          Silakan pilih menu terlebih dahulu sebelum melakukan checkout
        </p>
        <Button @click="router.visit('/menu')">
          <UtensilsCrossed class="h-4 w-4 mr-2" />
          Lihat Menu
        </Button>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Form -->
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Informasi Pesanan</CardTitle>
              <CardDescription>
                Lengkapi informasi di bawah ini untuk memproses pesanan Anda
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="customer_name">Nama Pelanggan</Label>
                  <Input 
                    id="customer_name"
                    v-model="orderForm.customer_name"
                    placeholder="Masukkan nama Anda"
                    :class="{ 'border-destructive': errors.customer_name }"
                  />
                  <p v-if="errors.customer_name" class="text-sm text-destructive">
                    {{ errors.customer_name }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="customer_phone">Nomor Telepon</Label>
                  <Input 
                    id="customer_phone"
                    v-model="orderForm.customer_phone"
                    placeholder="08xxxxxxxxxx"
                    :class="{ 'border-destructive': errors.customer_phone }"
                  />
                  <p v-if="errors.customer_phone" class="text-sm text-destructive">
                    {{ errors.customer_phone }}
                  </p>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="table_number">Nomor Meja (Opsional)</Label>
                <Input 
                  id="table_number"
                  v-model="orderForm.table_number"
                  placeholder="Contoh: A1, B5, atau kosongkan jika takeaway"
                />
              </div>

              <div class="space-y-2">
                <Label for="notes">Catatan Tambahan (Opsional)</Label>
                <Textarea 
                  id="notes"
                  v-model="orderForm.notes"
                  placeholder="Tambahkan catatan khusus untuk pesanan Anda..."
                  rows="3"
                />
              </div>
            </CardContent>
          </Card>

          <!-- Payment Method -->
          <Card>
            <CardHeader>
              <CardTitle>Metode Pembayaran</CardTitle>
              <CardDescription>
                Pilih metode pembayaran yang Anda inginkan
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  v-for="method in paymentMethods" 
                  :key="method.value"
                  class="relative"
                >
                  <input 
                    :id="method.value"
                    v-model="orderForm.payment_method"
                    :value="method.value"
                    type="radio"
                    class="peer sr-only"
                  />
                  <label 
                    :for="method.value"
                    class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-muted/50 peer-checked:border-primary peer-checked:bg-primary/5"
                  >
                    <component :is="method.icon" class="h-5 w-5" />
                    <div>
                      <p class="font-medium">{{ method.label }}</p>
                      <p class="text-sm text-muted-foreground">{{ method.description }}</p>
                    </div>
                  </label>
                </div>
              </div>
              <p v-if="errors.payment_method" class="text-sm text-destructive mt-2">
                {{ errors.payment_method }}
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Order Summary -->
        <div class="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Ringkasan Pesanan</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <!-- Order Items -->
              <div class="space-y-3">
                <div 
                  v-for="item in cartStore.items" 
                  :key="item.id"
                  class="flex justify-between items-start text-sm"
                >
                  <div class="flex-1">
                    <p class="font-medium">{{ item.name }}</p>
                    <p class="text-muted-foreground">
                      {{ item.quantity }}x {{ formatCurrency(item.price) }}
                    </p>
                    <p v-if="item.notes" class="text-xs text-muted-foreground">
                      Catatan: {{ item.notes }}
                    </p>
                  </div>
                  <p class="font-medium">
                    {{ formatCurrency(item.price * item.quantity) }}
                  </p>
                </div>
              </div>

              <Separator />

              <!-- Total -->
              <div class="flex justify-between items-center text-lg font-semibold">
                <span>Total:</span>
                <span>{{ cartStore.formattedTotal }}</span>
              </div>

              <!-- Validation Errors -->
              <div v-if="validationErrors.length > 0" class="space-y-1">
                <p 
                  v-for="error in validationErrors" 
                  :key="error"
                  class="text-sm text-destructive"
                >
                  {{ error }}
                </p>
              </div>

              <!-- Place Order Button -->
              <Button 
                class="w-full" 
                size="lg"
                :disabled="!canPlaceOrder || loading"
                @click="placeOrder"
              >
                <Loader2 v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
                <CreditCard v-else class="h-4 w-4 mr-2" />
                {{ loading ? 'Memproses...' : 'Buat Pesanan' }}
              </Button>

              <p class="text-xs text-muted-foreground text-center">
                Dengan melanjutkan, Anda menyetujui syarat dan ketentuan kami
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/CustomerLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Separator } from '@/components/ui/separator'
import { 
  ShoppingCart, 
  UtensilsCrossed, 
  CreditCard, 
  Banknote, 
  Smartphone, 
  Building2,
  Loader2
} from 'lucide-vue-next'
import { useCartStore } from '@/stores/cart'
import { useOrderStore } from '@/stores/order'

const cartStore = useCartStore()
const orderStore = useOrderStore()

const loading = ref(false)
const errors = ref<Record<string, string>>({})

const orderForm = ref({
  customer_name: '',
  customer_phone: '',
  table_number: '',
  notes: '',
  payment_method: 'cash',
})

const paymentMethods = [
  {
    value: 'cash',
    label: 'Tunai',
    description: 'Bayar langsung di kasir',
    icon: Banknote,
  },
  {
    value: 'qris',
    label: 'QRIS',
    description: 'Scan QR code untuk bayar',
    icon: Smartphone,
  },
  {
    value: 'bank_transfer',
    label: 'Transfer Bank',
    description: 'Transfer ke rekening toko',
    icon: Building2,
  },
  {
    value: 'e_wallet',
    label: 'E-Wallet',
    description: 'OVO, GoPay, DANA, dll',
    icon: Smartphone,
  },
]

const validationResult = computed(() => cartStore.validateCart())
const validationErrors = computed(() => validationResult.value.errors)

const canPlaceOrder = computed(() => {
  return validationResult.value.isValid && 
         orderForm.value.customer_name.trim() !== '' &&
         orderForm.value.payment_method !== ''
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const validateForm = () => {
  errors.value = {}

  if (!orderForm.value.customer_name.trim()) {
    errors.value.customer_name = 'Nama pelanggan wajib diisi'
  }

  if (orderForm.value.customer_phone && !/^[\+]?[0-9\-\(\)\s]+$/.test(orderForm.value.customer_phone)) {
    errors.value.customer_phone = 'Format nomor telepon tidak valid'
  }

  if (!orderForm.value.payment_method) {
    errors.value.payment_method = 'Pilih metode pembayaran'
  }

  return Object.keys(errors.value).length === 0
}

const placeOrder = async () => {
  if (!validateForm() || !canPlaceOrder.value) return

  loading.value = true

  try {
    const orderData = {
      ...orderForm.value,
      ...cartStore.prepareOrderData(),
    }

    const order = await orderStore.createOrder(orderData)
    
    // Clear cart after successful order
    cartStore.clearCart()
    
    // Redirect to order confirmation
    router.visit(`/order/${order.id}`)
    
  } catch (error: any) {
    console.error('Error placing order:', error)
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Gagal membuat pesanan. Silakan coba lagi.')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Redirect to menu if cart is empty
  if (cartStore.isEmpty) {
    router.visit('/menu')
  }
})
</script>
