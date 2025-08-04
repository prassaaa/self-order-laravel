<template>
  <Card class="overflow-hidden hover:shadow-lg transition-shadow">
    <div class="relative">
      <!-- Menu Image -->
      <div class="aspect-video bg-muted relative overflow-hidden">
        <img 
          v-if="menu.image_url"
          :src="menu.image_url" 
          :alt="menu.name"
          class="w-full h-full object-cover transition-transform hover:scale-105"
        />
        <div v-else class="w-full h-full flex items-center justify-center">
          <ImageIcon class="h-12 w-12 text-muted-foreground" />
        </div>
        
        <!-- Availability Badge -->
        <Badge 
          v-if="!menu.is_available"
          variant="destructive"
          class="absolute top-2 right-2"
        >
          Tidak Tersedia
        </Badge>
      </div>

      <!-- Menu Content -->
      <CardContent class="p-4">
        <div class="space-y-2">
          <!-- Category -->
          <p v-if="menu.category" class="text-xs text-muted-foreground uppercase tracking-wide">
            {{ menu.category.name }}
          </p>

          <!-- Name -->
          <h3 class="font-semibold text-lg leading-tight">
            {{ menu.name }}
          </h3>

          <!-- Description -->
          <p v-if="menu.description" class="text-sm text-muted-foreground line-clamp-2">
            {{ menu.description }}
          </p>

          <!-- Price -->
          <div class="flex items-center justify-between">
            <span class="text-lg font-bold text-primary">
              {{ menu.formatted_price }}
            </span>
            
            <!-- Quick Add Button -->
            <Button 
              v-if="menu.is_available"
              size="sm"
              @click="quickAdd"
              :disabled="loading"
            >
              <Plus class="h-4 w-4 mr-1" />
              Tambah
            </Button>
          </div>
        </div>
      </CardContent>
    </div>

    <!-- Detailed Add Dialog -->
    <Dialog v-model:open="showDialog">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{{ menu.name }}</DialogTitle>
          <DialogDescription v-if="menu.description">
            {{ menu.description }}
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4">
          <!-- Menu Image -->
          <div v-if="menu.image_url" class="aspect-video bg-muted rounded-lg overflow-hidden">
            <img 
              :src="menu.image_url" 
              :alt="menu.name"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Price -->
          <div class="text-center">
            <span class="text-2xl font-bold text-primary">
              {{ menu.formatted_price }}
            </span>
          </div>

          <!-- Quantity Selector -->
          <div class="flex items-center justify-center gap-4">
            <Button 
              variant="outline" 
              size="sm"
              @click="decreaseQuantity"
              :disabled="quantity <= 1"
            >
              <Minus class="h-4 w-4" />
            </Button>
            <span class="text-lg font-medium w-12 text-center">
              {{ quantity }}
            </span>
            <Button 
              variant="outline" 
              size="sm"
              @click="increaseQuantity"
              :disabled="quantity >= 99"
            >
              <Plus class="h-4 w-4" />
            </Button>
          </div>

          <!-- Notes -->
          <div class="space-y-2">
            <Label for="notes">Catatan (Opsional)</Label>
            <Textarea 
              id="notes"
              v-model="notes"
              placeholder="Tambahkan catatan khusus untuk pesanan ini..."
              rows="3"
            />
          </div>

          <!-- Total Price -->
          <div class="flex justify-between items-center text-lg font-semibold border-t pt-4">
            <span>Total:</span>
            <span class="text-primary">
              {{ formatCurrency(menu.price * quantity) }}
            </span>
          </div>
        </div>

        <DialogFooter class="gap-2">
          <Button 
            variant="outline" 
            @click="closeDialog"
          >
            Batal
          </Button>
          <Button 
            @click="addToCart"
            :disabled="loading"
          >
            <ShoppingCart class="h-4 w-4 mr-2" />
            Tambah ke Keranjang
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { 
  Dialog, 
  DialogContent, 
  DialogHeader, 
  DialogTitle, 
  DialogDescription,
  DialogFooter 
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { 
  ImageIcon, 
  Plus, 
  Minus, 
  ShoppingCart 
} from 'lucide-vue-next'

interface Menu {
  id: number
  name: string
  description?: string
  price: number
  formatted_price: string
  image_url?: string
  is_available: boolean
  category?: {
    id: number
    name: string
  }
}

interface Props {
  menu: Menu
}

interface Emits {
  (e: 'add-to-cart', menu: Menu, quantity: number, notes?: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const showDialog = ref(false)
const quantity = ref(1)
const notes = ref('')
const loading = ref(false)

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const quickAdd = () => {
  if (props.menu.is_available) {
    emit('add-to-cart', props.menu, 1)
  }
}

const increaseQuantity = () => {
  if (quantity.value < 99) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const addToCart = () => {
  if (props.menu.is_available) {
    loading.value = true
    
    setTimeout(() => {
      emit('add-to-cart', props.menu, quantity.value, notes.value || undefined)
      closeDialog()
      loading.value = false
    }, 300)
  }
}

const closeDialog = () => {
  showDialog.value = false
  quantity.value = 1
  notes.value = ''
}

const openDialog = () => {
  if (props.menu.is_available) {
    showDialog.value = true
  }
}

// Expose openDialog for parent components
defineExpose({
  openDialog
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
