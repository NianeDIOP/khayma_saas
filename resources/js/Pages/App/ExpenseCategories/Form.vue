<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ category: { type: Object, default: null } })
const isEditing = !!props.category

const form = useForm({
  name: props.category?.name || '',
})

function submit() {
  if (isEditing) {
    form.put(route('app.expense-categories.update', { expense_category: props.category.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.expense-categories.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEditing ? 'Modifier catégorie' : 'Nouvelle catégorie'">
    <Head :title="isEditing ? 'Modifier catégorie de dépense' : 'Nouvelle catégorie de dépense'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-tags" style="color:#DC2626"></i>
        {{ isEditing ? 'Modifier la catégorie' : 'Nouvelle catégorie de dépense' }}
      </h1>
      <Link :href="route('app.expense-categories.index', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="field">
        <label>Nom <span class="req">*</span></label>
        <input v-model="form.name" type="text" :class="{ 'has-error': form.errors.name }" />
        <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> {{ isEditing ? 'Mettre à jour' : 'Créer' }}
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
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 480px; }
.field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.field input:focus { border-color: #DC2626; box-shadow: 0 0 0 2px rgba(220,38,38,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #DC2626; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #B91C1C; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
