<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  stats: Object,
  popularModules: Array,
  recentSubscriptions: Array,
  recentCompanies: Array,
})

const mainCards = [
  { label: 'Entreprises total',     value: props.stats.companies_total,   icon: 'fa-building',        color: '#3B82F6' },
  { label: 'Actives',               value: props.stats.companies_active,  icon: 'fa-circle-check',    color: '#10B981' },
  { label: 'En essai',              value: props.stats.companies_trial,   icon: 'fa-hourglass-half',  color: '#F59E0B' },
  { label: 'Expirées / Suspendues', value: props.stats.companies_expired, icon: 'fa-circle-xmark',    color: '#EF4444' },
  { label: 'Utilisateurs',          value: props.stats.users_total,       icon: 'fa-users',           color: '#8B5CF6' },
  { label: 'Super admins',          value: props.stats.users_admin,       icon: 'fa-user-shield',     color: '#D97706' },
]

const revenueCards = [
  { label: 'MRR (Revenu mensuel)',   value: fmt(props.stats.mrr), icon: 'fa-money-bill-trend-up', color: '#10B981' },
  { label: 'Nouveaux cette semaine', value: props.stats.new_this_week,    icon: 'fa-user-plus',        color: '#3B82F6' },
  { label: 'Conversion Trial→Payé', value: props.stats.conversion_rate + '%', icon: 'fa-chart-line', color: '#8B5CF6' },
]

function fmt(v) {
  return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF'
}
function fmtDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—'
}

const STATUS_COLORS = {
  active: '#10B981', trial: '#F59E0B', grace_period: '#6366F1',
  expired: '#EF4444', suspended: '#DC2626', cancelled: '#6B7280',
}
const STATUS_LABELS = {
  active: 'Actif', trial: 'Essai', grace_period: 'Grâce',
  expired: 'Expiré', suspended: 'Suspendu', cancelled: 'Annulé',
}
</script>

