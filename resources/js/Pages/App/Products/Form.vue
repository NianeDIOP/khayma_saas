<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ product: Object, categories: Array, units: Array })
const isEdit = !!props.product

const form = useForm({
  name:            props.product?.name            || '',
  description:     props.product?.description     || '',
  category_id:     props.product?.category_id     || '',
  unit_id:         props.product?.unit_id         || '',
  purchase_price:  props.product?.purchase_price  || 0,
  selling_price:   props.product?.selling_price   || 0,
  barcode:         props.product?.barcode         || '',
  min_stock_alert: props.product?.min_stock_alert || 0,
  is_active:       props.product?.is_active ?? true,
})

function submit() {
  if (isEdit) {
    form.put(route('app.products.update', { product: props.product.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.products.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier produit' : 'Nouveau produit'">
    <Head :title="isEdit ? 'Modifier produit' : 'Nouveau produit'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-box" style="color:#10B981"></i>
        {{ isEdit ? 'Modifier le produit' : 'Nouveau produit' }}
      </h1>
      <Link :href="route('app.products.index', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="field full">
          <label>Nom <span class="req">*</span></label>
          <input v-model="form.name" type="text" :class="{ 'has-error': form.errors.name }" />
          <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
        </div>
        <div class="field">
          <label>Catégorie</label>
          <select v-model="form.category_id">
            <option value="">— Aucune —</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="field">
          <label>Unité</label>
          <select v-model="form.unit_id">
            <option value="">— Aucune —</option>
            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.symbol }})</option>
          </select>
        </div>
        <div class="field">
          <label>Prix d'achat (F CFA) <span class="req">*</span></label>
          <input v-model.number="form.purchase_price" type="number" step="0.01" min="0" :class="{ 'has-error': form.errors.purchase_price }" />
          <span v-if="form.errors.purchase_price" class="field-error">{{ form.errors.purchase_price }}</span>
        </div>
        <div class="field">
          <label>Prix de vente (F CFA) <span class="req">*</span></label>
          <input v-model.number="form.selling_price" type="number" step="0.01" min="0" :class="{ 'has-error': form.errors.selling_price }" />
          <span v-if="form.errors.selling_price" class="field-error">{{ form.errors.selling_price }}</span>
        </div>
        <div class="field">
          <label>Code-barres</label>
          <input v-model="form.barcode" type="text" :class="{ 'has-error': form.errors.barcode }" />
          <span v-if="form.errors.barcode" class="field-error">{{ form.errors.barcode }}</span>
        </div>
        <div class="field">
          <label>Seuil alerte stock</label>
          <input v-model.number="form.min_stock_alert" type="number" min="0" />
        </div>
        <div class="field full">
          <label>Description</label>
          <textarea v-model="form.description" rows="3" :class="{ 'has-error': form.errors.description }"></textarea>
          <span v-if="form.errors.description" class="field-error">{{ form.errors.description }}</span>
        </div>
        <div class="field">
          <label class="checkbox-label">
            <input v-model="form.is_active" type="checkbox" />
            Produit actif
          </label>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> {{ isEdit ? 'Enregistrer' : 'Créer le produit' }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #10B981; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 720px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select, .field textarea { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; font-family: inherit; }
.field input:focus, .field select:focus, .field textarea:focus { border-color: #10B981; box-shadow: 0 0 0 2px rgba(16,185,129,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.82rem; cursor: pointer; }
.checkbox-label input { width: 16px; height: 16px; accent-color: #10B981; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #10B981; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #059669; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
