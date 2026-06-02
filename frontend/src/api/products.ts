import { apiClient } from './client'
import type { Product } from '../types/product'

export async function fetchProducts(): Promise<Product[]> {
  const response = await apiClient.get<{ data: Product[] }>('/products')
  return response.data.data
}

export async function fetchProduct(id: number): Promise<Product> {
  const response = await apiClient.get<{ data: Product }>(`/products/${id}`)
  return response.data.data
}
