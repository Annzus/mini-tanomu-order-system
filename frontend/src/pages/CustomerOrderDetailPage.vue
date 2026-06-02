<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { fetchOrder } from '../api/orders'
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'
import type { Order } from '../types/order'

const route = useRoute()
const order = ref<Order | null>(null)
const isLoading = ref(false)
const errorMessage = ref('')

const orderId = computed(() => Number(route.params.id))

onMounted(() => {
  loadOrder()
})

async function loadOrder() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    order.value = await fetchOrder(orderId.value)
  } catch {
    errorMessage.value = '注文詳細を取得できませんでした。'
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
        <h1>注文詳細</h1>
      </div>
      <StatusBadge v-if="order" :status="order.status" />
    </div>

    <p v-if="isLoading" class="muted">注文詳細を読み込み中です。</p>
    <p v-else-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <div v-else-if="order" class="detail-layout">
      <section class="detail-panel">
        <h2>{{ order.order_no }}</h2>
        <dl class="detail-list">
          <div>
            <dt>注文日時</dt>
            <dd>{{ order.ordered_at }}</dd>
          </div>
          <div>
            <dt>希望納品日</dt>
            <dd>{{ order.desired_delivery_date ?? '-' }}</dd>
          </div>
          <div>
            <dt>合計金額</dt>
            <dd>¥{{ order.total_amount.toLocaleString() }}</dd>
          </div>
          <div>
            <dt>備考</dt>
            <dd>{{ order.note || '-' }}</dd>
          </div>
        </dl>
        <RouterLink class="ghost-button detail-action" to="/customer/orders">注文履歴へ戻る</RouterLink>
      </section>
      <section class="table-panel">
        <table>
          <thead>
            <tr>
              <th>商品名</th>
              <th>単位</th>
              <th>単価</th>
              <th>数量</th>
              <th>小計</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.items ?? []" :key="item.id">
              <td>{{ item.product_name }}</td>
              <td>{{ item.unit }}</td>
              <td>¥{{ item.unit_price.toLocaleString() }}</td>
              <td>{{ item.quantity }}</td>
              <td>¥{{ item.subtotal.toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </section>
    </div>
  </AppShell>
</template>
