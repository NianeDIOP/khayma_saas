<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  kpis: Object,
  salesByPayment: Array,
  topProducts: Array,
  expensesByCategory: Array,
  customerDebts: Array,
  chartLabels: Array,
  chartSales: Array,
  chartExpenses: Array,
  filters: Object,
})

const t = () => route().params._tenant

const period   = ref(props.filters?.period || 'month')
const dateFrom = ref(props.filters?.date_from || '')
const dateTo   = ref(props.filters?.date_to || '')

function applyFilters() {
  const params = { period: period.value }
  if (period.value === 'custom') {
    params.date_from = dateFrom.value
    params.date_to   = dateTo.value
  }
  router.get(route('app.reports.index', { _tenant: t() }), params, { preserveState: true, replace: true })
}

function fmt(v)  { return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0)) + ' XOF' }
function fmtN(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) }

const PERIOD_LABELS = {
  today: "Aujourd'hui",
  week:  'Cette semaine',
  month: 'Ce mois',
  year:  'Cette année',
  custom:'Personnalisé',
}

// ── Chart 1: Ventes vs Dépenses (bar grouped) ─────────────────────
const trendChartOptions = computed(() => ({
  chart: { type: 'bar', height: 280, toolbar: { show: false } },
  colors: ['#10B981', '#EF4444'],
  plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
  xaxis: {
    categories: props.chartLabels,
    labels: { style: { fontSize: '11px', colors: '#6B7280' } },
    axisBorder: { show: false },
  },
  yaxis: {
    labels: {
      formatter: v => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v),
      style: { fontSize: '11px', colors: '#6B7280' },
    },
  },
  legend: { position: 'top', fontSize: '12px' },
  grid: { borderColor: '#F3F4F6', strokeDashArray: 4 },
  dataLabels: { enabled: false },
  tooltip: { y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' XOF' } },
}))

const trendChartSeries = computed(() => [
  { name: 'Ventes',   data: props.chartSales },
  { name: 'Dépenses', data: props.chartExpenses },
])

// ── Chart 2: Payment methods donut ────────────────────────────────
const payChartOptions = computed(() => ({
  chart: { type: 'donut', height: 240 },
  labels: props.salesByPayment.map(p => p.label),
  colors: ['#10B981', '#3B82F6', '#F97316', '#8B5CF6', '#EAB308', '#EF4444'],
  legend: { position: 'bottom', fontSize: '12px' },
  dataLabels: { enabled: true, formatter: val => Math.round(val) + '%' },
  plotOptions: { pie: { donut: { size: '55%' } } },
  tooltip: { y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' XOF' } },
}))

const payChartSeries = computed(() =>
  props.salesByPayment.length ? props.salesByPayment.map(p => p.total) : [1]
)

// ── Chart 3: Expenses by category (horizontal bar) ────────────────
const expChartOptions = computed(() => ({
  chart: { type: 'bar', height: 240, toolbar: { show: false } },
  colors: ['#F59E0B'],
  plotOptions: { bar: { horizontal: true, borderRadius: 3, dataLabels: { position: 'top' } } },
  xaxis: {
    categories: props.expensesByCategory.map(e => e.category),
    labels: { formatter: v => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v), style: { fontSize: '11px' } },
  },
  yaxis: { labels: { style: { fontSize: '11px', colors: '#374151' } } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#F3F4F6' },
  tooltip: { x: { show: true }, y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' XOF' } },
}))

const expChartSeries = computed(() => [{
  name: 'Dépenses',
  data: props.expensesByCategory.map(e => (float => float)(e.total)),
}])
</script>

