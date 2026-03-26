<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ unit: Object, baseUnits: Array })
const isEdit = !!props.unit

const form = useForm({
  name:              props.unit?.name              || '',
  symbol:            props.unit?.symbol            || '',
  base_unit_id:      props.unit?.base_unit_id      || '',
  conversion_factor: props.unit?.conversion_factor || 1,
})

function submit() {
  if (isEdit) {
    form.put(route('app.units.update', { unit: props.unit.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.units.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier unité' : 'Nouvelle unité'">
    <Head :title="isEdit ? 'Modifier unité' : 'Nouvelle unité'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-ruler" style="color:#0891B2"></i>
        {{ isEdit ? 'Modifier l\'unité' : 'Nouvelle unité' }}
      </h1>
      <Link :href="route('app.units.index', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="field">
          <label>Nom <span class="req">*</span></label>
          <input v-model="form.name" type="text" placeholder="ex: Kilogramme" :class="{ 'has-error': form.errors.name }" />
          <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
        </div>
        <div class="field">
          <label>Symbole <span class="req">*</span></label>
          <input v-model="form.symbol" type="text" placeholder="ex: kg" :class="{ 'has-error': form.errors.symbol }" />
          <span v-if="form.errors.symbol" class="field-error">{{ form.errors.symbol }}</span>
        </div>
        <div class="field">
          <label>Unité de base</label>
          <select v-model="form.base_unit_id">
            <option value="">Aucune (unité de base)</option>
            <option v-for="b in baseUnits" :key="b.id" :value="b.id">{{ b.name }} ({{ b.symbol }})</option>
          </select>
        </div>
        <div class="field">
          <label>Facteur de conversion</label>
          <input v-model.number="form.conversion_factor" type="number" step="0.0001" min="0.0001" />
          <span v-if="form.errors.conversion_factor" class="field-error">{{ form.errors.conversion_factor }}</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> {{ isEdit ? 'Enregistrer' : 'Créer' }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #0891B2; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 640px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.field input:focus, .field select:focus { border-color: #0891B2; box-shadow: 0 0 0 2px rgba(8,145,178,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #0891B2; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #0E7490; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
