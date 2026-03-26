<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ module: { type: Object, default: null } })
const isEdit = !!props.module

const form = useForm({
  name: props.module?.name || '',
  code: props.module?.code || '',
  description: props.module?.description || '',
  icon: props.module?.icon || '',
  installation_fee: props.module?.installation_fee ?? 0,
  is_active: props.module?.is_active ?? true,
})

function submit() {
  if (isEdit) {
    form.put(route('admin.modules.update', props.module.id))
  } else {
    form.post(route('admin.modules.store'))
  }
}
</script>

<template>
  <AdminLayout :title="isEdit ? 'Modifier le module' : 'Nouveau module'">
    <Head :title="isEdit ? 'Admin · Modifier module' : 'Admin · Nouveau module'" />

    <div class="back-link">
      <Link :href="route('admin.modules.index')"><i class="fa-solid fa-arrow-left"></i> Modules</Link>
    </div>

    <div class="form-card">
      <h2 class="form-title">{{ isEdit ? 'Modifier le module' : 'Créer un module' }}</h2>

      <form @submit.prevent="submit">
        <div class="form-row">
          <div class="field">
            <label>Nom *</label>
            <input v-model="form.name" type="text" class="kh-input" placeholder="Restaurant, Boutique..." />
            <span v-if="form.errors.name" class="error">{{ form.errors.name }}</span>
          </div>
          <div class="field">
            <label>Code *</label>
            <input v-model="form.code" type="text" class="kh-input" placeholder="restaurant, boutique..." />
            <span v-if="form.errors.code" class="error">{{ form.errors.code }}</span>
          </div>
        </div>

        <div class="field">
          <label>Description</label>
          <textarea v-model="form.description" class="kh-input" rows="3" placeholder="Description du module..."></textarea>
        </div>

        <div class="form-row">
          <div class="field">
            <label>Icône Font Awesome</label>
            <input v-model="form.icon" type="text" class="kh-input" placeholder="fa-solid fa-utensils" />
            <div v-if="form.icon" class="icon-preview"><i :class="form.icon"></i> Aperçu</div>
          </div>
          <div class="field">
            <label>Frais d'installation (XOF) *</label>
            <input v-model.number="form.installation_fee" type="number" min="0" class="kh-input" />
          </div>
        </div>

        <div class="field">
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_active" />
            Module actif
          </label>
        </div>

        <div class="form-actions">
          <Link :href="route('admin.modules.index')" class="btn-cancel">Annuler</Link>
          <button type="submit" class="btn-submit" :disabled="form.processing">
            {{ isEdit ? 'Enregistrer' : 'Créer le module' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.back-link { margin-bottom: 20px; }
.back-link a { color: #6366F1; font-size: 0.82rem; text-decoration: none; font-weight: 600; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 600px; }
.form-title { font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { margin-bottom: 14px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.kh-input { width: 100%; padding: 8px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; }
.kh-input:focus { border-color: #F59E0B; outline: none; }
textarea.kh-input { resize: vertical; }
.error { color: #EF4444; font-size: 0.72rem; margin-top: 2px; display: block; }
.icon-preview { margin-top: 6px; font-size: 0.82rem; color: #F59E0B; display: flex; align-items: center; gap: 6px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: #374151; cursor: pointer; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #F3F4F6; }
.btn-cancel { padding: 8px 16px; background: #F3F4F6; color: #374151; font-size: 0.82rem; font-weight: 600; text-decoration: none; }
.btn-submit { padding: 8px 20px; background: #F59E0B; color: #fff; border: none; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.btn-submit:disabled { opacity: 0.6; }
</style>
