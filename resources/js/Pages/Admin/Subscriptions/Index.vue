<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ subscriptions: Object, filters: Object, stats: Object })

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const period = ref(props.filters.period || '')

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) }
function fmtDate(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const STATUS_LABELS = {
  active: { label: 'Actif', color: '#10B981' },
  expired: { label: 'Expiré', color: '#EF4444' },
  cancelled: { label: 'Annulé', color: '#6B7280' },
  grace_period: { label: 'Grâce', color: '#6366F1' },
}

function applyFilters() {
  router.get(route('admin.subscriptions.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
    period: period.value || undefined,
  }, { preserveState: true, replace: true })
}

function exportCsv() {
  const params = new URLSearchParams()
  if (status.value) params.set('status', status.value)
  window.open(route('admin.subscriptions.export') + '?' + params.toString(), '_blank')
}
</script>

<template>
  <AdminLayout title="Abonnements">
    <Head title="Admin · Abonnements" />

    <div class="toolbar">
      <h1 class="page-title">Abonnements & Paiements</h1>
      <button @click="exportCsv" class="export-btn">
        <i class="fa-solid fa-file-csv"></i> Exporter CSV
      </button>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat"><span class="stat-val">{{ stats.total }}</span><span class="stat-lbl">Total</span></div>
      <div class="stat"><span class="stat-val" style="color:#10B981;">{{ stats.active }}</span><span class="stat-lbl">Actifs</span></div>
      <div class="stat"><span class="stat-val" style="color:#F59E0B;">{{ fmt(stats.revenue) }} XOF</span><span class="stat-lbl">Revenus actifs</span></div>
    </div>

    <!-- Filtres -->
    <div class="filters">
      <input v-model="search" @keyup.enter="applyFilters" type="text" class="kh-input" placeholder="Rechercher entreprise…" />
      <select v-model="status" @change="applyFilters" class="kh-input">
        <option value="">Tous les statuts</option>
        <option v-for="(s, k) in STATUS_LABELS" :key="k" :value="k">{{ s.label }}</option>
      </select>
      <select v-model="period" @change="applyFilters" class="kh-input">
        <option value="">Toutes les périodes</option>
        <option value="monthly">Mensuel</option>
        <option value="quarterly">Trimestriel</option>
        <option value="yearly">Annuel</option>
      </select>
      <button @click="applyFilters" class="kh-btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table class="kh-table">
        <thead>
          <tr>
            <th>#</th><th>Entreprise</th><th>Plan</th><th>Module</th><th>Période</th>
            <th>Statut</th><th>Montant</th><th>Début</th><th>Fin</th><th>Réf.</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!subscriptions.data.length">
            <td colspan="10" class="empty">Aucun abonnement trouvé.</td>
          </tr>
          <tr v-for="s in subscriptions.data" :key="s.id">
            <td>{{ s.id }}</td>
            <td class="co-name">{{ s.company?.name || '—' }}</td>
            <td>{{ s.plan?.name || '—' }}</td>
            <td>{{ s.module?.name || '—' }}</td>
            <td>{{ s.billing_period }}</td>
            <td>
              <span class="badge" :style="{ background: (STATUS_LABELS[s.status]?.color || '#6B7280') + '22', color: STATUS_LABELS[s.status]?.color || '#6B7280' }">
                {{ STATUS_LABELS[s.status]?.label || s.status }}
              </span>
            </td>
            <td class="amount">{{ fmt(s.amount_paid) }}</td>
            <td>{{ fmtDate(s.starts_at) }}</td>
            <td>{{ fmtDate(s.ends_at) }}</td>
            <td class="ref">{{ s.payment_reference || '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="subscriptions.last_page > 1" class="pagination">
      <Link v-for="p in subscriptions.links" :key="p.label"
        :href="p.url || '#'" :class="['page-btn', { active: p.active, disabled: !p.url }]"
        v-html="p.label" />
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.page-title { font-size: 1.1rem; font-weight: 700; color: #111827; padding-left: 12px; border-left: 3px solid #10B981; }
.export-btn { background: #10B981; color: #fff; border: none; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.stats-row { display: flex; gap: 16px; margin-bottom: 16px; }
.stat { background: #fff; border: 1px solid #E5E7EB; padding: 14px 18px; flex: 1; }
.stat-val { font-size: 1.3rem; font-weight: 800; color: #111827; display: block; }
.stat-lbl { font-size: 0.72rem; color: #6B7280; }
.filters { display: flex; gap: 8px; margin-bottom: 16px; align-items: center; }
.kh-input { padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; }
.kh-input:focus { border-color: #10B981; outline: none; }
.kh-btn-sm { background: #10B981; color: #fff; border: none; padding: 8px 12px; cursor: pointer; }
.table-wrap { overflow-x: auto; }
.kh-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
.kh-table th { background: #F9FAFB; padding: 8px 10px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
.kh-table td { padding: 8px 10px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.co-name { font-weight: 600; color: #111827; }
.amount { font-weight: 700; color: #111827; }
.ref { font-size: 0.72rem; color: #9CA3AF; }
.badge { display: inline-block; padding: 2px 8px; font-size: 0.7rem; font-weight: 600; }
.empty { text-align: center; color: #9CA3AF; padding: 32px; }
.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { padding: 5px 10px; border: 1px solid #D1D5DB; font-size: 0.78rem; text-decoration: none; color: #374151; }
.page-btn.active { background: #10B981; color: #fff; border-color: #10B981; }
.page-btn.disabled { opacity: 0.4; pointer-events: none; }
</style>
