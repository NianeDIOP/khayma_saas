<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ quote: Object, products: Array, customers: Array, units: Array })
const t = () => route().params._tenant
const isEdit = computed(() => !!props.quote)

const form = useForm({
  customer_id: props.quote?.customer_id || '',
  discount_amount: props.quote?.discount_amount || 0,
  notes: props.quote?.notes || '',
  valid_until: props.quote?.valid_until || '',
  items: props.quote?.items?.map(i => ({
    product_id: i.product_id,
    unit_id: i.unit_id || '',
    quantity: i.quantity,
    unit_price: i.unit_price,
    discount: i.discount || 0,
  })) || [{ product_id: '', unit_id: '', quantity: 1, unit_price: 0, discount: 0 }],
})

function addLine() {
  form.items.push({ product_id: '', unit_id: '', quantity: 1, unit_price: 0, discount: 0 })
}

function removeLine(i) {
  if (form.items.length > 1) form.items.splice(i, 1)
}

function onProductChange(idx) {
  const p = props.products.find(x => x.id == form.items[idx].product_id)
  if (p) {
    form.items[idx].unit_price = p.selling_price
    form.items[idx].unit_id = p.unit_id || ''
  }
}

function lineTotal(item) {
  return (item.unit_price * item.quantity) - (item.discount || 0)
}

const subtotal = computed(() => form.items.reduce((s, i) => s + lineTotal(i), 0))
const total = computed(() => subtotal.value - (form.discount_amount || 0))

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }

function submit() {
  if (isEdit.value) {
    form.put(route('app.quincaillerie.quotes.update', { _tenant: t(), quote: props.quote.id }))
  } else {
    form.post(route('app.quincaillerie.quotes.store', { _tenant: t() }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier devis' : 'Nouveau devis'">
    <Head :title="isEdit ? 'Modifier devis' : 'Nouveau devis'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-file-invoice" style="color:#3B82F6"></i>
        {{ isEdit ? 'Modifier le devis' : 'Nouveau devis' }}
      </h1>
      <Link :href="route('app.quincaillerie.quotes.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="form-group">
          <label>Client</label>
          <select v-model="form.customer_id" class="form-input">
            <option value="">— Sans client —</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Validité</label>
          <input v-model="form.valid_until" type="date" class="form-input" />
        </div>
      </div>

      <h3 class="section-title">Lignes du devis</h3>
      <div class="items-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Produit</th>
              <th>Unité</th>
              <th>Qté</th>
              <th>Prix unit.</th>
              <th>Remise</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in form.items" :key="idx">
              <td>
                <select v-model="item.product_id" @change="onProductChange(idx)" class="form-input" required>
                  <option value="">Choisir…</option>
                  <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </td>
              <td>
                <select v-model="item.unit_id" class="form-input">
                  <option value="">—</option>
                  <option v-for="u in units" :key="u.id" :value="u.id">{{ u.symbol || u.name }}</option>
                </select>
              </td>
              <td><input v-model.number="item.quantity" type="number" min="0.01" step="0.01" class="form-input input-sm" required /></td>
              <td><input v-model.number="item.unit_price" type="number" min="0" step="1" class="form-input input-sm" required /></td>
              <td><input v-model.number="item.discount" type="number" min="0" step="1" class="form-input input-sm" /></td>
              <td class="font-medium">{{ fmt(lineTotal(item)) }}</td>
              <td>
                <button type="button" @click="removeLine(idx)" class="btn-danger-sm" v-if="form.items.length > 1">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <button type="button" @click="addLine" class="btn-add-line">
        <i class="fa-solid fa-plus"></i> Ajouter une ligne
      </button>

      <div class="totals-section">
        <div class="totals-row"><span>Sous-total</span><span class="font-medium">{{ fmt(subtotal) }}</span></div>
        <div class="totals-row">
          <span>Remise globale</span>
          <input v-model.number="form.discount_amount" type="number" min="0" step="1" class="form-input input-sm" style="width:120px;text-align:right" />
        </div>
        <div class="totals-row total-final"><span>Total</span><span>{{ fmt(total) }}</span></div>
      </div>

      <div class="form-group">
        <label>Notes</label>
        <textarea v-model="form.notes" class="form-input" rows="3"></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-save"></i> {{ isEdit ? 'Mettre à jour' : 'Créer le devis' }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #3B82F6}
.btn-back{color:#6B7280;font-size:.875rem;display:inline-flex;align-items:center;gap:.3rem}
.btn-back:hover{color:#111}
.form-card{background:#fff;border:1px solid #E5E7EB;padding:1.5rem}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
.form-group{display:flex;flex-direction:column;gap:.3rem}
.form-group label{font-size:.8rem;font-weight:600;color:#374151}
.form-input{padding:.45rem .6rem;border:1px solid #D1D5DB;font-size:.875rem;width:100%}
.input-sm{width:90px}
.section-title{font-size:1rem;font-weight:600;margin:1.5rem 0 .75rem;color:#111}
.items-table-wrap{overflow-x:auto;margin-bottom:.75rem}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.5rem .6rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.4rem .6rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.btn-danger-sm{background:#EF4444;color:#fff;border:none;padding:.25rem .5rem;cursor:pointer;font-size:.75rem}
.btn-danger-sm:hover{background:#DC2626}
.btn-add-line{background:none;border:1px dashed #9CA3AF;color:#6B7280;padding:.4rem .8rem;cursor:pointer;font-size:.8rem;width:100%;margin-bottom:1rem}
.btn-add-line:hover{border-color:#2563EB;color:#2563EB}
.totals-section{background:#F9FAFB;padding:1rem;margin-bottom:1rem}
.totals-row{display:flex;justify-content:space-between;align-items:center;padding:.3rem 0;font-size:.9rem}
.total-final{font-weight:700;font-size:1.1rem;border-top:2px solid #D1D5DB;padding-top:.5rem;margin-top:.3rem}
.form-actions{display:flex;justify-content:flex-end;margin-top:1rem}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1.25rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.btn-primary:hover{background:#1D4ED8}
.btn-primary:disabled{opacity:.5;cursor:not-allowed}
</style>
