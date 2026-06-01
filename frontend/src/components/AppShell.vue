<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const navItems = computed(() => {
  if (authStore.isAdmin) {
    return [{ label: '受注一覧', to: '/admin/orders' }]
  }

  return [
    { label: '商品一覧', to: '/customer/products' },
    { label: '注文履歴', to: '/customer/orders' },
  ]
})

const roleLabel = computed(() => {
  if (authStore.user?.role === 'admin') {
    return '管理者'
  }

  if (authStore.user?.role === 'customer') {
    return '得意先'
  }

  return 'ゲスト'
})

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="app-shell">
    <aside class="sidebar">
      <RouterLink class="brand" to="/">
        <span class="brand-mark">T</span>
        <span>
          <strong>Tanomu</strong>
          <small>受注管理</small>
        </span>
      </RouterLink>

      <nav class="nav-list">
        <RouterLink v-for="item in navItems" :key="item.to" :to="item.to">
          {{ item.label }}
        </RouterLink>
      </nav>
    </aside>

    <div class="workspace">
      <header class="topbar">
        <div>
          <span class="eyebrow">{{ roleLabel }}</span>
          <strong>{{ authStore.user?.name ?? '未ログイン' }}</strong>
        </div>
        <button class="ghost-button" type="button" @click="handleLogout">ログアウト</button>
      </header>

      <main class="page-surface">
        <slot />
      </main>
    </div>
  </div>
</template>
