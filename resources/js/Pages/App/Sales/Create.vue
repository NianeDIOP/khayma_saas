<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ products: Array, customers: Array })

const form = useForm({
  customer_id:     '',
  type:            'counter',
  discount_amount: 0,
  tax_amount:      0,
  notes:           '',
  items:           [],
  payment_method:  'cash',
})

const selectedProduct = ref('')
const itemQty = ref(1)

function addItem() {
  const product = props.products.find(p => p.id === Number(selectedProduct.value))
  if (!product || itemQty.value <= 0) return

  const existing = form.items.find(i => i.product_id === product.id)
  if (existing) {
    existing.quantity += itemQty.value
  } else {
    form.items.push({
      product_id: product.id,
      name:       product.name,
      quantity:   itemQty.value,
      unit_price: parseFloat(product.selling_price),
      discount:   0,
    })
  }
  selectedProduct.value = ''
  itemQty.value = 1
}

function removeItem(index) {
  form.items.splice(index, 1)
}

const subtotal = computed(() => {
  return form.items.reduce((sum, i) => sum + (i.unit_price * i.quantity) - i.discount, 0)
})

const total = computed(() => {
  return subtotal.value - form.discount_amount + form.tax_amount
})

function submit() {
  form.post(route('app.sales.store', { _tenant: route().params._tenant }))
}
</script>

<template>
  <AppLayout title="Nouvelle vente">
    <Head title="Nouvelle vente" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-cash-register" style="color:#F59E0B"></i> Nouvelle vente</h1>
      <Link :href="route('app.sales.index', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="sale-layout">
      <!-- Left: Items -->
      <div class="sale-items">
        <div class="add-item-bar">
          <select v-model="selectedProduct" class="product-select">
            <option value="">— Sélectionner un produit —</option>
            <option v-for="p in products" :key="p.id" :value="p.id">
              {{ p.name }} — {{ Number(p.selling_price).toLocaleString('fr-FR') }} F
            </option>
          </select>
          <input v-model.number="itemQty" type="number" min="1" step="0.01" class="qty-input" />
          <button type="button" @click="addItem" class="btn-add"><i class="fa-solid fa-plus"></i></button>
        </div>

        <div class="items-table-wrap">
          <table class="items-table">
            <thead>
              <tr>
                <th>Produit</th>
                <th>Qté</th>
                <th>P.U.</th>
                <th>Remise</th>
                <th>Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="form.items.length === 0">
                <td colspan="6" class="empty-row">Ajoutez des produits à la vente</td>
              </tr>
              <tr v-for="(item, idx) in form.items" :key="idx">
                <td class="cell-name">{{ item.name }}</td>
                <td><input v-model.number="item.quantity" type="number" min="0.01" step="0.01" class="inline-input" /></td>
                <td>{{ Number(item.unit_price).toLocaleString('fr-FR') }} F</td>
                <td><input v-model.number="item.discount" type="number" min="0" step="1" class="inline-input" /></td>
                <td class="text-bold">{{ Number((item.unit_price * item.quantity) - item.discount).toLocaleString('fr-FR') }} F</td>
                <td><button type="button" @click="removeItem(idx)" class="btn-remove"><i class="fa-solid fa-times"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right: Summary -->
      <div class="sale-summary">
        <div class="field">
          <label>Client</label>
          <select v-model="form.customer_id">
            <option value="">Client de passage</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="field">
          <label>Type</label>
          <select v-model="form.type">
            <option value="counter">Comptoir</option>
            <option value="delivery">Livraison</option>
            <option value="takeaway">Emporter</option>
          </select>
        </div>
        <div class="field">
          <label>Mode de paiement</label>
          <select v-model="form.payment_method">
            <option value="cash">Espèces</option>
            <option value="wave">Wave</option>
            <option value="om">Orange Money</option>
            <option value="card">Carte bancaire</option>
            <option value="other">Autre</option>
          </select>
        </div>

        <div class="summary-line"><span>Sous-total</span><span>{{ Number(subtotal).toLocaleString('fr-FR') }} F</span></div>
        <div class="field inline">
          <label>Remise globale</label>
          <input v-model.number="form.discount_amount" type="number" min="0" step="1" />
        </div>
        <div class="field inline">
          <label>TVA</label>
          <input v-model.number="form.tax_amount" type="number" min="0" step="1" />
        </div>
        <div class="summary-total"><span>TOTAL</span><span>{{ Number(total).toLocaleString('fr-FR') }} F</span></div>

        <div class="field full">
          <label>Notes</label>
          <textarea v-model="form.notes" rows="2"></textarea>
        </div>

        <button type="submit" class="btn-validate" :disabled="form.processing || form.items.length === 0">
          <i class="fa-solid fa-check-circle"></i> Valider la vente
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #F59E0B; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }

.sale-layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
.sale-items { background: #fff; border: 1px solid #E5E7EB; padding: 16px; }
.sale-summary { background: #fff; border: 1px solid #E5E7EB; padding: 16px; display: flex; flex-direction: column; gap: 12px; }

.add-item-bar { display: flex; gap: 8px; margin-bottom: 12px; }
.product-select { flex: 1; padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; }
.qty-input { width: 80px; padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; text-align: center; }
.btn-add { background: #F59E0B; color: #fff; border: none; padding: 8px 14px; cursor: pointer; font-size: 0.82rem; }
.btn-add:hover { background: #D97706; }

.items-table-wrap { overflow-x: auto; }
.items-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.items-table th { background: #F9FAFB; text-align: left; padding: 8px 10px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.items-table td { padding: 8px 10px; border-bottom: 1px solid #F3F4F6; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 20px 10px !important; }
.cell-name { font-weight: 600; }
.text-bold { font-weight: 700; }
.inline-input { width: 70px; padding: 4px 8px; border: 1px solid #D1D5DB; font-size: 0.8rem; text-align: center; }
.btn-remove { background: none; border: none; color: #EF4444; cursor: pointer; font-size: 0.9rem; }

.field { display: flex; flex-direction: column; gap: 4px; }
.field.inline { flex-direction: row; justify-content: space-between; align-items: center; }
.field.inline input { width: 100px; text-align: right; }
.field.full { width: 100%; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.field input, .field select, .field textarea { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; font-family: inherit; }
.field input:focus, .field select:focus, .field textarea:focus { border-color: #F59E0B; box-shadow: 0 0 0 2px rgba(245,158,11,0.12); }

.summary-line { display: flex; justify-content: space-between; font-size: 0.82rem; color: #374151; padding: 4px 0; }
.summary-total { display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 800; color: #111827; padding: 10px 0; border-top: 2px solid #F59E0B; }

.btn-validate { background: #F59E0B; color: #fff; padding: 12px 20px; font-size: 0.9rem; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 8px; }
.btn-validate:hover { background: #D97706; }
.btn-validate:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
