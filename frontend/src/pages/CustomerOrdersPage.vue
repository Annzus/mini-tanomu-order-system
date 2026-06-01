<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'

const orders = [
  {
    id: 1,
    orderNo: 'ORD-20260601-000001',
    status: 'pending',
    desiredDeliveryDate: '2026-06-05',
    totalAmount: 5550,
    orderedAt: '2026-06-01 10:00',
  },
]
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">得意先</span>
        <h1>注文履歴</h1>
      </div>
    </div>

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
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.orderNo }}</td>
            <td><StatusBadge :status="order.status" /></td>
            <td>{{ order.desiredDeliveryDate }}</td>
            <td>¥{{ order.totalAmount.toLocaleString() }}</td>
            <td>{{ order.orderedAt }}</td>
            <td><RouterLink :to="`/customer/orders/${order.id}`">詳細</RouterLink></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppShell>
</template>
