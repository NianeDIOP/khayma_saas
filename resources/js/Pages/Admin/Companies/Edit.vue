<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ company: Object })

const form = useForm({
  name: props.company.name || '',
  email: props.company.email || '',
  phone: props.company.phone || '',
  sector: props.company.sector || '',
  address: props.company.address || '',
  ninea: props.company.ninea || '',
})

function submit() {
  form.put(route('admin.companies.update', props.company.id))
}
</script>

<template>
  <AdminLayout title="Modifier l'entreprise">
    <Head title="Admin · Modifier entreprise" />

    <div class="back-link">
      <Link :href="route('admin.companies.show', company.id)"><i class="fa-solid fa-arrow-left"></i> {{ company.name }}</Link>
    </div>

    <div class="form-card">
      <h2 class="form-title">Modifier l'entreprise</h2>

      <form @submit.prevent="submit">
        <div class="form-row">
          <div class="field">
            <label>Nom *</label>
            <input v-model="form.name" type="text" class="kh-input" />
            <span v-if="form.errors.name" class="error">{{ form.errors.name }}</span>
          </div>
          <div class="field">
            <label>Email *</label>
            <input v-model="form.email" type="email" class="kh-input" />
            <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
          </div>
        </div>
        <div class="form-row">
          <div class="field">
            <label>Téléphone</label>
            <input v-model="form.phone" type="text" class="kh-input" />
          </div>
          <div class="field">
            <label>Secteur</label>
            <input v-model="form.sector" type="text" class="kh-input" />
          </div>
        </div>
        <div class="form-row">
          <div class="field">
            <label>Adresse</label>
            <input v-model="form.address" type="text" class="kh-input" />
          </div>
          <div class="field">
            <label>NINEA</label>
            <input v-model="form.ninea" type="text" class="kh-input" />
          </div>
        </div>

        <div class="form-actions">
          <Link :href="route('admin.companies.show', company.id)" class="btn-cancel">Annuler</Link>
          <button type="submit" class="btn-submit" :disabled="form.processing">Enregistrer</button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.back-link { margin-bottom: 20px; }
.back-link a { font-size: 0.82rem; color: #6366F1; text-decoration: none; font-weight: 600; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 600px; }
.form-title { font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { margin-bottom: 12px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.kh-input { width: 100%; padding: 8px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; }
.kh-input:focus { border-color: #6366F1; outline: none; }
.error { color: #EF4444; font-size: 0.72rem; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #F3F4F6; }
.btn-cancel { padding: 8px 16px; background: #F3F4F6; color: #374151; font-size: 0.82rem; font-weight: 600; text-decoration: none; }
.btn-submit { padding: 8px 20px; background: #6366F1; color: #fff; border: none; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.btn-submit:disabled { opacity: 0.6; }
</style>
