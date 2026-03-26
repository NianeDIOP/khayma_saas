<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ customer: Object })
const isEdit = !!props.customer

const form = useForm({
  name:     props.customer?.name     || '',
  phone:    props.customer?.phone    || '',
  email:    props.customer?.email    || '',
  address:  props.customer?.address  || '',
  nif:      props.customer?.nif      || '',
  category: props.customer?.category || 'normal',
})

function submit() {
  if (isEdit) {
    form.put(route('app.customers.update', { customer: props.customer.id, _tenant: route().params._tenant }))
  } else {
    form.post(route('app.customers.store', { _tenant: route().params._tenant }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier client' : 'Nouveau client'">
    <Head :title="isEdit ? 'Modifier client' : 'Nouveau client'" />

    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-user-pen" style="color:#2563EB"></i>
        {{ isEdit ? 'Modifier le client' : 'Nouveau client' }}
      </h1>
      <Link :href="route('app.customers.index', { _tenant: route().params._tenant })" class="btn-back">
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
          <label>NIF</label>
          <input v-model="form.nif" type="text" :class="{ 'has-error': form.errors.nif }" />
          <span v-if="form.errors.nif" class="field-error">{{ form.errors.nif }}</span>
        </div>
        <div class="field full">
          <label>Adresse</label>
          <input v-model="form.address" type="text" :class="{ 'has-error': form.errors.address }" />
          <span v-if="form.errors.address" class="field-error">{{ form.errors.address }}</span>
        </div>
        <div class="field">
          <label>Catégorie</label>
          <select v-model="form.category">
            <option value="normal">Normal</option>
            <option value="vip">VIP</option>
            <option value="professional">Professionnel</option>
          </select>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> {{ isEdit ? 'Enregistrer' : 'Créer le client' }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #2563EB; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }

.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 640px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #DC2626; }
.field input, .field select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.field input:focus, .field select:focus { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37,99,235,0.12); }
.has-error { border-color: #DC2626 !important; }
.field-error { font-size: 0.72rem; color: #DC2626; }
.form-actions { margin-top: 20px; }
.btn-primary { background: #2563EB; color: #fff; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-primary:hover { background: #1D4ED8; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
