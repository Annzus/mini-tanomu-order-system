<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const errorMessage = ref('')
const form = reactive({
  email: 'customer-a@example.com',
  password: 'password',
})

const demoAccounts = [
  { label: '得意先A', email: 'customer-a@example.com' },
  { label: '得意先B', email: 'customer-b@example.com' },
  { label: '管理者', email: 'admin@example.com' },
]

function useDemoAccount(email: string) {
  form.email = email
  form.password = 'password'
}

async function submit() {
  errorMessage.value = ''

  try {
    await authStore.login(form)
    router.push(authStore.isAdmin ? '/admin/orders' : '/customer/products')
  } catch {
    errorMessage.value = 'ログインに失敗しました。バックエンドの起動状態とデモユーザーを確認してください。'
  }
}
</script>

<template>
  <main class="login-layout">
    <section class="login-intro">
      <span class="eyebrow">ミニB2B受注管理デモ</span>
      <h1>得意先別価格に対応した業務用注文ワークフロー。</h1>
      <p>
        Laravel 11 と Vue 3 で、注文トランザクション、価格スナップショット、
        権限分離、CSV出力を確認できるデモです。
      </p>
    </section>

    <form class="login-panel" @submit.prevent="submit">
      <div>
        <h2>ログイン</h2>
        <p class="muted">Seederで作成されるデモアカウントを使用します。</p>
      </div>

      <label>
        メールアドレス
        <input v-model="form.email" autocomplete="username" type="email" />
      </label>

      <label>
        パスワード
        <input v-model="form.password" autocomplete="current-password" type="password" />
      </label>

      <div class="demo-buttons" aria-label="デモアカウント">
        <button
          v-for="account in demoAccounts"
          :key="account.email"
          type="button"
          @click="useDemoAccount(account.email)"
        >
          {{ account.label }}
        </button>
      </div>

      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

      <button class="primary-button" :disabled="authStore.isLoading" type="submit">
        {{ authStore.isLoading ? 'ログイン中...' : 'ログイン' }}
      </button>
    </form>
  </main>
</template>
