<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ depot: Object })
const isEdit = !!props.depot

const form = useForm({
  name:       props.depot?.name       || '',
  address:    props.depot?.address    || '',
  is_default: props.depot?.is_default || false,
})

function submit() {
  if (isEdit) {
    form.put(route('app.depots.update', { depot: props.depot.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.depots.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier dépôt' : 'Nouveau dépôt'">
    <Head :title="isEdit ? 'Modifier dépôt' : 'Nouveau dépôt'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-warehouse" style="color:#6366F1"></i>
        {{ isEdit ? 'Modifier le dépôt' : 'Nouveau dépôt' }}
      </h1>
      <Link :href="route('app.depots.index', { _tenant: route().params._tenant })" class="btn-back">
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
          <label>Adresse</label>
          <input v-model="form.address" type="text" />
        </div>
        <div class="field">
          <label class="checkbox-label">
            <input v-model="form.is_default" type="checkbox" />
            Dépôt par défaut
          </label>
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
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #6366F1; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 640px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.field input:focus { border-color: #6366F1; box-shadow: 0 0 0 2px rgba(99,102,241,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.82rem; cursor: pointer; }
.checkbox-label input { width: 16px; height: 16px; accent-color: #6366F1; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #6366F1; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #4F46E5; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
