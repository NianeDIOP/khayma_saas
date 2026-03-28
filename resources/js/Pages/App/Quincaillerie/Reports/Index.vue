<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  topProducts: Array,
  salesByType: Array,
  salesByPayment: Array,
  stockMovements: Array,
  filters: Object,
  chartLabels: Array,
  chartValues: Array,
})
const t = () => route().params._tenant

const dateFrom = ref(props.filters?.date_from || '')
const dateTo = ref(props.filters?.date_to || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.quincaillerie.reports.index', { _tenant: t() }), {
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true })
  }, 400)
}
watch([dateFrom, dateTo], applyFilters)

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtN(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) }

const TYPE_LABELS = { counter: 'Comptoir', delivery: 'Livraison', table: 'Table', takeaway: 'Emporter' }
const MOVEMENT_LABELS = { purchase: 'Achat', sale: 'Vente', adjustment: 'Ajustement', return_supplier: 'Retour fourn.', loss: 'Perte' }

const salesChartOpts = computed(() => ({
  chart: { type: 'area', height: 220, toolbar: { show: false } },
  colors: ['#6366F1'],
  stroke: { curve: 'smooth', width: 2 },
  fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0 } },
  dataLabels: { enabled: false },
  xaxis: { categories: props.chartLabels || [] },
  yaxis: { labels: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) } },
  tooltip: { y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' XOF' } },
  grid: { borderColor: '#F3F4F6' },
}))
const salesChartSeries = computed(() => [{ name: 'Ventes', data: props.chartValues || [] }])
</script>

<template>
  <AppLayout title="Rapports Quincaillerie">
    <Head title="Rapports Quincaillerie" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-chart-bar" style="color:#6366F1"></i> Rapports</h1>
    </div>

    <div class="filters-bar">
      <div class="filter-group">
        <label>Du</label>
        <input v-model="dateFrom" type="date" class="filter-select" />
      </div>
      <div class="filter-group">
        <label>Au</label>
        <input v-model="dateTo" type="date" class="filter-select" />
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon" style="color:#10B981"><i class="fa-solid fa-coins"></i></div>
        <div class="kpi-value">{{ fmt(stats.total_sales) }}</div>
        <div class="kpi-label">Total ventes</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon" style="color:#3B82F6"><i class="fa-solid fa-receipt"></i></div>
        <div class="kpi-value">{{ fmtN(stats.sales_count) }}</div>
        <div class="kpi-label">Nombre de ventes</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon" style="color:#8B5CF6"><i class="fa-solid fa-calculator"></i></div>
        <div class="kpi-value">{{ fmt(stats.avg_sale) }}</div>
        <div class="kpi-label">Panier moyen</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon" style="color:#EF4444"><i class="fa-solid fa-hand-holding-dollar"></i></div>
        <div class="kpi-value">{{ fmt(stats.client_debt_remaining) }}</div>
        <div class="kpi-label">Dettes clients</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon" style="color:#F59E0B"><i class="fa-solid fa-truck"></i></div>
        <div class="kpi-value">{{ fmt(stats.supplier_debts) }}</div>
        <div class="kpi-label">Dettes fournisseurs</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon" style="color:#6366F1"><i class="fa-solid fa-file-invoice"></i></div>
        <div class="kpi-value">{{ fmtN(stats.pending_quotes) }} ({{ fmt(stats.pending_quotes_total) }})</div>
        <div class="kpi-label">Devis en attente</div>
      </div>
    </div>

    <!-- Sales Trend Chart -->
    <div class="report-card" style="margin-bottom:1.5rem">
      <div class="card-title"><i class="fa-solid fa-chart-area" style="color:#6366F1"></i> Évolution des ventes</div>
      <apexchart v-if="chartLabels?.length" type="area" height="220"
        :options="salesChartOpts" :series="salesChartSeries" />
      <div v-else class="empty">Aucune donnée pour cette période</div>
    </div>

    <!-- Top Products -->
    <div class="report-section">
      <h3 class="section-title">Top 10 produits</h3>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>#</th><th>Produit</th><th>Qté vendue</th><th>Revenu</th></tr></thead>
          <tbody>
            <tr v-for="(p, i) in topProducts" :key="p.product_id">
              <td>{{ i + 1 }}</td>
              <td>{{ p.product?.name || '—' }}</td>
              <td>{{ fmtN(p.total_qty) }}</td>
              <td class="font-medium">{{ fmt(p.total_revenue) }}</td>
            </tr>
            <tr v-if="!topProducts.length"><td colspan="4" class="empty-row">Aucune donnée</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Sales by Type -->
    <div class="report-grid">
      <div class="report-section">
        <h3 class="section-title">Ventes par type</h3>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Type</th><th>Nombre</th><th>Total</th></tr></thead>
            <tbody>
              <tr v-for="s in salesByType" :key="s.type">
                <td>{{ TYPE_LABELS[s.type] || s.type }}</td>
                <td>{{ fmtN(s.count) }}</td>
                <td class="font-medium">{{ fmt(s.total) }}</td>
              </tr>
              <tr v-if="!salesByType.length"><td colspan="3" class="empty-row">—</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="report-section">
        <h3 class="section-title">Paiements</h3>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Méthode</th><th>Nombre</th><th>Total</th></tr></thead>
            <tbody>
              <tr v-for="s in salesByPayment" :key="s.method">
                <td>{{ s.method }}</td>
                <td>{{ fmtN(s.count) }}</td>
                <td class="font-medium">{{ fmt(s.total) }}</td>
              </tr>
              <tr v-if="!salesByPayment.length"><td colspan="3" class="empty-row">—</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Stock Movements -->
    <div class="report-section">
      <h3 class="section-title">Mouvements de stock</h3>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Type</th><th>Nombre</th><th>Qté totale</th></tr></thead>
          <tbody>
            <tr v-for="m in stockMovements" :key="m.type">
              <td>{{ MOVEMENT_LABELS[m.type] || m.type }}</td>
              <td>{{ fmtN(m.count) }}</td>
              <td class="font-medium">{{ fmtN(m.total_qty) }}</td>
            </tr>
            <tr v-if="!stockMovements.length"><td colspan="3" class="empty-row">—</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #6366F1}
.filters-bar{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
.filter-group{display:flex;flex-direction:column;gap:.2rem}
.filter-group label{font-size:.75rem;font-weight:600;color:#6B7280}
.filter-select{padding:.5rem .75rem;border:1px solid #D1D5DB;font-size:.875rem}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem}
.kpi-card{background:#fff;border:1px solid #E5E7EB;padding:1rem;display:flex;flex-direction:column;align-items:center;text-align:center}
.kpi-icon{font-size:1.5rem;margin-bottom:.5rem}
.kpi-value{font-size:1.15rem;font-weight:700;color:#111}
.kpi-label{font-size:.75rem;color:#6B7280;margin-top:.2rem}
.report-section{margin-bottom:2rem}
.report-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem}
.section-title{font-size:1rem;font-weight:600;margin-bottom:.75rem;color:#111}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.5rem .6rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.5rem .6rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.empty-row{text-align:center;color:#9CA3AF;padding:1.5rem}
</style>
