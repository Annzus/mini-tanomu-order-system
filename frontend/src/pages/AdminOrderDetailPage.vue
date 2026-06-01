<script setup lang="ts">
import AppShell from '../components/AppShell.vue'
import StatusBadge from '../components/StatusBadge.vue'
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">管理者</span>
        <h1>受注詳細</h1>
      </div>
      <StatusBadge status="pending" />
    </div>

    <section class="detail-layout">
      <div class="detail-panel">
        <h2>札幌レストランA</h2>
        <p class="muted">得意先情報と注文ヘッダーは管理者向け注文APIから取得します。</p>
      </div>

      <div class="side-panel">
        <h2>ステータス更新</h2>
        <select>
          <option>確認済み</option>
          <option>キャンセル</option>
        </select>
        <button class="primary-button" type="button">ステータスを更新</button>
      </div>
    </section>
  </AppShell>
</template>
