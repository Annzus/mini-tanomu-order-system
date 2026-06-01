<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'

const orders = [
  {
    id: 1,
    orderNo: 'ORD-20260601-000001',
    customer: '札幌レストランA',
    status: 'pending',
    totalAmount: 5550,
    orderedAt: '2026-06-01 10:00',
  },
]
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">管理者</span>
        <h1>受注一覧</h1>
      </div>
      <button class="primary-button" type="button">CSV出力</button>
    </div>

    <div class="filter-row">
      <select>
        <option>すべてのステータス</option>
        <option>未確定</option>
        <option>確認済み</option>
        <option>準備中</option>
        <option>納品済み</option>
        <option>キャンセル</option>
      </select>
    </div>

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
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.orderNo }}</td>
            <td>{{ order.customer }}</td>
            <td><StatusBadge :status="order.status" /></td>
            <td>¥{{ order.totalAmount.toLocaleString() }}</td>
            <td>{{ order.orderedAt }}</td>
            <td><RouterLink :to="`/admin/orders/${order.id}`">詳細</RouterLink></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppShell>
</template>
