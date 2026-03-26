<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ category: Object, parents: Array })
const isEdit = !!props.category

const form = useForm({
  name:      props.category?.name      || '',
  parent_id: props.category?.parent_id || '',
  module:    props.category?.module    || 'general',
})

function submit() {
  if (isEdit) {
    form.put(route('app.categories.update', { category: props.category.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.categories.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier catégorie' : 'Nouvelle catégorie'">
    <Head :title="isEdit ? 'Modifier catégorie' : 'Nouvelle catégorie'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-tag" style="color:#8B5CF6"></i>
        {{ isEdit ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
      </h1>
      <Link :href="route('app.categories.index', { _tenant: route().params._tenant })" class="btn-back">
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
          <label>Catégorie parente</label>
          <select v-model="form.parent_id">
            <option value="">Aucune (racine)</option>
            <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div class="field">
          <label>Module</label>
          <select v-model="form.module">
            <option value="general">Général</option>
            <option value="stock">Stock</option>
            <option value="expense">Dépenses</option>
          </select>
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
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #8B5CF6; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 640px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.field input:focus, .field select:focus { border-color: #8B5CF6; box-shadow: 0 0 0 2px rgba(139,92,246,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #8B5CF6; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #7C3AED; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
