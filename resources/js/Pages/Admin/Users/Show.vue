<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ user: Object })

function toggleAdmin() {
  if (!confirm(`${props.user.is_admin ? 'Retirer' : 'Donner'} le rôle admin à ${props.user.name} ?`)) return
  router.patch(route('admin.users.toggle-admin', props.user.id))
}
</script>

<template>
  <AdminLayout title="Détail utilisateur">
    <Head :title="`Admin · ${user.name}`" />

    <div class="back-link">
      <Link :href="route('admin.users.index')"><i class="fa-solid fa-arrow-left"></i> Utilisateurs</Link>
    </div>

    <div class="profile-card">
      <div class="avatar">{{ user.name?.charAt(0)?.toUpperCase() }}</div>
      <div class="info">
        <h2 class="name">{{ user.name }}</h2>
        <p class="email">{{ user.email }}</p>
        <div class="badges">
          <span class="badge admin" v-if="user.is_admin">Admin</span>
          <span class="badge" v-else>Utilisateur</span>
          <span class="badge date"><i class="fa-solid fa-calendar"></i> Inscrit {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}</span>
        </div>
      </div>
      <button @click="toggleAdmin" :class="['toggle-btn', user.is_admin ? 'remove' : 'add']">
        {{ user.is_admin ? 'Retirer admin' : 'Passer admin' }}
      </button>
    </div>

    <!-- Entreprises -->
    <div class="section">
      <h3 class="section-title">Entreprises ({{ user.companies?.length || 0 }})</h3>
      <div v-if="!user.companies?.length" class="empty">Aucune entreprise associée.</div>
      <div class="table-wrap" v-else>
        <table class="kh-table">
          <thead>
            <tr><th>Entreprise</th><th>Slug</th><th>Rôle</th><th>Statut</th><th></th></tr>
          </thead>
          <tbody>
            <tr v-for="c in user.companies" :key="c.id">
              <td class="co-name">{{ c.name }}</td>
              <td class="slug">{{ c.slug }}</td>
              <td>{{ c.pivot?.role || 'owner' }}</td>
              <td>
                <span :class="['status', c.subscription_status]">{{ c.subscription_status }}</span>
              </td>
              <td>
                <Link :href="route('admin.companies.show', c.id)" class="action-link">Voir</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.back-link { margin-bottom: 20px; }
.back-link a { font-size: 0.82rem; color: #6366F1; text-decoration: none; font-weight: 600; }
.profile-card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
.avatar { width: 48px; height: 48px; background: #EEF2FF; color: #6366F1; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; border-radius: 50%; }
.info { flex: 1; }
.name { font-size: 1rem; font-weight: 700; color: #111827; }
.email { font-size: 0.82rem; color: #6B7280; }
.badges { display: flex; gap: 6px; margin-top: 6px; }
.badge { font-size: 0.7rem; padding: 2px 8px; font-weight: 600; background: #F3F4F6; color: #6B7280; }
.badge.admin { background: #EEF2FF; color: #6366F1; }
.badge.date { background: #F9FAFB; color: #9CA3AF; display: flex; align-items: center; gap: 4px; }
.toggle-btn { border: none; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.toggle-btn.add { background: #EEF2FF; color: #6366F1; }
.toggle-btn.remove { background: #FEE2E2; color: #EF4444; }
.section { background: #fff; border: 1px solid #E5E7EB; padding: 16px; }
.section-title { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 12px; }
.table-wrap { overflow-x: auto; }
.kh-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
.kh-table th { background: #F9FAFB; padding: 8px 10px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.kh-table td { padding: 8px 10px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.co-name { font-weight: 600; color: #111827; }
.slug { font-size: 0.72rem; color: #6B7280; }
.status { font-size: 0.7rem; padding: 2px 8px; font-weight: 600; }
.status.active { background: #D1FAE5; color: #10B981; }
.status.trial { background: #EEF2FF; color: #6366F1; }
.status.expired { background: #FEE2E2; color: #EF4444; }
.action-link { font-size: 0.78rem; color: #6366F1; text-decoration: none; font-weight: 600; }
.empty { text-align: center; color: #9CA3AF; padding: 24px; font-size: 0.85rem; }
</style>
