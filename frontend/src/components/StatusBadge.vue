<script setup lang="ts">
const props = defineProps<{
  status: string
}>()

const statusLabels: Record<string, string> = {
  pending: '未確定',
  confirmed: '確認済み',
  preparing: '準備中',
  delivered: '納品済み',
  cancelled: 'キャンセル',
}
</script>

<template>
  <span class="status-badge" :data-status="status">{{ statusLabels[props.status] ?? props.status }}</span>
</template>
