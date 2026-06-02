<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { fetchAdminOrder, updateAdminOrderStatus } from '../api/adminOrders'
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'
import type { Order, OrderStatus } from '../types/order'

const route = useRoute()
const order = ref<Order | null>(null)
const selectedStatus = ref<OrderStatus | ''>('')
const isLoading = ref(false)
const isUpdating = ref(false)
const errorMessage = ref('')
const statusMessage = ref('')

const orderId = computed(() => Number(route.params.id))

const nextStatusOptions = computed<Array<{ label: string; value: OrderStatus }>>(() => {
  if (!order.value) {
    return []
  }

  if (order.value.status === 'pending') {
    return [
      { label: '確認済み', value: 'confirmed' },
      { label: 'キャンセル', value: 'cancelled' },
    ]
  }

  if (order.value.status === 'confirmed') {
    return [
      { label: '準備中', value: 'preparing' },
      { label: 'キャンセル', value: 'cancelled' },
    ]
  }

  if (order.value.status === 'preparing') {
    return [{ label: '納品済み', value: 'delivered' }]
  }

  return []
})

onMounted(() => {
  loadOrder()
})

watch(nextStatusOptions, (options) => {
  selectedStatus.value = options[0]?.value ?? ''
})

async function loadOrder() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    order.value = await fetchAdminOrder(orderId.value)
  } catch {
    errorMessage.value = '受注詳細を取得できませんでした。'
  } finally {
    isLoading.value = false
  }
}

async function updateStatus() {
  if (!order.value || !selectedStatus.value) {
    return
  }

  isUpdating.value = true
  statusMessage.value = ''
  errorMessage.value = ''

  try {
    order.value = await updateAdminOrderStatus(order.value.id, selectedStatus.value)
    statusMessage.value = 'ステータスを更新しました。'
  } catch {
    errorMessage.value = 'ステータスを更新できませんでした。'
  } finally {
    isUpdating.value = false
  }
}
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">管理者</span>
        <h1>受注詳細</h1>
      </div>
      <StatusBadge v-if="order" :status="order.status" />
    </div>

    <p v-if="isLoading" class="muted">受注詳細を読み込み中です。</p>
    <p v-else-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <section v-else-if="order" class="detail-layout">
      <div class="detail-panel">
        <h2>{{ order.customer?.name ?? '-' }}</h2>
        <dl class="detail-list">
          <div>
            <dt>注文番号</dt>
            <dd>{{ order.order_no }}</dd>
          </div>
          <div>
            <dt>得意先コード</dt>
            <dd>{{ order.customer?.code ?? '-' }}</dd>
          </div>
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
        <RouterLink class="ghost-button detail-action" to="/admin/orders">受注一覧へ戻る</RouterLink>
      </div>

      <div class="side-panel">
        <h2>ステータス更新</h2>
        <p v-if="nextStatusOptions.length === 0" class="muted">この受注はこれ以上更新できません。</p>
        <select v-else v-model="selectedStatus">
          <option v-for="option in nextStatusOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
        <button
          class="primary-button"
          type="button"
          :disabled="nextStatusOptions.length === 0 || isUpdating"
          @click="updateStatus"
        >
          {{ isUpdating ? '更新中' : 'ステータスを更新' }}
        </button>
        <p v-if="statusMessage" class="muted">{{ statusMessage }}</p>
      </div>
    </section>

    <section v-if="order" class="table-panel">
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
  </AppShell>
</template>
