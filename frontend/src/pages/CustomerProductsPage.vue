<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { createOrder } from '../api/orders'
import { fetchProducts } from '../api/products'
import AppShell from '../components/AppShell.vue'
import type { Product } from '../types/product'

const router = useRouter()
const products = ref<Product[]>([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')
const submitMessage = ref('')
const desiredDeliveryDate = ref('')
const note = ref('')
const quantities = ref<Record<number, number | null>>({})

const cartItems = computed(() =>
  products.value
    .map((product) => {
      const quantity = Number(quantities.value[product.id] ?? 0)
      const normalizedQuantity = Number.isFinite(quantity) && quantity > 0 ? Math.floor(quantity) : 0

      return {
        product,
        quantity: normalizedQuantity,
        subtotal: product.price * normalizedQuantity,
      }
    })
    .filter((item) => item.quantity > 0),
)

const totalAmount = computed(() => cartItems.value.reduce((sum, item) => sum + item.subtotal, 0))

onMounted(() => {
  loadProducts()
})

async function loadProducts() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    products.value = await fetchProducts()
  } catch {
    errorMessage.value = '商品情報を取得できませんでした。時間をおいて再度お試しください。'
  } finally {
    isLoading.value = false
  }
}

async function submitOrder() {
  if (cartItems.value.length === 0) {
    submitMessage.value = '数量を入力してください。'
    return
  }

  isSubmitting.value = true
  submitMessage.value = ''
  errorMessage.value = ''

  try {
    const order = await createOrder({
      desired_delivery_date: desiredDeliveryDate.value || null,
      note: note.value || null,
      items: cartItems.value.map((item) => ({
        product_id: item.product.id,
        quantity: item.quantity,
      })),
    })

    await router.push(`/customer/orders/${order.id}`)
  } catch {
    submitMessage.value = '注文を送信できませんでした。数量と納品日を確認してください。'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <AppShell>
    <div class="page-header">
      <div>
        <span class="eyebrow">得意先</span>
        <h1>商品一覧</h1>
      </div>
      <button
        class="primary-button"
        type="button"
        :disabled="cartItems.length === 0 || isSubmitting"
        @click="submitOrder"
      >
        {{ isSubmitting ? '送信中' : '注文を送信' }}
      </button>
    </div>

    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

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
            <tr v-if="isLoading">
              <td class="muted" colspan="5">商品情報を読み込み中です。</td>
            </tr>
            <tr v-else-if="products.length === 0">
              <td class="muted" colspan="5">表示できる商品がありません。</td>
            </tr>
            <tr v-for="product in products" :key="product.code">
              <td>{{ product.code }}</td>
              <td>
                {{ product.name }}
                <span v-if="product.is_customer_price" class="mini-badge">得意先価格</span>
              </td>
              <td>{{ product.unit }}</td>
              <td>¥{{ product.price.toLocaleString() }}</td>
              <td>
                <input
                  v-model.number="quantities[product.id]"
                  class="quantity-input"
                  min="1"
                  type="number"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <aside class="side-panel">
        <h2>注文内容</h2>
        <div class="form-stack">
          <label>
            希望納品日
            <input v-model="desiredDeliveryDate" type="date" />
          </label>
          <label>
            備考
            <textarea v-model="note" rows="3" />
          </label>
        </div>

        <div v-if="cartItems.length === 0" class="muted">数量を入力した商品がここに表示されます。</div>
        <div v-else class="cart-list">
          <div v-for="item in cartItems" :key="item.product.id" class="cart-row">
            <span>{{ item.product.name }} × {{ item.quantity }}</span>
            <strong>¥{{ item.subtotal.toLocaleString() }}</strong>
          </div>
          <div class="summary-row">
            <span>合計</span>
            <strong>¥{{ totalAmount.toLocaleString() }}</strong>
          </div>
        </div>
        <p v-if="submitMessage" class="error-message">{{ submitMessage }}</p>
      </aside>
    </section>
  </AppShell>
</template>
