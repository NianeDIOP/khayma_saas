<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ returns: Object, suppliers: Array, filters: Object })
const t = () => route().params._tenant

const supplierId = ref(props.filters?.supplier_id || '')
const status = ref(props.filters?.status || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.quincaillerie.supplier-returns.index', { _tenant: t() }), {
      supplier_id: supplierId.value || undefined,
      status: status.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}
watch([supplierId, status], applyFilters)

function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const STATUS_LABELS = { pending: 'En attente', accepted: 'Accepté', refunded: 'Remboursé' }
const STATUS_COLORS = { pending: '#F59E0B', accepted: '#10B981', refunded: '#3B82F6' }
</script>

<template>
  <AppLayout title="Retours fournisseur">
    <Head title="Retours fournisseur" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-rotate-left" style="color:#EF4444"></i> Retours fournisseur</h1>
      <Link :href="route('app.quincaillerie.supplier-returns.create', { _tenant: t() })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau retour
      </Link>
    </div>

    <div class="filters-bar">
      <select v-model="supplierId" class="filter-select">
        <option value="">Tous les fournisseurs</option>
        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
      <select v-model="status" class="filter-select">
        <option value="">Tous statuts</option>
        <option value="pending">En attente</option>
        <option value="accepted">Accepté</option>
        <option value="refunded">Remboursé</option>
      </select>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Date</th><th>Fournisseur</th><th>Produit</th><th>Qté</th><th>BC</th><th>Statut</th><th>Raison</th></tr>
        </thead>
        <tbody>
          <tr v-for="r in returns.data" :key="r.id">
            <td>{{ fmtDt(r.date) }}</td>
            <td>{{ r.supplier?.name || '—' }}</td>
            <td>{{ r.product?.name || '—' }}</td>
            <td class="font-medium">{{ r.quantity }}</td>
            <td>{{ r.purchase_order?.reference || '—' }}</td>
            <td><span class="badge" :style="{ background: STATUS_COLORS[r.status] }">{{ STATUS_LABELS[r.status] }}</span></td>
            <td class="truncate">{{ r.reason || '—' }}</td>
          </tr>
          <tr v-if="!returns.data.length">
            <td colspan="7" class="empty-row">Aucun retour</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="returns.last_page > 1" class="pagination-wrap">
      <Link v-for="link in returns.links" :key="link.label"
            :href="link.url || '#'"
            class="pagination-link"
            :class="{ active: link.active, disabled: !link.url }"
            v-html="link.label" />
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.5rem}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.filters-bar{display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.filter-select{padding:.5rem .75rem;border:1px solid #D1D5DB;font-size:.875rem;min-width:140px}
.flash-success{background:#D1FAE5;color:#065F46;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.6rem .75rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.6rem .75rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.badge{color:#fff;padding:.15rem .5rem;font-size:.75rem;font-weight:600;display:inline-block}
.truncate{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.empty-row{text-align:center;color:#9CA3AF;padding:2rem}
.pagination-wrap{display:flex;gap:.25rem;margin-top:1rem;justify-content:center}
.pagination-link{padding:.35rem .65rem;border:1px solid #D1D5DB;font-size:.8rem;color:#374151}
.pagination-link.active{background:#2563EB;color:#fff;border-color:#2563EB}
.pagination-link.disabled{opacity:.4;pointer-events:none}
</style>
