<script setup lang="ts">
import AppShell from '../components/AppShell.vue'

const products = [
  { code: 'P-001', name: 'トマト', unit: 'kg', price: 450, defaultPrice: 500, customerPrice: true },
  { code: 'P-004', name: '牛肉', unit: 'kg', price: 2100, defaultPrice: 2200, customerPrice: true },
  { code: 'P-006', name: '米', unit: '袋', price: 3500, defaultPrice: 3500, customerPrice: false },
]
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">得意先</span>
        <h1>商品一覧</h1>
      </div>
      <button class="primary-button" type="button">注文を送信</button>
    </div>

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
            <tr v-for="product in products" :key="product.code">
              <td>{{ product.code }}</td>
              <td>
                {{ product.name }}
                <span v-if="product.customerPrice" class="mini-badge">得意先価格</span>
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
