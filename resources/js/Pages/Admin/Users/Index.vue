<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ users: Object, filters: Object })
const search = ref(props.filters.search || '')
const role = ref(props.filters.role || '')

function applyFilters() {
  router.get(route('admin.users.index'), {
    search: search.value || undefined,
    role: role.value || undefined,
  }, { preserveState: true, replace: true })
}
</script>

<template>
  <AdminLayout title="Utilisateurs">
    <Head title="Admin · Utilisateurs" />

    <div class="toolbar">
      <h1 class="page-title">Utilisateurs</h1>
      <div class="filters">
        <input v-model="search" @keyup.enter="applyFilters" type="text"
          class="kh-input" placeholder="Nom ou email…" />
        <select v-model="role" @change="applyFilters" class="kh-input">
          <option value="">Tous les rôles</option>
          <option value="admin">Admins</option>
          <option value="user">Utilisateurs</option>
        </select>
        <button @click="applyFilters" class="kh-btn-sm">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </div>
    </div>

    <div class="table-wrap">
      <table class="kh-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Super admin</th>
            <th>Entreprises</th>
            <th>Inscrit le</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!users.data.length">
            <td colspan="7" class="empty">Aucun utilisateur trouvé.</td>
          </tr>
          <tr v-for="u in users.data" :key="u.id">
            <td>{{ u.id }}</td>
            <td class="user-name">{{ u.name }}</td>
            <td>{{ u.email }}</td>
            <td>
              <span v-if="u.is_super_admin" style="color:#6366F1;font-weight:700;">
                <i class="fa-solid fa-user-shield"></i> Oui
              </span>
              <span v-else style="color:#9CA3AF;">—</span>
            </td>
            <td>{{ u.companies?.length || 0 }}</td>
            <td>{{ new Date(u.created_at).toLocaleDateString('fr-FR') }}</td>
            <td>
              <Link :href="route('admin.users.show', u.id)" class="link-btn">
                Voir <i class="fa-solid fa-arrow-right"></i>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="users.last_page > 1" class="pagination">
      <Link v-for="p in users.links" :key="p.label"
        :href="p.url || '#'"
        :class="['page-btn', { active: p.active, disabled: !p.url }]"
        v-html="p.label" />
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.page-title { font-size: 1.1rem; font-weight: 700; color: #111827; padding-left: 12px; border-left: 3px solid #6366F1; }
.filters { display: flex; gap: 8px; align-items: center; }
.kh-input { padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; }
.kh-btn-sm { background: #6366F1; color: #fff; border: none; padding: 8px 12px; cursor: pointer; }
.table-wrap { overflow-x: auto; }
.kh-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.kh-table th { background: #F9FAFB; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.kh-table td { padding: 10px 12px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.kh-table tr:hover td { background: #F9FAFB; }
.user-name { font-weight: 600; color: #111827; }
.link-btn { color: #6366F1; font-size: 0.78rem; font-weight: 600; text-decoration: none; }
.link-btn:hover { text-decoration: underline; }
.empty { text-align: center; color: #9CA3AF; padding: 32px; }
.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { padding: 5px 10px; border: 1px solid #D1D5DB; font-size: 0.78rem; text-decoration: none; color: #374151; }
.page-btn.active { background: #6366F1; color: #fff; border-color: #6366F1; }
.page-btn.disabled { opacity: 0.4; pointer-events: none; }
</style>
