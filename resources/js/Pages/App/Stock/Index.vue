<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ stockItems: Object, depots: Array, filters: Object })

const search = ref(props.filters?.search || '')
const depotId = ref(props.filters?.depot_id || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.stock.index', { _tenant: route().params._tenant }), {
      search: search.value || undefined,
      depot_id: depotId.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch([search, depotId], applyFilters)
</script>

<template>
  <AppLayout title="Stock">
    <Head title="Stock" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-boxes-stacked" style="color:#EF4444"></i> État du stock</h1>
      <div class="header-actions">
        <Link :href="route('app.stock.movements', { _tenant: route().params._tenant })" class="btn-secondary">
          <i class="fa-solid fa-right-left"></i> Mouvements
        </Link>
        <Link :href="route('app.stock.create-movement', { _tenant: route().params._tenant })" class="btn-primary">
          <i class="fa-solid fa-plus"></i> Nouveau mouvement
        </Link>
      </div>
    </div>

    <div class="filters-bar">
      <div class="filter-input-wrap">
        <i class="fa-solid fa-search"></i>
        <input v-model="search" type="text" placeholder="Rechercher produit…" class="filter-input" />
      </div>
      <select v-model="depotId" class="filter-select">
        <option value="">Tous les dépôts</option>
        <option v-for="d in depots" :key="d.id" :value="d.id">{{ d.name }}</option>
      </select>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Code-barres</th>
            <th>Dépôt</th>
            <th>Quantité</th>
            <th>Seuil alerte</th>
            <th>Valeur stock</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="stockItems.data.length === 0">
            <td colspan="6" class="empty-row">Aucun stock trouvé.</td>
          </tr>
          <tr v-for="si in stockItems.data" :key="si.id" :class="{ 'low-stock': parseFloat(si.quantity) <= si.min_stock_alert && si.min_stock_alert > 0 }">
            <td class="cell-name">{{ si.product_name }}</td>
            <td>{{ si.barcode || '—' }}</td>
            <td>{{ si.depot_name }}</td>
            <td :class="{ 'text-red': parseFloat(si.quantity) <= si.min_stock_alert && si.min_stock_alert > 0 }">
              {{ Number(si.quantity).toLocaleString('fr-FR') }}
            </td>
            <td>{{ si.min_stock_alert }}</td>
            <td>{{ Number(si.quantity * si.selling_price).toLocaleString('fr-FR') }} F</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="stockItems.last_page > 1" class="pagination">
      <template v-for="link in stockItems.links" :key="link.label">
        <Link v-if="link.url" :href="link.url" class="page-link" :class="{ active: link.active }" v-html="link.label" />
        <span v-else class="page-link disabled" v-html="link.label" />
      </template>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #EF4444; }
.header-actions { display: flex; gap: 8px; }
.btn-primary { background: #EF4444; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-primary:hover { background: #DC2626; }
.btn-secondary { background: #fff; color: #374151; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: 1px solid #D1D5DB; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-secondary:hover { background: #F9FAFB; }
.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; }
.filter-input-wrap { position: relative; flex: 1; max-width: 360px; }
.filter-input-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 0.8rem; }
.filter-input { width: 100%; padding: 8px 12px 8px 32px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.filter-input:focus { border-color: #EF4444; box-shadow: 0 0 0 2px rgba(239,68,68,0.12); }
.filter-select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; min-width: 160px; }
.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.cell-name { font-weight: 600; }
.low-stock { background: #FEF2F2; }
.text-red { color: #DC2626; font-weight: 600; }
.pagination { display: flex; gap: 4px; margin-top: 16px; justify-content: center; }
.page-link { padding: 6px 12px; font-size: 0.78rem; border: 1px solid #E5E7EB; color: #374151; text-decoration: none; background: #fff; }
.page-link.active { background: #EF4444; color: #fff; border-color: #EF4444; }
.page-link.disabled { color: #D1D5DB; cursor: default; }
</style>
