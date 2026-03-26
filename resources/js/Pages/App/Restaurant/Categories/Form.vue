<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ category: Object })
const isEdit = !!props.category
const t = () => route().params._tenant

const form = useForm({
  name:       props.category?.name || '',
  sort_order: props.category?.sort_order ?? 0,
  is_active:  props.category?.is_active ?? true,
})

function submit() {
  if (isEdit) {
    form.put(route('app.restaurant.categories.update', { category: props.category.id, _tenant: t() }))
  } else {
    form.post(route('app.restaurant.categories.store', { _tenant: t() }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier catégorie' : 'Nouvelle catégorie'">
    <Head :title="isEdit ? 'Modifier catégorie' : 'Nouvelle catégorie'" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-list" style="color:#EF4444"></i> {{ isEdit ? 'Modifier' : 'Nouvelle' }} catégorie</h1>
      <Link :href="route('app.restaurant.categories.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="field">
        <label for="name">Nom *</label>
        <input id="name" v-model="form.name" type="text" :class="{ 'is-error': form.errors.name }" required />
        <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
      </div>

      <div class="field">
        <label for="sort_order">Ordre d'affichage</label>
        <input id="sort_order" v-model.number="form.sort_order" type="number" min="0" />
      </div>

      <div class="field-check">
        <label><input v-model="form.is_active" type="checkbox" /> Actif</label>
      </div>

      <button type="submit" class="btn-submit" :disabled="form.processing">
        {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Mettre à jour' : 'Créer') }}
      </button>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #EF4444; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 500px; }
.field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
.field label { font-size: 0.82rem; font-weight: 600; color: #374151; }
.field input, .field select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.88rem; outline: none; }
.field input:focus, .field select:focus { border-color: #EF4444; }
.field input.is-error { border-color: #EF4444; }
.field-error { font-size: 0.78rem; color: #EF4444; }
.field-check { margin-bottom: 22px; }
.field-check label { display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: #374151; cursor: pointer; }
.field-check input[type="checkbox"] { accent-color: #EF4444; width: 16px; height: 16px; }
.btn-submit { background: #EF4444; color: #fff; padding: 10px 24px; font-size: 0.88rem; font-weight: 600; border: none; cursor: pointer; }
.btn-submit:hover:not(:disabled) { background: #DC2626; }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
