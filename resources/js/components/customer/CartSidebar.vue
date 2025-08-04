<template>
  <Sheet :open="cartStore.isOpen" @update:open="cartStore.closeSidebar">
    <SheetContent side="right" class="w-full sm:max-w-lg">
      <SheetHeader>
        <SheetTitle class="flex items-center justify-between">
          <span>Keranjang Belanja</span>
          <Badge v-if="cartStore.itemCount > 0" variant="secondary">
            {{ cartStore.itemCount }} item
          </Badge>
        </SheetTitle>
        <SheetDescription>
          Review pesanan Anda sebelum checkout
        </SheetDescription>
      </SheetHeader>

      <div class="flex flex-col h-full">
        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto py-4">
          <div v-if="cartStore.isEmpty" class="text-center py-8">
            <ShoppingCart class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <p class="text-muted-foreground">Keranjang Anda kosong</p>
            <Button 
              variant="outline" 
              class="mt-4"
              @click="cartStore.closeSidebar"
            >
              Mulai Belanja
            </Button>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="item in cartStore.items" 
              :key="item.id"
              class="flex gap-3 p-3 border rounded-lg"
            >
              <!-- Item Image -->
              <div class="w-16 h-16 rounded-md overflow-hidden bg-muted flex-shrink-0">
                <img 
                  v-if="item.image"
                  :src="item.image" 
                  :alt="item.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <ImageIcon class="h-6 w-6 text-muted-foreground" />
                </div>
              </div>

              <!-- Item Details -->
              <div class="flex-1 min-w-0">
                <h4 class="font-medium text-sm truncate">{{ item.name }}</h4>
                <p v-if="item.category" class="text-xs text-muted-foreground">
                  {{ item.category }}
                </p>
                <p class="text-sm font-medium text-primary">
                  {{ formatCurrency(item.price) }}
                </p>
                <p v-if="item.notes" class="text-xs text-muted-foreground mt-1">
                  Catatan: {{ item.notes }}
                </p>

                <!-- Quantity Controls -->
                <div class="flex items-center gap-2 mt-2">
                  <Button 
                    variant="outline" 
                    size="sm"
                    class="h-8 w-8 p-0"
                    @click="decreaseQuantity(item)"
                  >
                    <Minus class="h-3 w-3" />
                  </Button>
                  <span class="text-sm font-medium w-8 text-center">
                    {{ item.quantity }}
                  </span>
                  <Button 
                    variant="outline" 
                    size="sm"
                    class="h-8 w-8 p-0"
                    @click="increaseQuantity(item)"
                  >
                    <Plus class="h-3 w-3" />
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="sm"
                    class="h-8 w-8 p-0 ml-2 text-destructive hover:text-destructive"
                    @click="removeItem(item)"
                  >
                    <Trash2 class="h-3 w-3" />
                  </Button>
                </div>
              </div>

              <!-- Item Total -->
              <div class="text-right">
                <p class="text-sm font-medium">
                  {{ formatCurrency(item.price * item.quantity) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Summary & Checkout -->
        <div v-if="!cartStore.isEmpty" class="border-t pt-4 space-y-4">
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

          <!-- Checkout Button -->
          <Button 
            class="w-full" 
            size="lg"
            :disabled="!isValidCart"
            @click="proceedToCheckout"
          >
            <CreditCard class="h-4 w-4 mr-2" />
            Lanjut ke Checkout
          </Button>

          <!-- Clear Cart -->
          <Button 
            variant="outline" 
            class="w-full"
            @click="clearCart"
          >
            Kosongkan Keranjang
          </Button>
        </div>
      </div>
    </SheetContent>
  </Sheet>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  Sheet, 
  SheetContent, 
  SheetHeader, 
  SheetTitle, 
  SheetDescription 
} from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { 
  ShoppingCart, 
  ImageIcon, 
  Minus, 
  Plus, 
  Trash2, 
  CreditCard 
} from 'lucide-vue-next'
import { useCartStore, type CartItem } from '@/stores/cart'

const cartStore = useCartStore()

const validationResult = computed(() => cartStore.validateCart())
const isValidCart = computed(() => validationResult.value.isValid)
const validationErrors = computed(() => validationResult.value.errors)

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const increaseQuantity = (item: CartItem) => {
  cartStore.updateQuantity(item.id, item.quantity + 1)
}

const decreaseQuantity = (item: CartItem) => {
  cartStore.updateQuantity(item.id, item.quantity - 1)
}

const removeItem = (item: CartItem) => {
  cartStore.removeItem(item.id)
}

const clearCart = () => {
  if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
    cartStore.clearCart()
  }
}

const proceedToCheckout = () => {
  if (isValidCart.value) {
    cartStore.closeSidebar()
    router.visit('/checkout')
  }
}
</script>