<template>
  <AdminLayout title="Tableau de bord Admin">
    <Head title="Admin · Dashboard" />

    <h1 class="page-title">Vue d'ensemble</h1>

    <!-- Stats principales -->
    <div class="stats-grid">
      <div v-for="card in mainCards" :key="card.label" class="stat-card">
        <div class="stat-icon" :style="{ background: card.color + '18', color: card.color }">
          <i :class="['fa-solid', card.icon]"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value">{{ card.value }}</div>
          <div class="stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Revenus / KPIs -->
    <div class="stats-grid rev-grid">
      <div v-for="card in revenueCards" :key="card.label" class="stat-card rev-card" :style="{ borderLeftColor: card.color }">
        <div class="stat-icon" :style="{ background: card.color + '18', color: card.color }">
          <i :class="['fa-solid', card.icon]"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value" style="font-size:1.2rem;">{{ card.value }}</div>
          <div class="stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Grille dual -->
    <div class="dual-grid">
      <!-- Modules populaires -->
      <div class="card">
        <div class="card-title"><i class="fa-solid fa-ranking-star" style="color:#F59E0B"></i> Modules populaires</div>
        <div v-if="!popularModules?.length" class="empty">Aucune donnée</div>
        <div v-for="m in popularModules" :key="m.name" class="mod-row">
          <span class="mod-name">{{ m.name }}</span>
          <span class="mod-count">{{ m.count }} entreprise{{ m.count > 1 ? 's' : '' }}</span>
        </div>
      </div>

      <!-- Entreprises récentes -->
      <div class="card">
        <div class="card-title"><i class="fa-solid fa-building" style="color:#3B82F6"></i> Entreprises récentes</div>
        <div v-if="!recentCompanies?.length" class="empty">Aucune</div>
        <div v-for="c in recentCompanies" :key="c.id" class="co-row">
          <div>
            <Link :href="route('admin.companies.show', c.id)" class="co-link">{{ c.name }}</Link>
            <div class="co-date">{{ fmtDate(c.created_at) }}</div>
          </div>
          <span class="badge" :style="{ background: (STATUS_COLORS[c.subscription_status] || '#6B7280') + '22',
                                        color: STATUS_COLORS[c.subscription_status] || '#6B7280' }">
            {{ STATUS_LABELS[c.subscription_status] || c.subscription_status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Abonnements récents -->
    <div class="card" style="margin-top:16px;">
      <div class="card-title"><i class="fa-solid fa-credit-card" style="color:#10B981"></i> Derniers abonnements</div>
      <table class="kh-table" v-if="recentSubscriptions?.length">
        <thead>
          <tr><th>Entreprise</th><th>Plan</th><th>Statut</th><th>Montant</th><th>Date</th></tr>
        </thead>
        <tbody>
          <tr v-for="s in recentSubscriptions" :key="s.id">
            <td>{{ s.company?.name || '—' }}</td>
            <td>{{ s.plan?.name || '—' }}</td>
            <td><span class="badge" :style="{ background: (STATUS_COLORS[s.status] || '#6B7280') + '22', color: STATUS_COLORS[s.status] || '#6B7280' }">{{ STATUS_LABELS[s.status] || s.status }}</span></td>
            <td>{{ fmt(s.amount_paid) }}</td>
            <td>{{ fmtDate(s.created_at) }}</td>
          </tr>
        </tbody>
      </table>
      <div v-else class="empty">Aucun abonnement</div>
    </div>

    <!-- Quick links -->
    <div class="quick-links">
      <Link :href="route('admin.companies.index')" class="ql-btn">
        <i class="fa-solid fa-building"></i> Entreprises
      </Link>
      <Link :href="route('admin.plans.index')" class="ql-btn ql-btn-alt">
        <i class="fa-solid fa-tags"></i> Plans & Tarifs
      </Link>
      <Link :href="route('admin.modules.index')" class="ql-btn" style="background:#F59E0B;">
        <i class="fa-solid fa-puzzle-piece"></i> Modules
      </Link>
      <Link :href="route('admin.subscriptions.index')" class="ql-btn" style="background:#10B981;">
        <i class="fa-solid fa-credit-card"></i> Abonnements
      </Link>
      <Link :href="route('admin.legal-pages.index')" class="ql-btn" style="background:#8B5CF6;">
        <i class="fa-solid fa-file-contract"></i> Pages légales
      </Link>
    </div>
  </AdminLayout>
</template>

<style scoped>
.page-title {
  font-size: 1.15rem; font-weight: 700; color: #111827;
  margin-bottom: 24px; padding-left: 12px;
  border-left: 3px solid #6366F1;
}
.stats-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px; margin-bottom: 20px;
}
.rev-grid { grid-template-columns: repeat(3, 1fr); }
.stat-card {
  background: #fff; border: 1px solid #E5E7EB;
  padding: 18px 16px; display: flex; align-items: center; gap: 14px;
}
.rev-card { border-left: 3px solid; }
.stat-icon {
  width: 42px; height: 42px; display: flex; align-items: center;
  justify-content: center; font-size: 1.05rem; flex-shrink: 0;
}
.stat-value { font-size: 1.5rem; font-weight: 800; color: #111827; line-height: 1; }
.stat-label { font-size: 0.72rem; color: #6B7280; margin-top: 4px; }

.dual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 4px; }
.card { background: #fff; border: 1px solid #E5E7EB; padding: 18px; }
.card-title { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #F3F4F6; display: flex; align-items: center; gap: 8px; }
.mod-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #F9FAFB; font-size: 0.82rem; }
.mod-name { font-weight: 600; color: #111827; }
.mod-count { color: #6B7280; font-size: 0.75rem; }
.co-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #F9FAFB; }
.co-link { color: #6366F1; font-weight: 600; font-size: 0.82rem; text-decoration: none; }
.co-link:hover { text-decoration: underline; }
.co-date { font-size: 0.72rem; color: #9CA3AF; }
.badge { display: inline-block; padding: 2px 8px; font-size: 0.72rem; font-weight: 600; }

.kh-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.kh-table th { background: #F9FAFB; padding: 8px 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.kh-table td { padding: 8px 12px; border-bottom: 1px solid #F3F4F6; color: #374151; }

.empty { text-align: center; color: #9CA3AF; padding: 20px; font-size: 0.82rem; }
.quick-links { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 24px; }
.ql-btn {
  display: inline-flex; align-items: center; gap: 8px;
  background: #6366F1; color: #fff; padding: 10px 18px;
  font-size: 0.82rem; font-weight: 600; text-decoration: none;
}
.ql-btn-alt { background: #374151; }
@media (max-width: 768px) { .dual-grid, .rev-grid { grid-template-columns: 1fr; } }
</style>
