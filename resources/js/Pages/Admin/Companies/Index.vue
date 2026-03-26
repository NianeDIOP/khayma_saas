<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  companies: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

const STATUS_LABELS = {
  active:       { label: 'Actif',          color: '#10B981' },
  trial:        { label: 'Essai',          color: '#F59E0B' },
  grace_period: { label: 'Grâce',          color: '#6366F1' },
  expired:      { label: 'Expiré',         color: '#EF4444' },
  suspended:    { label: 'Suspendu',       color: '#DC2626' },
  cancelled:    { label: 'Annulé',         color: '#6B7280' },
}

function applyFilters() {
  router.get(route('admin.companies.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
  }, { preserveState: true, replace: true })
}
</script>

<template>
  <AdminLayout title="Entreprises">
    <Head title="Admin · Entreprises" />

    <div class="toolbar">
      <div class="toolbar-left">
        <h1 class="page-title">Entreprises</h1>
        <Link :href="route('admin.companies.create')" class="kh-btn-create">
          <i class="fa-solid fa-plus"></i> Créer
        </Link>
      </div>
      <div class="filters">
        <input v-model="search" @keyup.enter="applyFilters" type="text"
          class="kh-input" placeholder="Rechercher…" />
        <select v-model="status" @change="applyFilters" class="kh-input">
          <option value="">Tous les statuts</option>
          <option v-for="(s, k) in STATUS_LABELS" :key="k" :value="k">{{ s.label }}</option>
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
            <th>Entreprise</th>
            <th>Secteur</th>
            <th>Statut</th>
            <th>Modules</th>
            <th>Active</th>
            <th>Créée le</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!companies.data.length">
            <td colspan="8" class="empty">Aucune entreprise trouvée.</td>
          </tr>
          <tr v-for="c in companies.data" :key="c.id">
            <td>{{ c.id }}</td>
            <td>
              <div class="co-name">{{ c.name }}</div>
              <div class="co-slug">{{ c.slug }}</div>
            </td>
            <td>{{ c.sector || '—' }}</td>
            <td>
              <span class="badge"
                :style="{ background: (STATUS_LABELS[c.subscription_status]?.color || '#6B7280') + '22',
                          color: STATUS_LABELS[c.subscription_status]?.color || '#6B7280' }">
                {{ STATUS_LABELS[c.subscription_status]?.label || c.subscription_status }}
              </span>
            </td>
            <td>
              <span class="mod-count">{{ c.modules_count || 0 }}</span>
            </td>
            <td>
              <span :style="{ color: c.is_active ? '#10B981' : '#EF4444' }">
                {{ c.is_active ? 'Oui' : 'Non' }}
              </span>
            </td>
            <td>{{ new Date(c.created_at).toLocaleDateString('fr-FR') }}</td>
            <td>
              <Link :href="route('admin.companies.show', c.id)" class="link-btn">
                Voir <i class="fa-solid fa-arrow-right"></i>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="companies.last_page > 1" class="pagination">
      <Link v-for="p in companies.links" :key="p.label"
        :href="p.url || '#'"
        :class="['page-btn', { active: p.active, disabled: !p.url }]"
        v-html="p.label" />
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.toolbar-left { display: flex; align-items: center; gap: 12px; }
.page-title { font-size: 1.1rem; font-weight: 700; color: #111827; padding-left: 12px; border-left: 3px solid #6366F1; }
.kh-btn-create { background: #6366F1; color: #fff; padding: 6px 14px; font-size: 0.78rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
.filters { display: flex; gap: 8px; align-items: center; }
.kh-input { padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; background: #fff; }
.kh-input:focus { border-color: #6366F1; }
.kh-btn-sm { background: #6366F1; color: #fff; border: none; padding: 8px 12px; cursor: pointer; }
.table-wrap { overflow-x: auto; }
.kh-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.kh-table th { background: #F9FAFB; padding: 10px 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.kh-table td { padding: 10px 12px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.kh-table tr:hover td { background: #F9FAFB; }
.co-name { font-weight: 600; color: #111827; }
.co-slug { font-size: 0.72rem; color: #9CA3AF; }
.badge { display: inline-block; padding: 2px 8px; font-size: 0.72rem; font-weight: 600; }
.link-btn { color: #6366F1; font-size: 0.78rem; font-weight: 600; text-decoration: none; }
.link-btn:hover { text-decoration: underline; }
.mod-count { background: #EEF2FF; color: #6366F1; padding: 2px 8px; font-size: 0.72rem; font-weight: 600; }
.empty { text-align: center; color: #9CA3AF; padding: 32px; }
.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { padding: 5px 10px; border: 1px solid #D1D5DB; font-size: 0.78rem; text-decoration: none; color: #374151; }
.page-btn.active { background: #6366F1; color: #fff; border-color: #6366F1; }
.page-btn.disabled { opacity: 0.4; pointer-events: none; }
</style>
