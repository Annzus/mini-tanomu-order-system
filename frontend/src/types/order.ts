export type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'delivered' | 'cancelled'

export interface OrderItem {
  id: number
  product_id: number
  product_code: string
  product_name: string
  unit: string
  unit_price: number
  quantity: number
  subtotal: number
}

export interface Order {
  id: number
  order_no: string
  status: OrderStatus
  desired_delivery_date: string | null
  note: string | null
  total_amount: number
  ordered_at: string
  items_count?: number
  items?: OrderItem[]
}

export interface StoreOrderItem {
  product_id: number
  quantity: number
}

export interface StoreOrderRequest {
  desired_delivery_date?: string | null
  note?: string | null
  items: StoreOrderItem[]
}
