<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  kpis: Object,
  chartDays: Array,
  chartValues: Array,
  payLabels: Array,
  payValues: Array,
  recentUsers: Array,
  activeModules: Array,
})

const cards = [
  { label: 'Utilisateurs',   value: props.stats.users_count,   icon: 'fa-users',          color: '#3B82F6' },
  { label: 'Modules actifs',  value: props.stats.modules_count, icon: 'fa-puzzle-piece',    color: '#8B5CF6' },
  { label: 'Abonnement',      value: props.stats.subscription,  icon: 'fa-credit-card',     color: '#10B981', isText: true },
  { label: 'Plan actuel',     value: props.stats.active_plan,   icon: 'fa-star',            color: '#F59E0B', isText: true },
]

function fmt(v) {
  return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' XOF'
}

// ApexCharts: line chart â€” ventes 7 derniers jours
const salesChartOptions = computed(() => ({
  chart: { type: 'area', height: 200, toolbar: { show: false }, sparkline: { enabled: false } },
  colors: ['#10B981'],
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: { categories: props.chartDays, labels: { style: { fontSize: '11px', colors: '#6B7280' } } },
  yaxis: { labels: { formatter: v => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) + ' XOF', style: { fontSize: '11px', colors: '#6B7280' } } },
  grid: { borderColor: '#F3F4F6', strokeDashArray: 4 },
  tooltip: { y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' XOF' } },
  dataLabels: { enabled: false },
}))

const salesChartSeries = computed(() => [{ name: 'Ventes', data: props.chartValues }])

// ApexCharts: pie chart â€” modes de paiement
const payChartOptions = computed(() => ({
  chart: { type: 'donut', height: 220 },
  labels: props.payLabels.length ? props.payLabels : ['Aucun'],
  colors: ['#10B981', '#3B82F6', '#F97316', '#8B5CF6', '#EAB308', '#EF4444'],
  legend: { position: 'bottom', fontSize: '12px' },
  dataLabels: { enabled: true, formatter: (val) => Math.round(val) + '%' },
  plotOptions: { pie: { donut: { size: '55%' } } },
  tooltip: { y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' XOF' } },
}))

const payChartSeries = computed(() => props.payValues.length ? props.payValues : [1])
</script>

<template>
  <AppLayout title="Tableau de bord">
    <Head title="Dashboard" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-gauge-high" style="color:#10B981"></i> Tableau de bord</h1>
    </div>

    <!-- Trial banner -->
    <div v-if="stats.trial_days_left !== null" class="trial-banner">
      <i class="fa-solid fa-hourglass-half"></i>
      <span>PÃ©riode d'essai : <strong>{{ stats.trial_days_left }} jour(s)</strong> restant(s)</span>
      <a href="#" class="trial-upgrade">Passer Ã  un plan payant</a>
    </div>

    <!-- Stats cards -->
    <div class="stats-grid">
      <div v-for="card in cards" :key="card.label" class="stat-card">
        <div class="stat-icon" :style="{ background: card.color + '18', color: card.color }">
          <i :class="['fa-solid', card.icon]"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value" :class="{ 'stat-value-text': card.isText }">{{ card.value }}</div>
          <div class="stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- KPI mÃ©tier: aujourd'hui + ce mois -->
    <div class="kpi-section-title"><i class="fa-solid fa-chart-pie" style="color:#10B981"></i> Performance financiÃ¨re</div>
    <div class="kpi-grid">
      <div class="kpi-card" style="border-top-color:#10B981">
        <div class="kpi-label">Ventes aujourd'hui</div>
        <div class="kpi-value text-green">{{ fmt(kpis.sales_today) }}</div>
      </div>
      <div class="kpi-card" style="border-top-color:#EF4444">
        <div class="kpi-label">DÃ©penses aujourd'hui</div>
        <div class="kpi-value text-red">{{ fmt(kpis.expenses_today) }}</div>
      </div>
      <div class="kpi-card" style="border-top-color:#8B5CF6">
        <div class="kpi-label">BÃ©nÃ©fice net (aujourd'hui)</div>
        <div class="kpi-value" :class="kpis.profit_today >= 0 ? 'text-green' : 'text-red'">{{ fmt(kpis.profit_today) }}</div>
      </div>
      <div class="kpi-card" style="border-top-color:#3B82F6">
        <div class="kpi-label">Ventes ce mois</div>
        <div class="kpi-value text-blue">{{ fmt(kpis.sales_month) }}</div>
      </div>
      <div class="kpi-card" style="border-top-color:#F59E0B">
        <div class="kpi-label">DÃ©penses ce mois</div>
        <div class="kpi-value text-amber">{{ fmt(kpis.expenses_month) }}</div>
      </div>
      <div class="kpi-card" style="border-top-color:#06B6D4">
        <div class="kpi-label">BÃ©nÃ©fice net (ce mois)</div>
        <div class="kpi-value" :class="kpis.profit_month >= 0 ? 'text-green' : 'text-red'">{{ fmt(kpis.profit_month) }}</div>
      </div>
    </div>

    <!-- Charts row -->
    <div class="charts-row">
      <div class="chart-card" style="flex:2;">
        <h3 class="chart-title"><i class="fa-solid fa-chart-area" style="color:#10B981"></i> Ventes â€” 7 derniers jours</h3>
        <apexchart type="area" height="200" :options="salesChartOptions" :series="salesChartSeries" />
      </div>
      <div class="chart-card" style="flex:1;">
        <h3 class="chart-title"><i class="fa-solid fa-circle-half-stroke" style="color:#3B82F6"></i> Paiements (ce mois)</h3>
        <apexchart v-if="payValues.length" type="donut" height="220" :options="payChartOptions" :series="payChartSeries" />
        <div v-else class="empty-chart-msg">Aucune vente ce mois</div>
      </div>
    </div>

    <!-- Sections -->
    <div class="sections-row">
      <!-- Active Modules -->
      <div class="section-card">
        <h2 class="section-title"><i class="fa-solid fa-puzzle-piece" style="color:#8B5CF6"></i> Modules actifs</h2>
        <div v-if="activeModules.length === 0" class="empty-msg">Aucun module activÃ©.</div>
        <div v-for="mod in activeModules" :key="mod.id" class="module-row">
          <i :class="mod.icon" class="module-icon"></i>
          <span class="module-name">{{ mod.name }}</span>
        </div>
      </div>

      <!-- Recent Team Members -->
      <div class="section-card">
        <h2 class="section-title"><i class="fa-solid fa-users" style="color:#3B82F6"></i> Ã‰quipe rÃ©cente</h2>
        <div v-if="recentUsers.length === 0" class="empty-msg">Aucun membre.</div>
        <div v-for="user in recentUsers" :key="user.id" class="user-row">
          <div class="user-avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
          <div class="user-info">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-role">{{ user.role }}</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title {
  font-size: 1.15rem; font-weight: 700; color: #111827;
  padding-left: 12px;
  border-left: 3px solid #10B981;
  display: flex; align-items: center; gap: 10px;
}

