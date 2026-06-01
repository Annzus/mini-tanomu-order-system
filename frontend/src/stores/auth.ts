import { defineStore } from 'pinia'
import * as authApi from '../api/auth'
import type { AuthUser, LoginRequest } from '../types/auth'

interface AuthState {
  token: string | null
  user: AuthUser | null
  isLoading: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('auth_token'),
    user: readStoredUser(),
    isLoading: false,
  }),
  getters: {
    isLoggedIn: (state) => Boolean(state.token && state.user),
    isAdmin: (state) => state.user?.role === 'admin',
    isCustomer: (state) => state.user?.role === 'customer',
  },
  actions: {
    async login(payload: LoginRequest) {
      this.isLoading = true

      try {
        const data = await authApi.login(payload)
        this.token = data.token
        this.user = data.user
        localStorage.setItem('auth_token', data.token)
        localStorage.setItem('auth_user', JSON.stringify(data.user))
      } finally {
        this.isLoading = false
      }
    },
    async logout() {
      if (this.token) {
        await authApi.logout().catch(() => undefined)
      }

      this.clear()
    },
    async fetchMe() {
      this.user = await authApi.fetchMe()
      localStorage.setItem('auth_user', JSON.stringify(this.user))
    },
    clear() {
      this.token = null
      this.user = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    },
  },
})

function readStoredUser(): AuthUser | null {
  const rawUser = localStorage.getItem('auth_user')

  if (!rawUser) {
    return null
  }

  try {
    return JSON.parse(rawUser) as AuthUser
  } catch {
    localStorage.removeItem('auth_user')
    return null
  }
}
