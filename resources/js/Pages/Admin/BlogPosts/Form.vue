<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ post: { type: Object, default: null } })
const isEdit = !!props.post

const form = useForm({
  title: props.post?.title ?? '',
  excerpt: props.post?.excerpt ?? '',
  body: props.post?.body ?? '',
  category: props.post?.category ?? '',
  is_published: props.post?.is_published ?? false,
})

function submit() {
  if (isEdit) {
    form.put(route('admin.blog-posts.update', props.post.id))
  } else {
    form.post(route('admin.blog-posts.store'))
  }
}
</script>

<template>
  <AdminLayout :title="isEdit ? 'Modifier l\'article' : 'Nouvel article'">
    <Head :title="isEdit ? 'Admin · Modifier article' : 'Admin · Nouvel article'" />

    <div class="toolbar">
      <h1 class="page-title">{{ isEdit ? 'Modifier l\'article' : 'Nouvel article' }}</h1>
      <Link :href="route('admin.blog-posts.index')" class="kh-btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form class="kh-card form-card" @submit.prevent="submit">
      <div class="form-group">
        <label>Titre *</label>
        <input type="text" v-model="form.title" required maxlength="255">
        <div v-if="form.errors.title" class="error">{{ form.errors.title }}</div>
      </div>

      <div class="form-group">
        <label>Extrait (résumé)</label>
        <textarea v-model="form.excerpt" rows="3" maxlength="1000" placeholder="Court résumé affiché dans la liste"></textarea>
        <div v-if="form.errors.excerpt" class="error">{{ form.errors.excerpt }}</div>
      </div>

      <div class="form-group">
        <label>Contenu *</label>
        <textarea v-model="form.body" rows="16" required></textarea>
        <div v-if="form.errors.body" class="error">{{ form.errors.body }}</div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Catégorie</label>
          <input type="text" v-model="form.category" placeholder="Ex: Guide, Actualité…">
        </div>
        <div class="form-group">
          <label class="checkbox-label" style="margin-top:1.5rem">
            <input type="checkbox" v-model="form.is_published">
            <span>Publier immédiatement</span>
          </label>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="kh-btn" :disabled="form.processing">
          <i class="fa-solid fa-save"></i> {{ isEdit ? 'Enregistrer' : 'Créer l\'article' }}
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
