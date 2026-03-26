<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ supplier: Object })
const isEdit = !!props.supplier

const form = useForm({
  name:    props.supplier?.name    || '',
  phone:   props.supplier?.phone   || '',
  email:   props.supplier?.email   || '',
  address: props.supplier?.address || '',
  ninea:   props.supplier?.ninea   || '',
  rib:     props.supplier?.rib     || '',
  rating:  props.supplier?.rating  || '',
  notes:   props.supplier?.notes   || '',
})

function submit() {
  if (isEdit) {
    form.put(route('app.suppliers.update', { supplier: props.supplier.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.suppliers.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier fournisseur' : 'Nouveau fournisseur'">
    <Head :title="isEdit ? 'Modifier fournisseur' : 'Nouveau fournisseur'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-truck-field" style="color:#F59E0B"></i>
        {{ isEdit ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
      </h1>
      <Link :href="route('app.suppliers.index', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="field">
          <label>Nom <span class="req">*</span></label>
          <input v-model="form.name" type="text" :class="{ 'has-error': form.errors.name }" />
          <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
        </div>
        <div class="field">
          <label>Téléphone <span class="req">*</span></label>
          <input v-model="form.phone" type="text" :class="{ 'has-error': form.errors.phone }" />
          <span v-if="form.errors.phone" class="field-error">{{ form.errors.phone }}</span>
        </div>
        <div class="field">
          <label>Email</label>
          <input v-model="form.email" type="email" :class="{ 'has-error': form.errors.email }" />
          <span v-if="form.errors.email" class="field-error">{{ form.errors.email }}</span>
        </div>
        <div class="field">
          <label>NINEA</label>
          <input v-model="form.ninea" type="text" :class="{ 'has-error': form.errors.ninea }" />
          <span v-if="form.errors.ninea" class="field-error">{{ form.errors.ninea }}</span>
        </div>
        <div class="field">
          <label>RIB</label>
          <input v-model="form.rib" type="text" :class="{ 'has-error': form.errors.rib }" />
          <span v-if="form.errors.rib" class="field-error">{{ form.errors.rib }}</span>
        </div>
        <div class="field">
          <label>Note (/5)</label>
          <input v-model="form.rating" type="number" step="0.1" min="0" max="5" :class="{ 'has-error': form.errors.rating }" />
          <span v-if="form.errors.rating" class="field-error">{{ form.errors.rating }}</span>
        </div>
        <div class="field full">
          <label>Adresse</label>
          <input v-model="form.address" type="text" :class="{ 'has-error': form.errors.address }" />
          <span v-if="form.errors.address" class="field-error">{{ form.errors.address }}</span>
        </div>
        <div class="field full">
          <label>Notes internes</label>
          <textarea v-model="form.notes" rows="3" :class="{ 'has-error': form.errors.notes }"></textarea>
          <span v-if="form.errors.notes" class="field-error">{{ form.errors.notes }}</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> {{ isEdit ? 'Enregistrer' : 'Créer le fournisseur' }}
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

.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 640px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select, .field textarea { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; font-family: inherit; }
.field input:focus, .field select:focus, .field textarea:focus { border-color: #F59E0B; box-shadow: 0 0 0 2px rgba(245,158,11,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #F59E0B; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #D97706; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
