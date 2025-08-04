import { ref, onMounted } from 'vue'
import axios from 'axios'

export interface PrinterStatus {
  connected: boolean
  type: string
}

export interface PrinterStatuses {
  customer_printer: PrinterStatus
  kitchen_printer: PrinterStatus
}

export function usePrinter() {
  const printerStatus = ref<PrinterStatuses>({
    customer_printer: { connected: false, type: 'Customer Receipt Printer' },
    kitchen_printer: { connected: false, type: 'Kitchen Order Printer' }
  })
  
  const loading = ref(false)
  const lastChecked = ref<Date | null>(null)

  const checkPrinterStatus = async () => {
    loading.value = true
    try {
      const response = await axios.get('/api/print/status')
      
      if (response.data.success) {
        printerStatus.value = response.data.status
        lastChecked.value = new Date()
      }
    } catch (error) {
      console.error('Failed to check printer status:', error)
    } finally {
      loading.value = false
    }
  }

  const testPrinter = async (printerType: 'customer_printer' | 'kitchen_printer') => {
    try {
      const response = await axios.post('/api/print/test', {
        printer_type: printerType
      })
      
      if (response.data.success) {
        // Update status after successful test
        printerStatus.value[printerType].connected = true
        return { success: true, message: 'Printer test successful' }
      } else {
        printerStatus.value[printerType].connected = false
        return { success: false, message: response.data.message }
      }
    } catch (error: any) {
      printerStatus.value[printerType].connected = false
      const message = error.response?.data?.message || error.message || 'Printer test failed'
      return { success: false, message }
    }
  }

  const printCustomerReceipt = async (orderId: number) => {
    try {
      const response = await axios.post(`/api/print/orders/${orderId}/customer`)
      return {
        success: response.data.success,
        message: response.data.message
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || error.message || 'Failed to print customer receipt'
      }
    }
  }

  const printKitchenReceipt = async (orderId: number) => {
    try {
      const response = await axios.post(`/api/print/orders/${orderId}/kitchen`)
      return {
        success: response.data.success,
        message: response.data.message
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || error.message || 'Failed to print kitchen receipt'
      }
    }
  }

  const printAllReceipts = async (orderId: number) => {
    try {
      const response = await axios.post(`/api/print/orders/${orderId}/all`)
      return {
        success: response.data.success,
        results: response.data.results,
        message: response.data.message
      }
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || error.message || 'Failed to print receipts'
      }
    }
  }

  const downloadPdfReceipt = async (orderId: number, orderNumber: string) => {
    try {
      const response = await axios.get(`/api/print/orders/${orderId}/pdf`, {
        responseType: 'blob'
      })
      
      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `receipt-${orderNumber}.pdf`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      
      return { success: true, message: 'PDF downloaded successfully' }
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || error.message || 'Failed to download PDF'
      }
    }
  }

  const previewReceipt = (orderId: number) => {
    const url = `/api/print/orders/${orderId}/preview`
    window.open(url, '_blank', 'width=400,height=600,scrollbars=yes')
  }

  // Auto-check printer status on mount
  onMounted(() => {
    checkPrinterStatus()
  })

  return {
    printerStatus,
    loading,
    lastChecked,
    checkPrinterStatus,
    testPrinter,
    printCustomerReceipt,
    printKitchenReceipt,
    printAllReceipts,
    downloadPdfReceipt,
    previewReceipt
  }
}

// Utility function for automatic printing based on order status
export function useAutoPrint() {
  const { printCustomerReceipt, printKitchenReceipt } = usePrinter()

  const autoPrintOnOrderCreated = async (orderId: number) => {
    // Auto-print kitchen receipt when order is created
    const kitchenResult = await printKitchenReceipt(orderId)
    console.log('Auto-print kitchen receipt:', kitchenResult)
    return kitchenResult
  }

  const autoPrintOnPaymentCompleted = async (orderId: number) => {
    // Auto-print customer receipt when payment is completed
    const customerResult = await printCustomerReceipt(orderId)
    console.log('Auto-print customer receipt:', customerResult)
    return customerResult
  }

  return {
    autoPrintOnOrderCreated,
    autoPrintOnPaymentCompleted
  }
}
