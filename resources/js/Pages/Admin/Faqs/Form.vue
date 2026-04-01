<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ faq: { type: Object, default: null } })
const isEdit = !!props.faq

const form = useForm({
  question: props.faq?.question ?? '',
  answer: props.faq?.answer ?? '',
  category: props.faq?.category ?? '',
  sort_order: props.faq?.sort_order ?? 0,
  is_active: props.faq?.is_active ?? true,
})

function submit() {
  if (isEdit) {
    form.put(route('admin.faqs.update', props.faq.id))
  } else {
    form.post(route('admin.faqs.store'))
  }
}
</script>

<template>
  <AdminLayout :title="isEdit ? 'Modifier la FAQ' : 'Nouvelle FAQ'">
    <Head :title="isEdit ? 'Admin · Modifier FAQ' : 'Admin · Nouvelle FAQ'" />

    <div class="toolbar">
      <h1 class="page-title">{{ isEdit ? 'Modifier la FAQ' : 'Nouvelle FAQ' }}</h1>
      <Link :href="route('admin.faqs.index')" class="kh-btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form class="kh-card form-card" @submit.prevent="submit">
      <div class="form-group">
        <label>Question *</label>
        <input type="text" v-model="form.question" required maxlength="500">
        <div v-if="form.errors.question" class="error">{{ form.errors.question }}</div>
      </div>

      <div class="form-group">
        <label>Réponse *</label>
        <textarea v-model="form.answer" rows="8" required></textarea>
        <div v-if="form.errors.answer" class="error">{{ form.errors.answer }}</div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Catégorie</label>
          <input type="text" v-model="form.category" placeholder="Ex: Général, Restaurant…">
        </div>
        <div class="form-group">
          <label>Ordre</label>
          <input type="number" v-model="form.sort_order" min="0">
        </div>
      </div>

      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.is_active">
          <span>Active (visible sur le site)</span>
        </label>
      </div>

      <div class="form-actions">
        <button type="submit" class="kh-btn" :disabled="form.processing">
          <i class="fa-solid fa-save"></i> {{ isEdit ? 'Enregistrer' : 'Créer la FAQ' }}
        </button>
      </div>
    </form>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.35rem; font-weight: 700; }
.kh-btn { display: inline-flex; align-items: center; gap: 0.4rem; background: #10B981; color: #fff; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 600; border: none; cursor: pointer; }
.kh-btn-outline { display: inline-flex; align-items: center; gap: 0.4rem; background: #fff; color: #64748B; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 600; border: 1px solid #E2E8F0; cursor: pointer; text-decoration: none; }
.kh-card { background: #fff; border: 1px solid #E2E8F0; }
.form-card { padding: 2rem; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem; }
.form-group input, .form-group textarea { width: 100%; padding: 0.65rem 0.85rem; font-size: 0.9rem; border: 1px solid #E2E8F0; font-family: inherit; }
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: #10B981; }
.form-group textarea { resize: vertical; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.checkbox-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; }
.form-actions { padding-top: 1rem; border-top: 1px solid #F1F5F9; }
.error { color: #EF4444; font-size: 0.8rem; margin-top: 0.25rem; }
</style>
