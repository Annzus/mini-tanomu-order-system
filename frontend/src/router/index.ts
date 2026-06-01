import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AdminOrderDetailPage from '../pages/AdminOrderDetailPage.vue'
import AdminOrdersPage from '../pages/AdminOrdersPage.vue'
import CustomerOrderDetailPage from '../pages/CustomerOrderDetailPage.vue'
import CustomerOrdersPage from '../pages/CustomerOrdersPage.vue'
import CustomerProductsPage from '../pages/CustomerProductsPage.vue'
import LoginPage from '../pages/LoginPage.vue'
import NotFoundPage from '../pages/NotFoundPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: () => defaultPath() },
    { path: '/login', name: 'login', component: LoginPage, meta: { guest: true } },
    {
      path: '/customer/products',
      name: 'customer-products',
      component: CustomerProductsPage,
      meta: { role: 'customer' },
    },
    {
      path: '/customer/orders',
      name: 'customer-orders',
      component: CustomerOrdersPage,
      meta: { role: 'customer' },
    },
    {
      path: '/customer/orders/:id',
      name: 'customer-order-detail',
      component: CustomerOrderDetailPage,
      meta: { role: 'customer' },
    },
    {
      path: '/admin/orders',
      name: 'admin-orders',
      component: AdminOrdersPage,
      meta: { role: 'admin' },
    },
    {
      path: '/admin/orders/:id',
      name: 'admin-order-detail',
      component: AdminOrderDetailPage,
      meta: { role: 'admin' },
    },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundPage },
  ],
})

router.beforeEach((to) => {
  const authStore = useAuthStore()

  if (to.meta.guest && authStore.isLoggedIn) {
    return defaultPath()
  }

  const requiredRole = to.meta.role

  if (!requiredRole) {
    return true
  }

  if (!authStore.isLoggedIn) {
    return '/login'
  }

  if (authStore.user?.role !== requiredRole) {
    return defaultPath()
  }

  return true
})

window.addEventListener('auth:expired', () => {
  router.replace('/login')
})

function defaultPath() {
  const authStore = useAuthStore()

  if (authStore.user?.role === 'admin') {
    return '/admin/orders'
  }

  if (authStore.user?.role === 'customer') {
    return '/customer/products'
  }

  return '/login'
}
