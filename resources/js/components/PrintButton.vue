<template>
  <div class="flex items-center space-x-2">
    <!-- Print Customer Receipt -->
    <Button
      variant="outline"
      size="sm"
      @click="printCustomerReceipt"
      :disabled="printing.customer"
    >
      <Printer class="h-4 w-4 mr-2" />
      {{ printing.customer ? 'Printing...' : 'Customer Receipt' }}
    </Button>

    <!-- Print Kitchen Receipt -->
    <Button
      variant="outline"
      size="sm"
      @click="printKitchenReceipt"
      :disabled="printing.kitchen"
    >
      <ChefHat class="h-4 w-4 mr-2" />
      {{ printing.kitchen ? 'Printing...' : 'Kitchen Receipt' }}
    </Button>

    <!-- Download PDF -->
    <Button
      variant="outline"
      size="sm"
      @click="downloadPdf"
      :disabled="printing.pdf"
    >
      <Download class="h-4 w-4 mr-2" />
      {{ printing.pdf ? 'Generating...' : 'Download PDF' }}
    </Button>

    <!-- Print All -->
    <Button
      size="sm"
      @click="printAll"
      :disabled="printing.all"
    >
      <PrinterIcon class="h-4 w-4 mr-2" />
      {{ printing.all ? 'Printing All...' : 'Print All' }}
    </Button>

    <!-- Preview -->
    <Button
      variant="ghost"
      size="sm"
      @click="previewReceipt"
    >
      <Eye class="h-4 w-4 mr-2" />
      Preview
    </Button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { 
  Printer, 
  ChefHat, 
  Download, 
  Eye,
  Printer as PrinterIcon
} from 'lucide-vue-next'
import axios from 'axios'

interface Props {
  orderId: number
  orderNumber: string
}

interface Emits {
  (e: 'print-success', type: string): void
  (e: 'print-error', error: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const printing = ref({
  customer: false,
  kitchen: false,
  pdf: false,
  all: false,
})

const printCustomerReceipt = async () => {
  printing.value.customer = true
  try {
    const response = await axios.post(`/api/print/orders/${props.orderId}/customer`)
    
    if (response.data.success) {
      emit('print-success', 'customer')
      showNotification('Success', 'Customer receipt printed successfully', 'success')
    } else {
      throw new Error(response.data.message)
    }
  } catch (error: any) {
    const message = error.response?.data?.message || error.message || 'Failed to print customer receipt'
    emit('print-error', message)
    showNotification('Error', message, 'error')
  } finally {
    printing.value.customer = false
  }
}

const printKitchenReceipt = async () => {
  printing.value.kitchen = true
  try {
    const response = await axios.post(`/api/print/orders/${props.orderId}/kitchen`)
    
    if (response.data.success) {
      emit('print-success', 'kitchen')
      showNotification('Success', 'Kitchen receipt printed successfully', 'success')
    } else {
      throw new Error(response.data.message)
    }
  } catch (error: any) {
    const message = error.response?.data?.message || error.message || 'Failed to print kitchen receipt'
    emit('print-error', message)
    showNotification('Error', message, 'error')
  } finally {
    printing.value.kitchen = false
  }
}

const downloadPdf = async () => {
  printing.value.pdf = true
  try {
    const response = await axios.get(`/api/print/orders/${props.orderId}/pdf`, {
      responseType: 'blob'
    })
    
    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `receipt-${props.orderNumber}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    
    emit('print-success', 'pdf')
    showNotification('Success', 'PDF receipt downloaded successfully', 'success')
  } catch (error: any) {
    const message = error.response?.data?.message || error.message || 'Failed to download PDF receipt'
    emit('print-error', message)
    showNotification('Error', message, 'error')
  } finally {
    printing.value.pdf = false
  }
}

const printAll = async () => {
  printing.value.all = true
  try {
    const response = await axios.post(`/api/print/orders/${props.orderId}/all`)
    
    if (response.data.success) {
      const results = response.data.results
      let successCount = 0
      let errors = []

      if (results.customer) successCount++
      else errors.push('Customer receipt failed')

      if (results.kitchen) successCount++
      else errors.push('Kitchen receipt failed')

      if (results.pdf) successCount++
      else errors.push('PDF generation failed')

      emit('print-success', 'all')
      
      if (errors.length === 0) {
        showNotification('Success', 'All receipts printed successfully', 'success')
      } else {
        showNotification('Partial Success', `${successCount} of 3 print jobs completed. ${errors.join(', ')}`, 'warning')
      }
    } else {
      throw new Error(response.data.message)
    }
  } catch (error: any) {
    const message = error.response?.data?.message || error.message || 'Failed to print receipts'
    emit('print-error', message)
    showNotification('Error', message, 'error')
  } finally {
    printing.value.all = false
  }
}

const previewReceipt = () => {
  const url = `/api/print/orders/${props.orderId}/preview`
  window.open(url, '_blank', 'width=400,height=600,scrollbars=yes')
}

const showNotification = (title: string, message: string, type: 'success' | 'error' | 'warning' = 'success') => {
  // This would integrate with your notification system
  console.log(`[${type.toUpperCase()}] ${title}: ${message}`)
  
  // You can integrate with toast notifications here
  // For example: toast.add({ title, description: message, variant: type })
}
</script>
