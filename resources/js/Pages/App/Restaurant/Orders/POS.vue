<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ dishes: Array, services: Array, openSession: Object })
const t = () => route().params._tenant

// Cart state
const cart = ref([])
const orderType = ref('table')
const tableNumber = ref('')
const deliveryAddress = ref('')
const deliveryPerson = ref('')
const customerName = ref('')
const serviceId = ref(props.services?.[0]?.id || '')
const paymentMethod = ref('cash')
const paymentStatus = ref('paid')
const discount = ref(0)
const notes = ref('')
const searchQuery = ref('')
const activeCategory = ref(null)
const processing = ref(false)

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) }

// Group dishes by category
const categories = computed(() => {
  const map = {}
  props.dishes.forEach(d => {
    const catName = d.category?.name || 'Sans catégorie'
    const catId = d.restaurant_category_id
    if (!map[catId]) map[catId] = { id: catId, name: catName, dishes: [] }
    map[catId].dishes.push(d)
  })
  return Object.values(map)
})

const filteredDishes = computed(() => {
  let list = props.dishes
  if (activeCategory.value) {
    list = list.filter(d => d.restaurant_category_id === activeCategory.value)
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(d => d.name.toLowerCase().includes(q))
  }
  return list
})

function getEffectivePrice(dish) {
  if (dish.promo_price && dish.promo_start && dish.promo_end) {
    const today = new Date().toISOString().slice(0, 10)
    if (today >= dish.promo_start && today <= dish.promo_end) return parseFloat(dish.promo_price)
  }
  return parseFloat(dish.price)
}

function addToCart(dish) {
  const existing = cart.value.find(c => c.dish_id === dish.id)
  if (existing) {
    existing.quantity++
    existing.total = existing.quantity * existing.unit_price
  } else {
    cart.value.push({
      dish_id: dish.id,
      name: dish.name,
      quantity: 1,
      unit_price: getEffectivePrice(dish),
      total: getEffectivePrice(dish),
      notes: '',
    })
  }
}

function removeFromCart(index) {
  cart.value.splice(index, 1)
}

function updateQty(index, delta) {
  const item = cart.value[index]
  item.quantity = Math.max(1, item.quantity + delta)
  item.total = item.quantity * item.unit_price
}

const subtotal = computed(() => cart.value.reduce((s, i) => s + i.total, 0))
const total = computed(() => Math.max(0, subtotal.value - (discount.value || 0)))

function submitOrder() {
  if (cart.value.length === 0) return
  processing.value = true

  router.post(route('app.restaurant.orders.store', { _tenant: t() }), {
    type: orderType.value,
    table_number: orderType.value === 'table' ? tableNumber.value : null,
    delivery_address: orderType.value === 'delivery' ? deliveryAddress.value : null,
    delivery_person: orderType.value === 'delivery' ? deliveryPerson.value : null,
    customer_name: customerName.value || null,
    service_id: serviceId.value || null,
    discount_amount: discount.value || 0,
    payment_method: paymentMethod.value,
    payment_status: paymentStatus.value,
    notes: notes.value || null,
    items: cart.value.map(i => ({
      dish_id: i.dish_id,
      quantity: i.quantity,
      notes: i.notes || null,
    })),
  }, {
    onFinish: () => { processing.value = false },
  })
}

const TYPE_LABELS = { table: 'Sur table', takeaway: 'À emporter', delivery: 'Livraison' }
const PAY_LABELS = { cash: 'Cash', wave: 'Wave', om: 'Orange Money', card: 'Carte', other: 'Autre' }
</script>

