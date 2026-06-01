import { apiClient } from './client'
import type { AuthUser, LoginRequest, LoginResponse } from '../types/auth'

export async function login(payload: LoginRequest): Promise<LoginResponse> {
  const response = await apiClient.post<LoginResponse>('/login', payload)
  return response.data
}

export async function logout(): Promise<void> {
  await apiClient.post('/logout')
}

export async function fetchMe(): Promise<AuthUser> {
  const response = await apiClient.get<{ user: AuthUser }>('/me')
  return response.data.user
}
