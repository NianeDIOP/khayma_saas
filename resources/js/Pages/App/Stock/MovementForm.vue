<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ products: Array, depots: Array })

const form = useForm({
  product_id: '',
  depot_id:   '',
  type:       'in',
  quantity:   '',
  unit_cost:  0,
  reference:  '',
  notes:      '',
})

function submit() {
  form.post(route('app.stock.store-movement', { _tenant: route().params._tenant }))
}
</script>

<template>
  <AppLayout title="Nouveau mouvement">
    <Head title="Nouveau mouvement de stock" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-right-left" style="color:#EF4444"></i>
        Nouveau mouvement de stock
      </h1>
      <Link :href="route('app.stock.movements', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="field">
          <label>Produit <span class="req">*</span></label>
          <select v-model="form.product_id" :class="{ 'has-error': form.errors.product_id }">
            <option value="">— Sélectionner —</option>
            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} {{ p.barcode ? `(${p.barcode})` : '' }}</option>
          </select>
          <span v-if="form.errors.product_id" class="field-error">{{ form.errors.product_id }}</span>
        </div>
        <div class="field">
          <label>Dépôt <span class="req">*</span></label>
          <select v-model="form.depot_id" :class="{ 'has-error': form.errors.depot_id }">
            <option value="">— Sélectionner —</option>
            <option v-for="d in depots" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
          <span v-if="form.errors.depot_id" class="field-error">{{ form.errors.depot_id }}</span>
        </div>
        <div class="field">
          <label>Type <span class="req">*</span></label>
          <select v-model="form.type">
            <option value="in">Entrée</option>
            <option value="out">Sortie</option>
            <option value="adjustment">Ajustement</option>
            <option value="loss">Perte</option>
          </select>
        </div>
        <div class="field">
          <label>Quantité <span class="req">*</span></label>
          <input v-model.number="form.quantity" type="number" step="0.01" min="0.01" :class="{ 'has-error': form.errors.quantity }" />
          <span v-if="form.errors.quantity" class="field-error">{{ form.errors.quantity }}</span>
        </div>
        <div class="field">
          <label>Coût unitaire (F CFA)</label>
          <input v-model.number="form.unit_cost" type="number" step="0.01" min="0" />
        </div>
        <div class="field">
          <label>Référence</label>
          <input v-model="form.reference" type="text" />
        </div>
        <div class="field full">
          <label>Notes</label>
          <textarea v-model="form.notes" rows="3"></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> Enregistrer le mouvement
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #EF4444; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 720px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select, .field textarea { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; font-family: inherit; }
.field input:focus, .field select:focus, .field textarea:focus { border-color: #EF4444; box-shadow: 0 0 0 2px rgba(239,68,68,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #EF4444; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #DC2626; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
