import { apiClient } from './client'
import type { Order, StoreOrderRequest } from '../types/order'

export async function fetchOrders(): Promise<Order[]> {
  const response = await apiClient.get<{ data: Order[] }>('/orders')
  return response.data.data
}

export async function fetchOrder(id: number): Promise<Order> {
  const response = await apiClient.get<{ data: Order }>(`/orders/${id}`)
  return response.data.data
}

export async function createOrder(payload: StoreOrderRequest): Promise<Order> {
  const response = await apiClient.post<{ data: Order }>('/orders', payload)
  return response.data.data
}
