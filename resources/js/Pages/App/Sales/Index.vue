<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ sales: Object, filters: Object })

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.sales.index', { _tenant: route().params._tenant }), {
      search: search.value || undefined,
      status: status.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch([search, status], applyFilters)

const statusLabels = { completed: 'Terminée', cancelled: 'Annulée', refunded: 'Remboursée' }
const statusColors = { completed: '#10B981', cancelled: '#EF4444', refunded: '#F59E0B' }
const paymentLabels = { paid: 'Payé', partial: 'Partiel', unpaid: 'Impayé' }
const paymentColors = { paid: '#10B981', partial: '#F59E0B', unpaid: '#EF4444' }
</script>

<template>
  <AppLayout title="Ventes">
    <Head title="Ventes" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-cash-register" style="color:#F59E0B"></i> Ventes</h1>
      <Link :href="route('app.sales.create', { _tenant: route().params._tenant })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouvelle vente
      </Link>
    </div>

    <div class="filters-bar">
      <div class="filter-input-wrap">
        <i class="fa-solid fa-search"></i>
        <input v-model="search" type="text" placeholder="Rechercher par référence, client…" class="filter-input" />
      </div>
      <select v-model="status" class="filter-select">
        <option value="">Tous statuts</option>
        <option value="completed">Terminée</option>
        <option value="cancelled">Annulée</option>
      </select>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Référence</th>
            <th>Date</th>
            <th>Client</th>
            <th>Total</th>
            <th>Statut</th>
            <th>Paiement</th>
            <th style="width:80px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="sales.data.length === 0">
            <td colspan="7" class="empty-row">Aucune vente trouvée.</td>
          </tr>
          <tr v-for="s in sales.data" :key="s.id">
            <td class="cell-name">
              <Link :href="route('app.sales.show', { sale: s.id, _tenant: route().params._tenant })" class="name-link">{{ s.reference }}</Link>
            </td>
            <td>{{ new Date(s.created_at).toLocaleDateString('fr-FR') }}</td>
            <td>{{ s.customer?.name || 'Client de passage' }}</td>
            <td class="text-bold">{{ Number(s.total).toLocaleString('fr-FR') }} F</td>
            <td>
              <span class="status-badge" :style="{ color: statusColors[s.status], background: statusColors[s.status] + '18' }">
                {{ statusLabels[s.status] || s.status }}
              </span>
            </td>
            <td>
              <span class="status-badge" :style="{ color: paymentColors[s.payment_status], background: paymentColors[s.payment_status] + '18' }">
                {{ paymentLabels[s.payment_status] || s.payment_status }}
              </span>
            </td>
            <td>
              <Link :href="route('app.sales.show', { sale: s.id, _tenant: route().params._tenant })" class="btn-icon" title="Voir">
                <i class="fa-solid fa-eye"></i>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="sales.last_page > 1" class="pagination">
      <template v-for="link in sales.links" :key="link.label">
        <Link v-if="link.url" :href="link.url" class="page-link" :class="{ active: link.active }" v-html="link.label" />
        <span v-else class="page-link disabled" v-html="link.label" />
      </template>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #F59E0B; }
.btn-primary { background: #F59E0B; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-primary:hover { background: #D97706; }
.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; }
.filter-input-wrap { position: relative; flex: 1; max-width: 360px; }
.filter-input-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 0.8rem; }
.filter-input { width: 100%; padding: 8px 12px 8px 32px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.filter-input:focus { border-color: #F59E0B; box-shadow: 0 0 0 2px rgba(245,158,11,0.12); }
.filter-select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; min-width: 160px; }
.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.cell-name { font-weight: 600; }
.name-link { color: #F59E0B; text-decoration: none; }
.name-link:hover { text-decoration: underline; }
.text-bold { font-weight: 700; }
.status-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; text-transform: uppercase; }
.btn-icon { background: none; border: 1px solid #E5E7EB; padding: 5px 8px; cursor: pointer; color: #6B7280; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; }
.btn-icon:hover { background: #F3F4F6; color: #111827; }
.pagination { display: flex; gap: 4px; margin-top: 16px; justify-content: center; }
.page-link { padding: 6px 12px; font-size: 0.78rem; border: 1px solid #E5E7EB; color: #374151; text-decoration: none; background: #fff; }
.page-link.active { background: #F59E0B; color: #fff; border-color: #F59E0B; }
.page-link.disabled { color: #D1D5DB; cursor: default; }
</style>