<template>
  <AppLayout title="Nouvelle commande">
    <Head title="POS — Commande" />

    <div class="pos-layout">
      <!-- LEFT: Menu -->
      <div class="pos-menu">
        <div class="menu-header">
          <h1 class="pos-title"><i class="fa-solid fa-utensils" style="color:#EF4444"></i> Prise de commande</h1>
          <div v-if="!openSession" class="caisse-warning">
            <i class="fa-solid fa-triangle-exclamation"></i> Caisse non ouverte
          </div>
        </div>

        <div class="search-bar">
          <i class="fa-solid fa-search"></i>
          <input v-model="searchQuery" type="text" placeholder="Rechercher un plat…" />
        </div>

        <div class="cat-tabs">
          <button :class="{ active: !activeCategory }" @click="activeCategory = null">Tous</button>
          <button v-for="cat in categories" :key="cat.id" :class="{ active: activeCategory === cat.id }" @click="activeCategory = cat.id">
            {{ cat.name }}
          </button>
        </div>

        <div class="dishes-grid">
          <button v-for="d in filteredDishes" :key="d.id" class="dish-card" @click="addToCart(d)">
            <div class="dish-name">{{ d.name }}</div>
            <div class="dish-price">{{ fmt(getEffectivePrice(d)) }} <small>XOF</small></div>
            <span v-if="d.promo_price" class="dish-promo">PROMO</span>
          </button>
          <div v-if="filteredDishes.length === 0" class="dishes-empty">Aucun plat disponible</div>
        </div>
      </div>

      <!-- RIGHT: Cart -->
      <div class="pos-cart">
        <div class="cart-header">
          <h2 class="cart-title"><i class="fa-solid fa-basket-shopping"></i> Panier ({{ cart.length }})</h2>
        </div>

        <!-- Order type -->
        <div class="type-row">
          <button v-for="(label, key) in TYPE_LABELS" :key="key" :class="['type-btn', { active: orderType === key }]" @click="orderType = key">
            {{ label }}
          </button>
        </div>

        <div v-if="orderType === 'table'" class="cart-field">
          <input v-model="tableNumber" type="text" placeholder="N° table" />
        </div>
        <div v-if="orderType === 'delivery'" class="cart-field-group">
          <input v-model="deliveryAddress" type="text" placeholder="Adresse livraison" />
          <input v-model="deliveryPerson" type="text" placeholder="Nom livreur" />
        </div>

        <div class="cart-field">
          <input v-model="customerName" type="text" placeholder="Nom client (optionnel)" />
        </div>

        <!-- Cart items -->
        <div class="cart-items">
          <div v-if="cart.length === 0" class="cart-empty">Ajoutez des plats au panier</div>
          <div v-for="(item, i) in cart" :key="i" class="cart-item">
            <div class="item-info">
              <div class="item-name">{{ item.name }}</div>
              <div class="item-price">{{ fmt(item.unit_price) }} × {{ item.quantity }}</div>
            </div>
            <div class="item-actions">
              <button @click="updateQty(i, -1)" class="qty-btn">−</button>
              <span class="qty-val">{{ item.quantity }}</span>
              <button @click="updateQty(i, 1)" class="qty-btn">+</button>
              <button @click="removeFromCart(i)" class="remove-btn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="item-total">{{ fmt(item.total) }}</div>
          </div>
        </div>

        <!-- Totals -->
        <div class="cart-totals">
          <div class="total-row"><span>Sous-total</span><span>{{ fmt(subtotal) }} XOF</span></div>
          <div class="total-row">
            <span>Remise</span>
            <input v-model.number="discount" type="number" min="0" step="100" class="discount-input" />
          </div>
          <div class="total-row total-final"><span>TOTAL</span><span>{{ fmt(total) }} XOF</span></div>
        </div>

        <!-- Payment -->
        <div class="pay-section">
          <div class="pay-row">
            <select v-model="paymentMethod" class="pay-select">
              <option v-for="(label, key) in PAY_LABELS" :key="key" :value="key">{{ label }}</option>
            </select>
            <select v-model="paymentStatus" class="pay-select">
              <option value="paid">Payé</option>
              <option value="pending">En attente</option>
            </select>
          </div>
          <select v-if="services.length" v-model="serviceId" class="pay-select full">
            <option value="">Service…</option>
            <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>

        <button @click="submitOrder" class="btn-validate" :disabled="cart.length === 0 || processing">
          <i class="fa-solid fa-check"></i>
          {{ processing ? 'Enregistrement…' : 'Valider la commande' }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.pos-layout { display: grid; grid-template-columns: 1fr 380px; gap: 0; min-height: calc(100vh - 80px); }
.pos-menu { padding: 0 16px 16px 0; overflow-y: auto; }
.pos-cart { background: #fff; border-left: 1px solid #E5E7EB; padding: 16px; display: flex; flex-direction: column; }

.menu-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.pos-title { font-size: 1.1rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; }
.caisse-warning { font-size: 0.75rem; font-weight: 600; color: #D97706; background: #FEF3C7; padding: 4px 10px; display: flex; align-items: center; gap: 6px; }

.search-bar { position: relative; margin-bottom: 10px; }
.search-bar i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 0.8rem; }
.search-bar input { width: 100%; padding: 8px 12px 8px 32px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.search-bar input:focus { border-color: #EF4444; }

.cat-tabs { display: flex; gap: 6px; margin-bottom: 14px; flex-wrap: wrap; }
.cat-tabs button { padding: 5px 12px; font-size: 0.76rem; font-weight: 600; border: 1px solid #E5E7EB; background: #fff; color: #374151; cursor: pointer; }
.cat-tabs button.active { background: #EF4444; color: #fff; border-color: #EF4444; }

.dishes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; }
.dish-card { background: #fff; border: 1px solid #E5E7EB; padding: 14px 12px; cursor: pointer; text-align: left; position: relative; transition: border-color 0.15s; }
.dish-card:hover { border-color: #EF4444; }
.dish-name { font-size: 0.82rem; font-weight: 600; color: #111827; margin-bottom: 4px; }
.dish-price { font-size: 0.88rem; font-weight: 800; color: #EF4444; }
.dish-price small { font-size: 0.68rem; font-weight: 500; color: #9CA3AF; }
.dish-promo { position: absolute; top: 4px; right: 4px; font-size: 0.6rem; font-weight: 700; background: #FEF3C7; color: #D97706; padding: 1px 5px; }
.dishes-empty { grid-column: 1 / -1; text-align: center; color: #9CA3AF; padding: 40px; font-size: 0.82rem; }

.cart-header { margin-bottom: 10px; }
.cart-title { font-size: 0.92rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; }

.type-row { display: flex; gap: 4px; margin-bottom: 10px; }
.type-btn { flex: 1; padding: 6px; font-size: 0.76rem; font-weight: 600; border: 1px solid #E5E7EB; background: #fff; color: #374151; cursor: pointer; text-align: center; }
.type-btn.active { background: #EF4444; color: #fff; border-color: #EF4444; }

.cart-field { margin-bottom: 8px; }
.cart-field input { width: 100%; padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.cart-field-group { display: flex; gap: 6px; margin-bottom: 8px; }
.cart-field-group input { flex: 1; padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }

.cart-items { flex: 1; overflow-y: auto; margin-bottom: 10px; border-top: 1px solid #F3F4F6; padding-top: 8px; }
.cart-empty { text-align: center; color: #9CA3AF; padding: 30px 0; font-size: 0.82rem; }
.cart-item { display: grid; grid-template-columns: 1fr auto auto; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid #F9FAFB; }
.item-name { font-size: 0.82rem; font-weight: 600; color: #111827; }
.item-price { font-size: 0.72rem; color: #6B7280; }
.item-actions { display: flex; align-items: center; gap: 4px; }
.qty-btn { width: 24px; height: 24px; border: 1px solid #D1D5DB; background: #fff; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #374151; display: flex; align-items: center; justify-content: center; }
.qty-val { font-size: 0.82rem; font-weight: 700; min-width: 20px; text-align: center; }
.remove-btn { background: none; border: none; color: #EF4444; cursor: pointer; font-size: 0.78rem; padding: 2px 4px; }
.item-total { font-size: 0.82rem; font-weight: 700; color: #111827; text-align: right; min-width: 70px; }

.cart-totals { border-top: 2px solid #E5E7EB; padding-top: 8px; margin-bottom: 10px; }
.total-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; color: #374151; padding: 4px 0; }
.total-final { font-size: 1rem; font-weight: 800; color: #111827; border-top: 1px solid #E5E7EB; padding-top: 8px; margin-top: 4px; }
.discount-input { width: 100px; padding: 4px 8px; border: 1px solid #D1D5DB; font-size: 0.82rem; text-align: right; outline: none; }

.pay-section { margin-bottom: 10px; }
.pay-row { display: flex; gap: 6px; margin-bottom: 6px; }
.pay-select { flex: 1; padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.pay-select.full { width: 100%; }

.btn-validate { width: 100%; padding: 12px; background: #10B981; color: #fff; font-weight: 700; font-size: 0.92rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-validate:hover:not(:disabled) { background: #059669; }
.btn-validate:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 900px) { .pos-layout { grid-template-columns: 1fr; } .pos-cart { border-left: none; border-top: 1px solid #E5E7EB; } }
</style>
