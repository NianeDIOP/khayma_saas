<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ orders: Object, filters: Object })
const t = () => route().params._tenant

const search = ref(props.filters?.search || '')
const type = ref(props.filters?.type || '')
const status = ref(props.filters?.status || '')
const date = ref(props.filters?.date || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.restaurant.orders.index', { _tenant: t() }), {
      search: search.value || undefined,
      type: type.value || undefined,
      status: status.value || undefined,
      date: date.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch([search, type, status, date], applyFilters)

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) : '—' }

const TYPE_LABELS = { table: 'Table', takeaway: 'Emporter', delivery: 'Livraison' }
const TYPE_COLORS = { table: '#3B82F6', takeaway: '#F59E0B', delivery: '#8B5CF6' }
const STATUS_LABELS = { pending: 'En attente', completed: 'Terminée', cancelled: 'Annulée' }
const STATUS_COLORS = { pending: '#F59E0B', completed: '#10B981', cancelled: '#EF4444' }
</script>

<template>
  <AppLayout title="Commandes">
    <Head title="Commandes" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-clipboard-list" style="color:#EF4444"></i> Commandes</h1>
      <Link :href="route('app.restaurant.orders.create', { _tenant: t() })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouvelle commande
      </Link>
    </div>

    <div class="filters-bar">
      <div class="filter-input-wrap">
        <i class="fa-solid fa-search"></i>
        <input v-model="search" type="text" placeholder="Réf. ou nom client…" class="filter-input" />
      </div>
      <select v-model="type" class="filter-select">
        <option value="">Tous types</option>
        <option value="table">Sur table</option>
        <option value="takeaway">À emporter</option>
        <option value="delivery">Livraison</option>
      </select>
      <select v-model="status" class="filter-select">
        <option value="">Tous statuts</option>
        <option value="pending">En attente</option>
        <option value="completed">Terminée</option>
        <option value="cancelled">Annulée</option>
      </select>
      <input v-model="date" type="date" class="filter-select" />
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Réf.</th>
            <th>Type</th>
            <th>Client</th>
            <th>Articles</th>
            <th>Total</th>
            <th>Paiement</th>
            <th>Statut</th>
            <th>Date</th>
            <th style="width:80px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="orders.data.length === 0">
            <td colspan="9" class="empty-row">Aucune commande trouvée.</td>
          </tr>
          <tr v-for="o in orders.data" :key="o.id">
            <td class="cell-ref">{{ o.reference }}</td>
            <td><span class="type-badge" :style="{ color: TYPE_COLORS[o.type], background: TYPE_COLORS[o.type] + '18' }">{{ TYPE_LABELS[o.type] || o.type }}</span></td>
            <td>{{ o.customer_name || (o.type === 'table' ? 'Table ' + o.table_number : '—') }}</td>
            <td>{{ o.items?.length || 0 }}</td>
            <td class="cell-total">{{ fmt(o.total) }}</td>
            <td><span class="pay-badge" :class="o.payment_status">{{ o.payment_status === 'paid' ? 'Payé' : 'En attente' }}</span></td>
            <td><span class="status-badge" :style="{ color: STATUS_COLORS[o.status], background: STATUS_COLORS[o.status] + '18' }">{{ STATUS_LABELS[o.status] || o.status }}</span></td>
            <td>{{ fmtDt(o.created_at) }}</td>
            <td>
              <Link :href="route('app.restaurant.orders.show', { order: o.id, _tenant: t() })" class="btn-icon" title="Voir">
                <i class="fa-solid fa-eye"></i>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="orders.last_page > 1" class="pagination">
      <template v-for="link in orders.links" :key="link.label">
        <Link v-if="link.url" :href="link.url" class="page-link" :class="{ active: link.active }" v-html="link.label" />
        <span v-else class="page-link disabled" v-html="link.label" />
      </template>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #EF4444; }
.btn-primary { background: #EF4444; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.filters-bar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.filter-input-wrap { position: relative; flex: 1; max-width: 280px; }
.filter-input-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 0.8rem; }
.filter-input { width: 100%; padding: 8px 12px 8px 32px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.filter-select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 12px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.data-table td { padding: 10px 12px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.cell-ref { font-weight: 700; font-family: monospace; font-size: 0.78rem; }
.cell-total { font-weight: 700; }
.type-badge, .status-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; }
.pay-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; }
.pay-badge.paid { color: #10B981; background: #D1FAE522; }
.pay-badge.pending { color: #F59E0B; background: #FEF3C722; }
.btn-icon { background: none; border: 1px solid #E5E7EB; padding: 5px 8px; cursor: pointer; color: #6B7280; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; }
.btn-icon:hover { background: #F3F4F6; color: #111827; }
.pagination { display: flex; gap: 4px; margin-top: 16px; justify-content: center; }
.page-link { padding: 6px 12px; font-size: 0.78rem; border: 1px solid #E5E7EB; color: #374151; text-decoration: none; background: #fff; }
.page-link.active { background: #EF4444; color: #fff; border-color: #EF4444; }
.page-link.disabled { color: #D1D5DB; cursor: default; }
</style>
