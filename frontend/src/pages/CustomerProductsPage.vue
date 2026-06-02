<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { fetchProducts } from '../api/products'
import AppShell from '../components/AppShell.vue'
import type { Product } from '../types/product'

const products = ref<Product[]>([])
const isLoading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  loadProducts()
})

async function loadProducts() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    products.value = await fetchProducts()
  } catch {
    errorMessage.value = '商品情報を取得できませんでした。時間をおいて再度お試しください。'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">得意先</span>
        <h1>商品一覧</h1>
      </div>
      <button class="primary-button" type="button" disabled>注文を送信</button>
    </div>

    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <section class="content-grid">
      <div class="table-panel">
        <table>
          <thead>
            <tr>
              <th>商品コード</th>
              <th>商品名</th>
              <th>単位</th>
              <th>価格</th>
              <th>数量</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td class="muted" colspan="5">商品情報を読み込み中です。</td>
            </tr>
            <tr v-else-if="products.length === 0">
              <td class="muted" colspan="5">表示できる商品がありません。</td>
            </tr>
            <tr v-for="product in products" :key="product.code">
              <td>{{ product.code }}</td>
              <td>
                {{ product.name }}
                <span v-if="product.is_customer_price" class="mini-badge">得意先価格</span>
              </td>
              <td>{{ product.unit }}</td>
              <td>¥{{ product.price.toLocaleString() }}</td>
              <td><input class="quantity-input" min="1" type="number" value="1" /></td>
            </tr>
          </tbody>
        </table>
      </div>

      <aside class="side-panel">
        <h2>注文内容</h2>
        <p class="muted">カート状態と注文送信は、注文APIと接続して実装します。</p>
      </aside>
    </section>
  </AppShell>
</template>
