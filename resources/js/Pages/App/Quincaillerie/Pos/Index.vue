<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    products: Array,
    customers: Array,
    depots: Array,
    categories: Array,
})

const page = usePage()
const company = page.props.currentCompany
const flash = computed(() => page.props.flash)

const search = ref('')
const selectedCategory = ref(null)
const selectedDepot = ref(props.depots?.find(d => d.is_default)?.id || null)
const cart = ref([])
const form = useForm({
    customer_id: null,
    depot_id: null,
    discount_amount: 0,
    tax_amount: 0,
    notes: '',
    items: [],
    payment_method: 'cash',
})

const fmt = (v) => new Intl.NumberFormat('fr-FR').format(Math.round(v))

const filteredProducts = computed(() => {
    let list = props.products || []
    if (search.value) {
        const s = search.value.toLowerCase()
        list = list.filter(p => p.name.toLowerCase().includes(s) || (p.barcode && p.barcode.toLowerCase().includes(s)))
    }
    if (selectedCategory.value) {
        list = list.filter(p => p.category_id === selectedCategory.value)
    }
    return list
})

function addToCart(product) {
    const existing = cart.value.find(c => c.product_id === product.id)
    if (existing) {
        existing.quantity++
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            unit_price: product.selling_price,
            quantity: 1,
            discount: 0,
            stock_qty: product.stock_qty || 0,
        })
    }
}

function removeFromCart(index) { cart.value.splice(index, 1) }

const subtotal = computed(() => cart.value.reduce((s, i) => s + (i.unit_price * i.quantity) - i.discount, 0))
const total = computed(() => Math.max(0, subtotal.value - form.discount_amount + form.tax_amount))

function submit() {
    if (cart.value.length === 0) return
    form.depot_id = selectedDepot.value
    form.items = cart.value.map(i => ({
        product_id: i.product_id,
        quantity: i.quantity,
        unit_price: i.unit_price,
        discount: i.discount,
    }))
    form.post(route('app.quincaillerie.pos.store', { _tenant: company.slug }), {
        onSuccess: () => { cart.value = []; form.reset() },
    })
}
</script>

