<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  expense:    { type: Object, default: null },
  categories: Array,
  suppliers:  Array,
})
const isEditing = !!props.expense

const form = useForm({
  expense_category_id: props.expense?.expense_category_id || '',
  amount:              props.expense?.amount || '',
  description:         props.expense?.description || '',
  supplier_id:         props.expense?.supplier_id || '',
  date:                props.expense?.date || new Date().toISOString().slice(0, 10),
  receipt_url:         props.expense?.receipt_url || '',
})

function submit() {
  if (isEditing) {
    form.put(route('app.expenses.update', { expense: props.expense.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.expenses.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEditing ? 'Modifier dépense' : 'Nouvelle dépense'">
    <Head :title="isEditing ? 'Modifier dépense' : 'Nouvelle dépense'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-money-bill-trend-up" style="color:#DC2626"></i>
        {{ isEditing ? 'Modifier la dépense' : 'Nouvelle dépense' }}
      </h1>
      <Link :href="route('app.expenses.index', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="field">
          <label>Catégorie <span class="req">*</span></label>
          <select v-model="form.expense_category_id" :class="{ 'has-error': form.errors.expense_category_id }">
            <option value="">— Sélectionner —</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <span v-if="form.errors.expense_category_id" class="field-error">{{ form.errors.expense_category_id }}</span>
        </div>
        <div class="field">
          <label>Montant (F CFA) <span class="req">*</span></label>
          <input v-model.number="form.amount" type="number" step="0.01" min="0" :class="{ 'has-error': form.errors.amount }" />
          <span v-if="form.errors.amount" class="field-error">{{ form.errors.amount }}</span>
        </div>
        <div class="field">
          <label>Date <span class="req">*</span></label>
          <input v-model="form.date" type="date" :class="{ 'has-error': form.errors.date }" />
          <span v-if="form.errors.date" class="field-error">{{ form.errors.date }}</span>
        </div>
        <div class="field">
          <label>Fournisseur</label>
          <select v-model="form.supplier_id">
            <option value="">— Aucun —</option>
            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="field full">
          <label>Description</label>
          <textarea v-model="form.description" rows="3" :class="{ 'has-error': form.errors.description }"></textarea>
          <span v-if="form.errors.description" class="field-error">{{ form.errors.description }}</span>
        </div>
        <div class="field full">
          <label>URL reçu</label>
          <input v-model="form.receipt_url" type="url" placeholder="https://..." />
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> {{ isEditing ? 'Mettre à jour' : 'Enregistrer' }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #DC2626; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 720px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select, .field textarea { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; font-family: inherit; }
.field input:focus, .field select:focus, .field textarea:focus { border-color: #DC2626; box-shadow: 0 0 0 2px rgba(220,38,38,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #DC2626; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #B91C1C; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
