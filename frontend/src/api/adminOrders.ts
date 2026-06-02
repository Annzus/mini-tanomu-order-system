import { apiClient } from './client'
import type { Order, OrderStatus } from '../types/order'

export async function fetchAdminOrders(status?: OrderStatus | ''): Promise<Order[]> {
  const response = await apiClient.get<{ data: Order[] }>('/admin/orders', {
    params: status ? { status } : undefined,
  })

  return response.data.data
}

export async function fetchAdminOrder(id: number): Promise<Order> {
  const response = await apiClient.get<{ data: Order }>(`/admin/orders/${id}`)
  return response.data.data
}

export async function updateAdminOrderStatus(id: number, status: OrderStatus): Promise<Order> {
  const response = await apiClient.patch<{ data: Order }>(`/admin/orders/${id}/status`, { status })
  return response.data.data
}

export async function downloadAdminOrdersCsv(): Promise<Blob> {
  const response = await apiClient.get<Blob>('/admin/orders/export', {
    responseType: 'blob',
  })

  return response.data
}
