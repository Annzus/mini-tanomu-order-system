<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { fetchOrders } from '../api/orders'
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'
import type { Order } from '../types/order'

const orders = ref<Order[]>([])
const isLoading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  loadOrders()
})

async function loadOrders() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    orders.value = await fetchOrders()
  } catch {
    errorMessage.value = '注文履歴を取得できませんでした。'
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
        <h1>注文履歴</h1>
      </div>
    </div>

    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <div class="table-panel">
      <table>
        <thead>
          <tr>
            <th>注文番号</th>
            <th>ステータス</th>
            <th>希望納品日</th>
            <th>合計金額</th>
            <th>注文日時</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td class="muted" colspan="6">注文履歴を読み込み中です。</td>
          </tr>
          <tr v-else-if="orders.length === 0">
            <td class="muted" colspan="6">注文履歴はまだありません。</td>
          </tr>
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.order_no }}</td>
            <td><StatusBadge :status="order.status" /></td>
            <td>{{ order.desired_delivery_date ?? '-' }}</td>
            <td>¥{{ order.total_amount.toLocaleString() }}</td>
            <td>{{ order.ordered_at }}</td>
            <td><RouterLink class="table-link" :to="`/customer/orders/${order.id}`">詳細</RouterLink></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppShell>
</template>