<template>
  <AppLayout title="Rapports">
    <Head title="Rapports" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-chart-bar" style="color:#06B6D4"></i> Rapports &amp; Statistiques</h1>
    </div>

    <!-- Period filters -->
    <div class="filters-bar">
      <div class="period-buttons">
        <button v-for="p in ['today','week','month','year','custom']" :key="p"
          @click="period = p"
          :class="['period-btn', { active: period === p }]">
          {{ PERIOD_LABELS[p] }}
        </button>
      </div>
      <template v-if="period === 'custom'">
        <div class="field-inline">
          <label>Du</label>
          <input v-model="dateFrom" type="date" />
        </div>
        <div class="field-inline">
          <label>Au</label>
          <input v-model="dateTo" type="date" />
        </div>
      </template>
      <button @click="applyFilters" class="btn-apply">
        <i class="fa-solid fa-filter"></i> Appliquer
      </button>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card" style="border-top-color:#10B981">
        <div class="kpi-label"><i class="fa-solid fa-arrow-trend-up" style="color:#10B981"></i> Ventes totales</div>
        <div class="kpi-value text-green">{{ fmt(kpis.total_sales) }}</div>
        <div class="kpi-sub">{{ fmtN(kpis.total_orders) }} commandes</div>
      </div>
      <div class="kpi-card" style="border-top-color:#EF4444">
        <div class="kpi-label"><i class="fa-solid fa-arrow-trend-down" style="color:#EF4444"></i> Dépenses totales</div>
        <div class="kpi-value text-red">{{ fmt(kpis.total_expenses) }}</div>
      </div>
      <div class="kpi-card" style="border-top-color:#8B5CF6">
        <div class="kpi-label"><i class="fa-solid fa-sack-dollar" style="color:#8B5CF6"></i> Bénéfice net</div>
        <div class="kpi-value" :class="kpis.net_profit >= 0 ? 'text-green' : 'text-red'">
          {{ fmt(kpis.net_profit) }}
        </div>
      </div>
      <div class="kpi-card" style="border-top-color:#3B82F6">
        <div class="kpi-label"><i class="fa-solid fa-basket-shopping" style="color:#3B82F6"></i> Panier moyen</div>
        <div class="kpi-value text-blue">{{ fmt(kpis.avg_basket) }}</div>
      </div>
    </div>

    <!-- Trend chart (full width) -->
    <div class="chart-card full-width mb-20">
      <h3 class="chart-title">
        <i class="fa-solid fa-chart-column" style="color:#06B6D4"></i>
        Ventes vs Dépenses — {{ PERIOD_LABELS[filters?.period || 'month'] }}
      </h3>
      <apexchart v-if="chartLabels.length" type="bar" height="280"
        :options="trendChartOptions" :series="trendChartSeries" />
      <div v-else class="empty-chart">Pas de données pour cette période</div>
    </div>

    <!-- Row 2: payment pie + expenses by category -->
    <div class="charts-row mb-20">
      <div class="chart-card">
        <h3 class="chart-title">
          <i class="fa-solid fa-circle-half-stroke" style="color:#3B82F6"></i>
          Modes de paiement
        </h3>
        <apexchart v-if="salesByPayment.length" type="donut" height="240"
          :options="payChartOptions" :series="payChartSeries" />
        <div v-else class="empty-chart">Aucune vente</div>
        <!-- payment table -->
        <table v-if="salesByPayment.length" class="mini-table">
          <thead><tr><th>Mode</th><th>Nb</th><th>Montant</th></tr></thead>
          <tbody>
            <tr v-for="p in salesByPayment" :key="p.method">
              <td>{{ p.label }}</td>
              <td>{{ fmtN(p.count) }}</td>
              <td class="text-right">{{ fmt(p.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="chart-card">
        <h3 class="chart-title">
          <i class="fa-solid fa-tags" style="color:#F59E0B"></i>
          Dépenses par catégorie
        </h3>
        <apexchart v-if="expensesByCategory.length" type="bar" height="240"
          :options="expChartOptions" :series="expChartSeries" />
        <div v-else class="empty-chart">Aucune dépense</div>
      </div>
    </div>

    <!-- Row 3: top products + customer debts -->
    <div class="charts-row">
      <div class="chart-card">
        <h3 class="chart-title"><i class="fa-solid fa-medal" style="color:#F59E0B"></i> Top 10 produits</h3>
        <div v-if="!topProducts.length" class="empty-chart">Aucune vente</div>
        <table v-else class="mini-table">
          <thead><tr><th>#</th><th>Produit</th><th>Qté</th><th>CA</th></tr></thead>
          <tbody>
            <tr v-for="(p, i) in topProducts" :key="i">
              <td class="rank">{{ i + 1 }}</td>
              <td>{{ p.name }}</td>
              <td>{{ fmtN(p.qty) }}</td>
              <td class="text-right">{{ fmt(p.revenue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="chart-card">
        <h3 class="chart-title"><i class="fa-solid fa-clock" style="color:#EF4444"></i> Créances clients (top 10)</h3>
        <div v-if="!customerDebts.length" class="empty-chart">Aucune créance en cours</div>
        <table v-else class="mini-table">
          <thead><tr><th>Client</th><th>Téléphone</th><th>Solde dû</th></tr></thead>
          <tbody>
            <tr v-for="c in customerDebts" :key="c.id">
              <td>{{ c.name }}</td>
              <td>{{ c.phone }}</td>
              <td class="text-right text-red">{{ fmt(c.outstanding_balance) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title {
  font-size: 1.15rem; font-weight: 700; color: #111827;
  padding-left: 12px; border-left: 3px solid #06B6D4;
  display: flex; align-items: center; gap: 10px;
}

/* Filters */
.filters-bar {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  background: #fff; border: 1px solid #E5E7EB; padding: 12px 16px; margin-bottom: 24px;
}
.period-buttons { display: flex; gap: 4px; }
.period-btn {
  padding: 5px 12px; font-size: 0.78rem; font-weight: 500; border: 1px solid #E5E7EB;
  background: #fff; color: #374151; cursor: pointer; transition: all 0.15s;
}
.period-btn.active, .period-btn:hover { background: #06B6D4; color: #fff; border-color: #06B6D4; }
.field-inline { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #6B7280; }
.field-inline input { border: 1px solid #D1D5DB; padding: 5px 8px; font-size: 0.8rem; }
.btn-apply {
  margin-left: auto; padding: 6px 14px; background: #06B6D4; color: #fff;
  border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; gap: 6px;
}
.btn-apply:hover { background: #0891B2; }

/* KPIs */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
.kpi-card {
  background: #fff; border: 1px solid #E5E7EB; border-top: 3px solid #E5E7EB; padding: 16px 18px;
}
.kpi-label { font-size: 0.73rem; color: #6B7280; margin-bottom: 6px; display: flex; align-items: center; gap: 5px; }
.kpi-value { font-size: 1.2rem; font-weight: 800; color: #111827; }
.kpi-sub   { font-size: 0.72rem; color: #9CA3AF; margin-top: 4px; }

/* Charts */
.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.chart-card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.full-width { grid-column: 1 / -1; }
.mb-20 { margin-bottom: 20px; }
.chart-title {
  font-size: 0.82rem; font-weight: 700; color: #111827;
  margin-bottom: 14px; display: flex; align-items: center; gap: 7px;
}
.empty-chart {
  height: 120px; display: flex; align-items: center; justify-content: center;
  font-size: 0.82rem; color: #9CA3AF; font-style: italic;
}

/* Tables */
.mini-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; margin-top: 12px; }
.mini-table th {
  text-align: left; font-weight: 600; color: #6B7280; font-size: 0.71rem;
  text-transform: uppercase; padding: 6px 8px; border-bottom: 2px solid #E5E7EB;
}
.mini-table td { padding: 7px 8px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.mini-table tr:last-child td { border-bottom: none; }
.mini-table .text-right { text-align: right; }
.mini-table .rank { font-weight: 700; color: #9CA3AF; width: 24px; }

/* Colors */
.text-green { color: #10B981; }
.text-red   { color: #EF4444; }
.text-blue  { color: #3B82F6; }
</style>
