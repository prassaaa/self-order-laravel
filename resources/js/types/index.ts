export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  avatar?: string
  roles?: string[]
  permissions?: string[]
  created_at: string
  updated_at: string
}

// Navigation Types
export interface NavItem {
  title: string
  href?: string
  icon?: any
  badge?: string | number
  children?: NavItem[]
  disabled?: boolean
}

export interface BreadcrumbItem {
  title: string
  href?: string
}

export interface AuthProps {
  user: User
}

export interface PageProps {
  auth?: AuthProps
  ziggy?: {
    route?: {
      name?: string
    }
  }
  [key: string]: any
}

export interface InertiaPageProps extends PageProps {
  errors: Record<string, string>
  flash: {
    message?: string
    error?: string
    success?: string
  }
}

// Menu & Category Types
export interface Category {
  id: number
  name: string
  slug: string
  description?: string
  image?: string
  image_url?: string
  is_active: boolean
  sort_order: number
  menus_count?: number
  created_at: string
  updated_at: string
}

export interface Menu {
  id: number
  category_id: number
  name: string
  description?: string
  price: number
  formatted_price: string
  image?: string
  image_url?: string
  is_available: boolean
  sort_order: number
  category?: Category
  created_at: string
  updated_at: string
}

// Order Types
export interface OrderItem {
  id: number
  order_id: number
  menu_id: number
  quantity: number
  price: number
  subtotal: number
  notes?: string
  menu?: Menu
  created_at: string
  updated_at: string
}

export interface Order {
  id: number
  order_number: string
  table_number?: string
  status: 'pending' | 'confirmed' | 'preparing' | 'ready' | 'completed' | 'cancelled'
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded'
  total_amount: number
  formatted_total: string
  customer_name?: string
  customer_phone?: string
  notes?: string
  order_items: OrderItem[]
  payments?: Payment[]
  is_paid: boolean
  can_be_cancelled: boolean
  created_at: string
  updated_at: string
  created_at_human: string
}

// Payment Types
export interface Payment {
  id: number
  order_id: number
  amount: number
  method: 'cash' | 'qris' | 'bank_transfer' | 'e_wallet'
  status: 'pending' | 'completed' | 'failed' | 'refunded'
  transaction_id?: string
  notes?: string
  processed_by?: number
  processed_at?: string
  created_at: string
  updated_at: string
  order?: Order
  processedBy?: User
}

// API Response Types
export interface ApiResponse<T> {
  data: T
  message?: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
  links: {
    first: string
    last: string
    prev?: string
    next?: string
  }
}

// Form Types
export interface LoginForm {
  email: string
  password: string
  remember: boolean
}

export interface RegisterForm {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface OrderForm {
  customer_name?: string
  customer_phone?: string
  table_number?: string
  notes?: string
  payment_method: string
  items: Array<{
    menu_id: number
    quantity: number
    notes?: string
  }>
}

export interface PaymentForm {
  amount: number
  method: string
  transaction_id?: string
  notes?: string
}

// Dashboard Stats Types
export interface DashboardStats {
  total_orders: number
  pending_orders: number
  completed_orders: number
  total_revenue: number
  today_orders: number
  today_revenue: number
  popular_items: Array<{
    id: number
    name: string
    total_ordered: number
    revenue: number
  }>
  recent_orders: Array<{
    id: number
    order_number: string
    customer_name?: string
    total_amount: number
    status: string
    created_at: string
  }>
}

// Filter Types
export interface OrderFilters {
  status?: string
  payment_status?: string
  search?: string
  date_from?: string
  date_to?: string
  per_page?: number
}

export interface MenuFilters {
  category_id?: number
  search?: string
  available?: boolean
  per_page?: number
}

// Cart Types
export interface CartItem {
  id: number
  menu_id: number
  name: string
  price: number
  quantity: number
  notes?: string
  image?: string
  category?: string
}

// Notification Types
export interface Notification {
  id: string
  type: 'info' | 'success' | 'warning' | 'error'
  title: string
  message: string
  timestamp: string
  read: boolean
}

// Settings Types
export interface SystemSettings {
  restaurant_name: string
  restaurant_address: string
  restaurant_phone: string
  restaurant_email: string
  currency: string
  timezone: string
  tax_rate: number
  service_charge: number
  minimum_order_amount: number
  maximum_order_amount: number
}
