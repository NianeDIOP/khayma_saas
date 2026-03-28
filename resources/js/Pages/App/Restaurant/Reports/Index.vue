<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  salesByService: Array,
  salesByType: Array,
  salesByPayment: Array,
  topDishes: Array,
  filters: Object,
  chartLabels: Array,
  chartValues: Array,
})
const t = () => route().params._tenant

const dateFrom = ref(props.filters?.date_from || new Date().toISOString().slice(0, 10))
const dateTo   = ref(props.filters?.date_to || new Date().toISOString().slice(0, 10))

function applyFilters() {
  router.get(route('app.restaurant.reports.index', { _tenant: t() }), {
    date_from: dateFrom.value,
    date_to: dateTo.value,
  }, { preserveState: true, replace: true })
}

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }

const TYPE_LABELS = { table: 'Sur table', takeaway: 'À emporter', delivery: 'Livraison' }
const PAY_LABELS = { cash: 'Cash', wave: 'Wave', om: 'Orange Money', card: 'Carte', other: 'Autre' }

const salesChartOpts = computed(() => ({
  chart: { type: 'area', height: 220, toolbar: { show: false } },
  colors: ['#8B5CF6'],
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
  <AppLayout title="Rapports Restaurant">
    <Head title="Rapports" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-chart-bar" style="color:#8B5CF6"></i> Rapports</h1>
    </div>

    <!-- Date filters -->
    <div class="filters-bar">
      <div class="field-inline">
        <label>Du</label>
        <input v-model="dateFrom" type="date" />
      </div>
      <div class="field-inline">
        <label>Au</label>
        <input v-model="dateTo" type="date" />
      </div>
      <button @click="applyFilters" class="btn-filter">
        <i class="fa-solid fa-filter"></i> Filtrer
      </button>
    </div>

    <!-- KPI cards -->
    <div class="kpi-grid">
      <div class="kpi-card" style="border-left-color:#10B981">
        <div class="kpi-label">Total ventes</div>
        <div class="kpi-value">{{ fmt(stats.total_sales) }}</div>
      </div>
      <div class="kpi-card" style="border-left-color:#3B82F6">
        <div class="kpi-label">Commandes</div>
        <div class="kpi-value">{{ stats.order_count }}</div>
      </div>
      <div class="kpi-card" style="border-left-color:#EF4444">
        <div class="kpi-label">Annulées</div>
        <div class="kpi-value">{{ stats.cancelled }}</div>
      </div>
      <div class="kpi-card" style="border-left-color:#F59E0B">
        <div class="kpi-label">Dépenses</div>
        <div class="kpi-value">{{ fmt(stats.total_expenses) }}</div>
      </div>
      <div class="kpi-card" style="border-left-color:#8B5CF6">
        <div class="kpi-label">Bénéfice net</div>
        <div class="kpi-value" :class="stats.net_profit >= 0 ? 'text-green' : 'text-red'">{{ fmt(stats.net_profit) }}</div>
      </div>
    </div>

    <!-- Sales Trend Chart -->
    <div class="report-card" style="margin-bottom:16px">
      <div class="card-title"><i class="fa-solid fa-chart-area" style="color:#8B5CF6"></i> Évolution des ventes</div>
      <apexchart v-if="chartLabels?.length" type="area" height="220"
        :options="salesChartOpts" :series="salesChartSeries" />
      <div v-else class="empty">Aucune donnée pour cette période</div>
    </div>

    <!-- Details grids -->
    <div class="triple-grid">
      <!-- By service -->
      <div class="report-card">
        <div class="card-title"><i class="fa-solid fa-clock" style="color:#F59E0B"></i> Par service</div>
        <div v-if="!salesByService?.length" class="empty">Aucune donnée</div>
        <div v-for="s in salesByService" :key="s.service" class="report-row">
          <span>{{ s.service }}</span>
          <span class="row-count">{{ s.count }} cmd</span>
          <span class="row-total">{{ fmt(s.total) }}</span>
        </div>
      </div>

      <!-- By type -->
      <div class="report-card">
        <div class="card-title"><i class="fa-solid fa-layer-group" style="color:#3B82F6"></i> Par type</div>
        <div v-if="!salesByType?.length" class="empty">Aucune donnée</div>
        <div v-for="s in salesByType" :key="s.type" class="report-row">
          <span>{{ TYPE_LABELS[s.type] || s.type }}</span>
          <span class="row-count">{{ s.count }} cmd</span>
          <span class="row-total">{{ fmt(s.total) }}</span>
        </div>
      </div>

      <!-- By payment -->
      <div class="report-card">
        <div class="card-title"><i class="fa-solid fa-wallet" style="color:#10B981"></i> Par paiement</div>
        <div v-if="!salesByPayment?.length" class="empty">Aucune donnée</div>
        <div v-for="s in salesByPayment" :key="s.payment_method" class="report-row">
          <span>{{ PAY_LABELS[s.payment_method] || s.payment_method }}</span>
          <span class="row-count">{{ s.count }} cmd</span>
          <span class="row-total">{{ fmt(s.total) }}</span>
        </div>
      </div>
    </div>

    <!-- Top dishes -->
    <div class="report-card" style="margin-top:16px">
      <div class="card-title"><i class="fa-solid fa-ranking-star" style="color:#EF4444"></i> Top 10 plats vendus</div>
      <div class="table-wrap-inner">
        <table class="data-table">
          <thead>
            <tr><th>Plat</th><th>Quantité</th><th>Revenu</th></tr>
          </thead>
          <tbody>
            <tr v-if="!topDishes?.length">
              <td colspan="3" class="empty-row">Aucune donnée</td>
            </tr>
            <tr v-for="(d, i) in topDishes" :key="d.name">
              <td class="cell-name"><span class="rank">#{{ i + 1 }}</span> {{ d.name }}</td>
              <td>{{ d.qty }}</td>
              <td class="cell-total">{{ fmt(d.revenue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #8B5CF6; }

.filters-bar { display: flex; gap: 12px; align-items: end; margin-bottom: 20px; }
.field-inline { display: flex; flex-direction: column; gap: 4px; }
.field-inline label { font-size: 0.75rem; font-weight: 600; color: #6B7280; }
.field-inline input { padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.btn-filter { background: #8B5CF6; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; height: fit-content; }

.kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }
.kpi-card { background: #fff; border: 1px solid #E5E7EB; border-left: 3px solid; padding: 16px; }
.kpi-label { font-size: 0.72rem; color: #6B7280; margin-bottom: 4px; }
.kpi-value { font-size: 1.2rem; font-weight: 800; color: #111827; }
.text-green { color: #10B981; }
.text-red { color: #EF4444; }

.triple-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.report-card { background: #fff; border: 1px solid #E5E7EB; padding: 18px; }
.card-title { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #F3F4F6; display: flex; align-items: center; gap: 8px; }
.report-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #F9FAFB; font-size: 0.82rem; }
.row-count { color: #6B7280; font-size: 0.75rem; }
.row-total { font-weight: 700; color: #111827; }
.empty { text-align: center; color: #9CA3AF; padding: 16px; font-size: 0.82rem; }

.table-wrap-inner { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 8px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.data-table td { padding: 8px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; }
.cell-name { font-weight: 600; }
.cell-total { font-weight: 700; }
.rank { color: #8B5CF6; font-weight: 800; margin-right: 6px; }

@media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } .triple-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