<template>
  <AppLayout title="POS Quincaillerie">
    <div class="pos-layout">
      <!-- Left: Products -->
      <div class="pos-products">
        <div class="pos-filters">
          <input v-model="search" type="text" placeholder="Rechercher un produit ou code-barre..." class="search-input" />
          <div class="cat-pills">
            <button :class="['pill', { active: !selectedCategory }]" @click="selectedCategory = null">Tous</button>
            <button v-for="c in categories" :key="c.id" :class="['pill', { active: selectedCategory === c.id }]"
              @click="selectedCategory = c.id">{{ c.name }}</button>
          </div>
        </div>
        <div class="product-grid">
          <button v-for="p in filteredProducts" :key="p.id" class="product-card" @click="addToCart(p)">
            <div class="p-name">{{ p.name }}</div>
            <div class="p-price">{{ fmt(p.selling_price) }} F</div>
            <div class="p-stock" :class="{ low: (p.stock_qty || 0) <= 5 }">Stock: {{ p.stock_qty ?? 0 }}</div>
          </button>
          <div v-if="filteredProducts.length === 0" class="empty-msg">Aucun produit trouvé</div>
        </div>
      </div>

      <!-- Right: Cart -->
      <div class="pos-cart">
        <h2 class="cart-title"><i class="fa-solid fa-cart-shopping"></i> Panier</h2>

        <!-- Customer -->
        <div class="cart-field">
          <label>Client (optionnel)</label>
          <select v-model="form.customer_id">
            <option :value="null">— Vente anonyme —</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} {{ c.phone ? '('+c.phone+')' : '' }}</option>
          </select>
        </div>

        <!-- Depot -->
        <div class="cart-field" v-if="depots.length > 1">
          <label>Dépôt</label>
          <select v-model="selectedDepot">
            <option v-for="d in depots" :key="d.id" :value="d.id">{{ d.name }}{{ d.is_default ? ' (défaut)' : '' }}</option>
          </select>
        </div>

        <!-- Items -->
        <div class="cart-items">
          <div v-if="cart.length === 0" class="cart-empty">Panier vide</div>
          <div v-for="(item, idx) in cart" :key="item.product_id" class="cart-item">
            <div class="item-header">
              <span class="item-name">{{ item.name }}</span>
              <button class="item-remove" @click="removeFromCart(idx)" title="Retirer"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="item-controls">
              <button class="qty-btn" @click="item.quantity = Math.max(1, item.quantity - 1)">−</button>
              <input type="number" v-model.number="item.quantity" min="1" class="qty-input" />
              <button class="qty-btn" @click="item.quantity++">+</button>
              <span class="item-line-total">{{ fmt(item.unit_price * item.quantity - item.discount) }} F</span>
            </div>
            <div class="item-meta">
              <input type="number" v-model.number="item.unit_price" min="0" class="price-input" placeholder="Prix" /> F/u
              <input type="number" v-model.number="item.discount" min="0" class="discount-input" placeholder="Remise" /> remise
            </div>
          </div>
        </div>

        <!-- Totals -->
        <div class="cart-totals">
          <div class="total-row"><span>Sous-total</span><span>{{ fmt(subtotal) }} F</span></div>
          <div class="total-row">
            <span>Remise globale</span>
            <input type="number" v-model.number="form.discount_amount" min="0" class="small-input" />
          </div>
          <div class="total-row">
            <span>Taxe</span>
            <input type="number" v-model.number="form.tax_amount" min="0" class="small-input" />
          </div>
          <div class="total-row grand"><span>TOTAL</span><span>{{ fmt(total) }} F</span></div>
        </div>

        <!-- Payment method -->
        <div class="cart-field">
          <label>Mode de paiement</label>
          <div class="pay-options">
            <label v-for="m in ['cash','wave','om','card','credit']" :key="m" :class="['pay-opt', { active: form.payment_method === m }]">
              <input type="radio" v-model="form.payment_method" :value="m" class="sr-only" />
              {{ { cash:'Espèces', wave:'Wave', om:'OM', card:'Carte', credit:'Crédit' }[m] }}
            </label>
          </div>
        </div>

        <!-- Notes -->
        <div class="cart-field">
          <textarea v-model="form.notes" rows="2" placeholder="Notes (optionnel)"></textarea>
        </div>

        <button class="btn-validate" :disabled="cart.length === 0 || form.processing" @click="submit">
          {{ form.processing ? 'Enregistrement...' : 'Valider la vente' }}
        </button>

        <div v-if="flash?.success" class="flash-success">{{ flash.success }}</div>
        <div v-if="form.errors && Object.keys(form.errors).length" class="flash-error">
          <div v-for="(e, k) in form.errors" :key="k">{{ e }}</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.pos-layout {display:flex;gap:0;min-height:calc(100vh - 84px);margin:-24px}
