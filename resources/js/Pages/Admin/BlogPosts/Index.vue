<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ posts: Array })

function deletePost(post) {
  if (!confirm(`Supprimer l'article "${post.title}" ?`)) return
  router.delete(route('admin.blog-posts.destroy', post.id))
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
  <AdminLayout title="Articles de blog">
    <Head title="Admin · Blog" />

    <div class="toolbar">
      <h1 class="page-title">Articles de blog</h1>
      <Link :href="route('admin.blog-posts.create')" class="kh-btn">
        <i class="fa-solid fa-plus"></i> Nouvel article
      </Link>
    </div>

    <div class="kh-card">
      <table class="kh-table">
        <thead>
          <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Catégorie</th>
            <th style="width:90px">Statut</th>
            <th style="width:110px">Publié le</th>
            <th style="width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="post in posts" :key="post.id">
            <td class="fw-600">{{ post.title }}</td>
            <td>{{ post.author?.name ?? '—' }}</td>
            <td><span v-if="post.category" class="badge-cat">{{ post.category }}</span><span v-else class="text-muted">—</span></td>
            <td>
              <span class="badge" :class="post.is_published ? 'badge-green' : 'badge-yellow'">
                {{ post.is_published ? 'Publié' : 'Brouillon' }}
              </span>
            </td>
            <td>{{ formatDate(post.published_at) }}</td>
            <td>
              <div class="actions">
                <Link :href="route('admin.blog-posts.edit', post.id)" class="action-btn edit"><i class="fa-solid fa-pen"></i></Link>
                <button class="action-btn delete" @click="deletePost(post)"><i class="fa-solid fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <tr v-if="!posts.length">
            <td colspan="6" class="text-center text-muted" style="padding:2rem">Aucun article. Créez-en un !</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.35rem; font-weight: 700; }
.kh-btn { display: inline-flex; align-items: center; gap: 0.4rem; background: #10B981; color: #fff; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; }
.kh-card { background: #fff; border: 1px solid #E2E8F0; }
.kh-table { width: 100%; border-collapse: collapse; }
.kh-table th { background: #F8FAFC; padding: 0.75rem 1rem; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748B; text-align: left; border-bottom: 1px solid #E2E8F0; }
.kh-table td { padding: 0.75rem 1rem; font-size: 0.88rem; border-bottom: 1px solid #F1F5F9; }
.fw-600 { font-weight: 600; }
.badge { padding: 0.2rem 0.6rem; font-size: 0.72rem; font-weight: 600; }
.badge-green { background: #D1FAE5; color: #059669; }
.badge-yellow { background: #FEF3C7; color: #D97706; }
.badge-cat { background: #EFF6FF; color: #3B82F6; padding: 0.2rem 0.6rem; font-size: 0.72rem; font-weight: 600; }
.text-center { text-align: center; }
.text-muted { color: #94A3B8; }
.actions { display: flex; gap: 0.35rem; }
.action-btn { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #fff; cursor: pointer; font-size: 0.78rem; color: #64748B; transition: all 0.15s; }
.action-btn.edit:hover { color: #3B82F6; border-color: #3B82F6; }
.action-btn.delete:hover { color: #EF4444; border-color: #EF4444; }
</style>
