<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { downloadAdminOrdersCsv, fetchAdminOrders } from '../api/adminOrders'
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'
import type { Order, OrderStatus } from '../types/order'

const statusOptions: Array<{ label: string; value: OrderStatus | '' }> = [
  { label: 'すべてのステータス', value: '' },
  { label: '未確定', value: 'pending' },
  { label: '確認済み', value: 'confirmed' },
  { label: '準備中', value: 'preparing' },
  { label: '納品済み', value: 'delivered' },
  { label: 'キャンセル', value: 'cancelled' },
]

const orders = ref<Order[]>([])
const selectedStatus = ref<OrderStatus | ''>('')
const isLoading = ref(false)
const isExporting = ref(false)
const errorMessage = ref('')

onMounted(() => {
  loadOrders()
})

watch(selectedStatus, () => {
  loadOrders()
})

async function loadOrders() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    orders.value = await fetchAdminOrders(selectedStatus.value)
  } catch {
    errorMessage.value = '受注一覧を取得できませんでした。'
  } finally {
    isLoading.value = false
  }
}

async function exportCsv() {
  isExporting.value = true
  errorMessage.value = ''

  try {
    const blob = await downloadAdminOrdersCsv()
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.download = 'orders.csv'
    link.click()
    URL.revokeObjectURL(url)
  } catch {
    errorMessage.value = 'CSVを出力できませんでした。'
  } finally {
    isExporting.value = false
  }
}
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">管理者</span>
        <h1>受注一覧</h1>
      </div>
      <button class="primary-button" type="button" :disabled="isExporting" @click="exportCsv">
        {{ isExporting ? '出力中' : 'CSV出力' }}
      </button>
    </div>

    <div class="filter-row">
      <select v-model="selectedStatus">
        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
    </div>

    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <div class="table-panel">
      <table>
        <thead>
          <tr>
            <th>注文番号</th>
            <th>得意先</th>
            <th>ステータス</th>
            <th>合計金額</th>
            <th>注文日時</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td class="muted" colspan="6">受注一覧を読み込み中です。</td>
          </tr>
          <tr v-else-if="orders.length === 0">
            <td class="muted" colspan="6">表示できる受注はありません。</td>
          </tr>
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.order_no }}</td>
            <td>{{ order.customer?.name ?? '-' }}</td>
            <td><StatusBadge :status="order.status" /></td>
            <td>¥{{ order.total_amount.toLocaleString() }}</td>
            <td>{{ order.ordered_at }}</td>
            <td><RouterLink class="table-link" :to="`/admin/orders/${order.id}`">詳細</RouterLink></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppShell>
</template>