.pos-products {flex:1;padding:20px;overflow-y:auto;background:#F9FAFB}
.pos-cart {width:380px;background:#fff;border-left:1px solid #E5E7EB;padding:20px;display:flex;flex-direction:column;overflow-y:auto}

.pos-filters {margin-bottom:16px}
.search-input {width:100%;padding:10px 14px;border:1px solid #D1D5DB;font-size:.88rem;margin-bottom:10px}
.search-input:focus {outline:none;border-color:#4F46E5;box-shadow:0 0 0 3px rgba(79,70,229,.1)}
.cat-pills {display:flex;flex-wrap:wrap;gap:6px}
.pill {padding:5px 12px;font-size:.78rem;font-weight:600;border:1px solid #D1D5DB;background:#fff;cursor:pointer;color:#374151;transition:all .15s}
.pill.active,.pill:hover {background:#4F46E5;color:#fff;border-color:#4F46E5}

.product-grid {display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px}
.product-card {padding:14px;border:1px solid #E5E7EB;background:#fff;cursor:pointer;transition:all .15s;text-align:left}
.product-card:hover {border-color:#4F46E5;box-shadow:0 2px 8px rgba(79,70,229,.12)}
.p-name {font-size:.82rem;font-weight:700;color:#111827;margin-bottom:4px;line-height:1.3}
.p-price {font-size:.88rem;font-weight:800;color:#4F46E5}
.p-stock {font-size:.7rem;color:#6B7280;margin-top:2px}
.p-stock.low {color:#EF4444;font-weight:700}
.empty-msg {grid-column:1/-1;text-align:center;padding:40px;color:#9CA3AF;font-size:.88rem}

.cart-title {font-size:1rem;font-weight:800;color:#111827;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.cart-field {margin-bottom:12px}
.cart-field label {display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px}
.cart-field select,.cart-field textarea {width:100%;padding:8px 10px;border:1px solid #D1D5DB;font-size:.82rem}
.cart-field select:focus,.cart-field textarea:focus {outline:none;border-color:#4F46E5}

.cart-items {flex:1;overflow-y:auto;margin-bottom:12px;min-height:100px}
.cart-empty {text-align:center;color:#9CA3AF;padding:30px 0;font-size:.85rem}
.cart-item {padding:10px 0;border-bottom:1px solid #F3F4F6}
.item-header {display:flex;justify-content:space-between;align-items:center}
.item-name {font-size:.82rem;font-weight:700;color:#111827}
.item-remove {background:none;border:none;color:#EF4444;cursor:pointer;font-size:.9rem;padding:2px 6px}
.item-controls {display:flex;align-items:center;gap:6px;margin-top:6px}
.qty-btn {width:28px;height:28px;border:1px solid #D1D5DB;background:#F9FAFB;font-size:1rem;cursor:pointer;font-weight:700;color:#374151}
.qty-btn:hover {background:#EEF2FF;border-color:#4F46E5;color:#4F46E5}
.qty-input {width:50px;text-align:center;padding:4px;border:1px solid #D1D5DB;font-size:.82rem}
.item-line-total {margin-left:auto;font-size:.82rem;font-weight:800;color:#4F46E5}
.item-meta {display:flex;align-items:center;gap:6px;margin-top:4px;font-size:.72rem;color:#6B7280}
.price-input,.discount-input {width:70px;padding:3px 6px;border:1px solid #E5E7EB;font-size:.75rem;text-align:right}

.cart-totals {background:#F9FAFB;padding:12px;margin-bottom:12px}
.total-row {display:flex;justify-content:space-between;align-items:center;font-size:.82rem;color:#374151;padding:3px 0}
.total-row.grand {font-size:1.05rem;font-weight:900;color:#111827;border-top:2px solid #E5E7EB;padding-top:8px;margin-top:6px}
.small-input {width:80px;padding:4px 8px;border:1px solid #D1D5DB;font-size:.82rem;text-align:right}

.pay-options {display:flex;flex-wrap:wrap;gap:6px}
.pay-opt {padding:6px 14px;font-size:.78rem;font-weight:600;border:1px solid #D1D5DB;background:#fff;cursor:pointer;color:#374151;transition:all .15s}
.pay-opt.active {background:#4F46E5;color:#fff;border-color:#4F46E5}
.sr-only {position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0}

.btn-validate {width:100%;padding:12px;background:#4F46E5;color:#fff;font-size:.88rem;font-weight:700;border:none;cursor:pointer;margin-top:8px;transition:background .15s}
.btn-validate:hover {background:#4338CA}
.btn-validate:disabled {opacity:.5;cursor:not-allowed}

.flash-success {margin-top:8px;padding:8px 12px;background:#D1FAE5;color:#065F46;font-size:.82rem;font-weight:600}
.flash-error {margin-top:8px;padding:8px 12px;background:#FEE2E2;color:#991B1B;font-size:.82rem}
</style>