/* â”€â”€ Trial banner â”€â”€â”€ */
.trial-banner {
  background: #FEF3C7; border: 1px solid #FDE68A; padding: 12px 18px;
  display: flex; align-items: center; gap: 10px; margin-bottom: 20px;
  font-size: 0.84rem; color: #92400E;
}
.trial-upgrade {
  margin-left: auto; font-weight: 600; color: #D97706;
  text-decoration: underline; white-space: nowrap;
}

/* â”€â”€ Stats Grid â”€â”€â”€ */
.stats-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px; margin-bottom: 28px;
}
.stat-card {
  background: #fff; border: 1px solid #E5E7EB;
  padding: 20px 18px; display: flex; align-items: center; gap: 14px;
}
.stat-icon {
  width: 44px; height: 44px; display: flex; align-items: center;
  justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
.stat-value { font-size: 1.5rem; font-weight: 800; color: #111827; line-height: 1; }
.stat-value-text { font-size: 0.95rem; font-weight: 700; text-transform: capitalize; }
.stat-label { font-size: 0.75rem; color: #6B7280; margin-top: 4px; }

/* â”€â”€ KPI section â”€â”€â”€ */
.kpi-section-title {
  font-size: 0.82rem; font-weight: 700; color: #374151; text-transform: uppercase;
  letter-spacing: 0.4px; margin-bottom: 12px; display: flex; align-items: center; gap: 7px;
}
.kpi-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 28px;
}
.kpi-card {
  background: #fff; border: 1px solid #E5E7EB; border-top: 3px solid #E5E7EB;
  padding: 16px 18px;
}
.kpi-label { font-size: 0.73rem; color: #6B7280; margin-bottom: 6px; }
.kpi-value { font-size: 1.2rem; font-weight: 800; color: #111827; }
.text-green { color: #10B981; }
.text-red   { color: #EF4444; }
.text-blue  { color: #3B82F6; }
.text-amber { color: #F59E0B; }

/* â”€â”€ Charts â”€â”€â”€ */
.charts-row {
  display: flex; gap: 16px; margin-bottom: 28px;
}
.chart-card {
  background: #fff; border: 1px solid #E5E7EB; padding: 20px;
}
.chart-title {
  font-size: 0.82rem; font-weight: 700; color: #111827;
  margin-bottom: 12px; display: flex; align-items: center; gap: 7px;
}
.empty-chart-msg {
  height: 220px; display: flex; align-items: center; justify-content: center;
  font-size: 0.82rem; color: #9CA3AF; font-style: italic;
}

/* â”€â”€ Sections row â”€â”€â”€ */
.sections-row {
  display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
}
.section-card {
  background: #fff; border: 1px solid #E5E7EB; padding: 20px;
}
.section-title {
  font-size: 0.88rem; font-weight: 700; color: #111827;
  margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
}
.empty-msg { font-size: 0.82rem; color: #9CA3AF; font-style: italic; }

/* Module rows */
.module-row {
  display: flex; align-items: center; gap: 10px; padding: 8px 0;
  border-bottom: 1px solid #F3F4F6; font-size: 0.84rem; color: #374151;
}
.module-row:last-child { border-bottom: none; }
.module-icon { width: 20px; text-align: center; font-size: 1rem; color: #6B7280; }
.module-name { font-weight: 500; }

/* User rows */
.user-row {
  display: flex; align-items: center; gap: 10px; padding: 8px 0;
  border-bottom: 1px solid #F3F4F6;
}
.user-row:last-child { border-bottom: none; }
.user-avatar {
  width: 32px; height: 32px; background: #EEF2FF; color: #4F46E5;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.82rem; flex-shrink: 0;
}
.user-info { flex: 1; }
.user-name { font-size: 0.84rem; font-weight: 600; color: #111827; }
.user-role { font-size: 0.72rem; color: #6B7280; text-transform: capitalize; }
</style>
