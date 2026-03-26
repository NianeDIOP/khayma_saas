<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ logs: Object, filters: Object, actions: Array })

const search = ref(props.filters.search || '')
const action = ref(props.filters.action || '')

function fmtDate(d) { return d ? new Date(d).toLocaleString('fr-FR') : '—' }

function applyFilters() {
  router.get(route('admin.audit-logs.index'), {
    search: search.value || undefined,
    action: action.value || undefined,
  }, { preserveState: true, replace: true })
}
</script>

<template>
  <AdminLayout title="Journal d'audit">
    <Head title="Admin · Logs" />

    <div class="toolbar">
      <h1 class="page-title">Journal d'audit</h1>
      <span class="count">{{ logs.total }} entrée{{ logs.total > 1 ? 's' : '' }}</span>
    </div>

    <!-- Filters -->
    <div class="filters">
      <input v-model="search" @keyup.enter="applyFilters" type="text" class="kh-input" placeholder="Rechercher (action, modèle, utilisateur)…" />
      <select v-model="action" @change="applyFilters" class="kh-input">
        <option value="">Toutes les actions</option>
        <option v-for="a in actions" :key="a" :value="a">{{ a }}</option>
      </select>
      <button @click="applyFilters" class="kh-btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table class="kh-table">
        <thead>
          <tr>
            <th>Date</th><th>Utilisateur</th><th>Entreprise</th><th>Action</th><th>Type</th><th>Modèle ID</th><th>IP</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!logs.data.length">
            <td colspan="7" class="empty">Aucun log trouvé.</td>
          </tr>
          <tr v-for="log in logs.data" :key="log.id">
            <td class="date-cell">{{ fmtDate(log.created_at) }}</td>
            <td class="user-cell">{{ log.user?.name || '—' }}</td>
            <td>{{ log.company?.name || '—' }}</td>
            <td>
              <span class="action-badge" :class="log.action?.toLowerCase()">{{ log.action }}</span>
            </td>
            <td class="type-cell">{{ log.model_type?.split('\\').pop() || '—' }}</td>
            <td>{{ log.model_id || '—' }}</td>
            <td class="ip-cell">{{ log.ip_address || '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="logs.last_page > 1" class="pagination">
      <Link v-for="p in logs.links" :key="p.label"
        :href="p.url || '#'" :class="['page-btn', { active: p.active, disabled: !p.url }]"
        v-html="p.label" />
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.page-title { font-size: 1.1rem; font-weight: 700; color: #111827; padding-left: 12px; border-left: 3px solid #EF4444; }
.count { font-size: 0.78rem; color: #6B7280; }
.filters { display: flex; gap: 8px; margin-bottom: 16px; }
.kh-input { padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; }
.kh-input:focus { border-color: #EF4444; outline: none; }
.kh-btn-sm { background: #EF4444; color: #fff; border: none; padding: 8px 12px; cursor: pointer; }
.table-wrap { overflow-x: auto; }
.kh-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
.kh-table th { background: #F9FAFB; padding: 8px 10px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
.kh-table td { padding: 8px 10px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.date-cell { white-space: nowrap; font-size: 0.72rem; color: #6B7280; }
.user-cell { font-weight: 600; color: #111827; }
.type-cell { font-size: 0.72rem; color: #6366F1; font-weight: 600; }
.ip-cell { font-family: monospace; font-size: 0.72rem; color: #9CA3AF; }
.action-badge { display: inline-block; padding: 2px 8px; font-size: 0.7rem; font-weight: 600; background: #F3F4F6; color: #374151; }
.action-badge.created { background: #D1FAE5; color: #10B981; }
.action-badge.updated { background: #DBEAFE; color: #3B82F6; }
.action-badge.deleted { background: #FEE2E2; color: #EF4444; }
.action-badge.login { background: #EEF2FF; color: #6366F1; }
.empty { text-align: center; color: #9CA3AF; padding: 32px; }
.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { padding: 5px 10px; border: 1px solid #D1D5DB; font-size: 0.78rem; text-decoration: none; color: #374151; }
.page-btn.active { background: #EF4444; color: #fff; border-color: #EF4444; }
.page-btn.disabled { opacity: 0.4; pointer-events: none; }
</style>
