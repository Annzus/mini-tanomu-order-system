<script setup lang="ts">
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">得意先</span>
        <h1>注文詳細</h1>
      </div>
      <StatusBadge status="pending" />
    </div>

    <div class="detail-layout">
      <section class="detail-panel">
        <h2>ORD-20260601-000001</h2>
        <p class="muted">注文詳細データは得意先向け注文APIから取得します。</p>
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
            <tr>
              <td>トマト</td>
              <td>kg</td>
              <td>¥450</td>
              <td>3</td>
              <td>¥1,350</td>
            </tr>
          </tbody>
        </table>
      </section>
    </div>
  </AppShell>
</template>
