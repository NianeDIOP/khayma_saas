<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ service: Object })
const isEdit = !!props.service
const t = () => route().params._tenant

const form = useForm({
  name:       props.service?.name || '',
  start_time: props.service?.start_time?.substring(0, 5) || '',
  end_time:   props.service?.end_time?.substring(0, 5) || '',
  is_active:  props.service?.is_active ?? true,
})

function submit() {
  if (isEdit) {
    form.put(route('app.restaurant.services.update', { service: props.service.id, _tenant: t() }))
  } else {
    form.post(route('app.restaurant.services.store', { _tenant: t() }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier service' : 'Nouveau service'">
    <Head :title="isEdit ? 'Modifier service' : 'Nouveau service'" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-clock" style="color:#F59E0B"></i> {{ isEdit ? 'Modifier' : 'Nouveau' }} service</h1>
      <Link :href="route('app.restaurant.services.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="field">
        <label>Nom * <small>(ex: Matin, Midi, Soir)</small></label>
        <input v-model="form.name" type="text" :class="{ 'is-error': form.errors.name }" required />
        <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
      </div>

      <div class="form-row">
        <div class="field">
          <label>Heure début *</label>
          <input v-model="form.start_time" type="time" :class="{ 'is-error': form.errors.start_time }" required />
          <span v-if="form.errors.start_time" class="field-error">{{ form.errors.start_time }}</span>
        </div>
        <div class="field">
          <label>Heure fin *</label>
          <input v-model="form.end_time" type="time" :class="{ 'is-error': form.errors.end_time }" required />
          <span v-if="form.errors.end_time" class="field-error">{{ form.errors.end_time }}</span>
        </div>
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
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #F59E0B; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 500px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
.field label { font-size: 0.82rem; font-weight: 600; color: #374151; }
.field label small { font-weight: 400; color: #9CA3AF; }
.field input { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.88rem; outline: none; }
.field input:focus { border-color: #F59E0B; }
.field input.is-error { border-color: #EF4444; }
.field-error { font-size: 0.78rem; color: #EF4444; }
.field-check { margin-bottom: 22px; }
.field-check label { display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: #374151; cursor: pointer; }
.field-check input[type="checkbox"] { accent-color: #F59E0B; width: 16px; height: 16px; }
.btn-submit { background: #F59E0B; color: #fff; padding: 10px 24px; font-size: 0.88rem; font-weight: 600; border: none; cursor: pointer; }
.btn-submit:hover:not(:disabled) { background: #D97706; }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
