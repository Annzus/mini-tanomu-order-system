export type UserRole = 'admin' | 'customer'

export interface CustomerSummary {
  id: number
  code: string
  name: string
}

export interface AuthUser {
  id: number
  name: string
  email: string
  role: UserRole
  customer: CustomerSummary | null
}

export interface LoginRequest {
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  user: AuthUser
}
