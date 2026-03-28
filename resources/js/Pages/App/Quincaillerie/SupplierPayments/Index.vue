<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ payments: Object, suppliers: Array, filters: Object })
const t = () => route().params._tenant

const supplierId = ref(props.filters?.supplier_id || '')
const dateFrom = ref(props.filters?.date_from || '')
const dateTo = ref(props.filters?.date_to || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.quincaillerie.supplier-payments.index', { _tenant: t() }), {
      supplier_id: supplierId.value || undefined,
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}
watch([supplierId, dateFrom, dateTo], applyFilters)

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const METHOD_LABELS = { cash: 'Espèces', wave: 'Wave', om: 'Orange Money', bank: 'Virement', other: 'Autre' }
</script>

<template>
  <AppLayout title="Paiements fournisseur">
    <Head title="Paiements fournisseur" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-money-check-dollar" style="color:#10B981"></i> Paiements fournisseur</h1>
      <Link :href="route('app.quincaillerie.supplier-payments.create', { _tenant: t() })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau paiement
      </Link>
    </div>

    <!-- Total dettes fournisseurs -->
    <div class="debt-cards">
      <div v-for="s in suppliers.filter(x => x.outstanding_balance > 0)" :key="s.id" class="debt-card">
        <div class="debt-name">{{ s.name }}</div>
        <div class="debt-amount">{{ fmt(s.outstanding_balance) }}</div>
      </div>
    </div>

    <div class="filters-bar">
      <select v-model="supplierId" class="filter-select">
        <option value="">Tous les fournisseurs</option>
        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
      <input v-model="dateFrom" type="date" class="filter-select" placeholder="Du" />
      <input v-model="dateTo" type="date" class="filter-select" placeholder="Au" />
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Date</th><th>Fournisseur</th><th>BC</th><th>Montant</th><th>Méthode</th><th>Réf.</th><th>Par</th></tr>
        </thead>
        <tbody>
          <tr v-for="p in payments.data" :key="p.id">
            <td>{{ fmtDt(p.date) }}</td>
            <td>{{ p.supplier?.name || '—' }}</td>
            <td>{{ p.purchase_order?.reference || '—' }}</td>
            <td class="font-medium" style="color:#10B981">{{ fmt(p.amount) }}</td>
            <td>{{ METHOD_LABELS[p.method] || p.method }}</td>
            <td>{{ p.reference || '—' }}</td>
            <td>{{ p.user?.name || '—' }}</td>
          </tr>
          <tr v-if="!payments.data.length">
            <td colspan="7" class="empty-row">Aucun paiement</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="payments.last_page > 1" class="pagination-wrap">
      <Link v-for="link in payments.links" :key="link.label"
            :href="link.url || '#'"
            class="pagination-link"
            :class="{ active: link.active, disabled: !link.url }"
            v-html="link.label" />
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #10B981}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.debt-cards{display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.debt-card{background:#FEF3C7;border:1px solid #F59E0B;padding:.5rem 1rem;min-width:150px}
.debt-name{font-size:.8rem;color:#92400E;font-weight:600}
.debt-amount{font-size:1.1rem;font-weight:700;color:#B45309}
.filters-bar{display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.filter-select{padding:.5rem .75rem;border:1px solid #D1D5DB;font-size:.875rem;min-width:140px}
.flash-success{background:#D1FAE5;color:#065F46;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.6rem .75rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.6rem .75rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.empty-row{text-align:center;color:#9CA3AF;padding:2rem}
.pagination-wrap{display:flex;gap:.25rem;margin-top:1rem;justify-content:center}
.pagination-link{padding:.35rem .65rem;border:1px solid #D1D5DB;font-size:.8rem;color:#374151}
.pagination-link.active{background:#2563EB;color:#fff;border-color:#2563EB}
.pagination-link.disabled{opacity:.4;pointer-events:none}
</style>
