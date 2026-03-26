<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ dishes: Object, categories: Array, filters: Object })
const t = () => route().params._tenant

const search = ref(props.filters?.search || '')
const categoryId = ref(props.filters?.category_id || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.restaurant.dishes.index', { _tenant: t() }), {
      search: search.value || undefined,
      category_id: categoryId.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch([search, categoryId], applyFilters)

function destroy(id) {
  if (!confirm('Supprimer ce plat ?')) return
  router.delete(route('app.restaurant.dishes.destroy', { dish: id, _tenant: t() }))
}

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
</script>

<template>
  <AppLayout title="Plats">
    <Head title="Plats" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-utensils" style="color:#EF4444"></i> Plats</h1>
      <Link :href="route('app.restaurant.dishes.create', { _tenant: t() })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau plat
      </Link>
    </div>

    <div class="filters-bar">
      <div class="filter-input-wrap">
        <i class="fa-solid fa-search"></i>
        <input v-model="search" type="text" placeholder="Rechercher un plat…" class="filter-input" />
      </div>
      <select v-model="categoryId" class="filter-select">
        <option value="">Toutes les catégories</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Plat</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Promo</th>
            <th>Services</th>
            <th>Statut</th>
            <th style="width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="dishes.data.length === 0">
            <td colspan="7" class="empty-row">Aucun plat trouvé.</td>
          </tr>
          <tr v-for="d in dishes.data" :key="d.id">
            <td class="cell-name">
              {{ d.name }}
              <span v-if="d.is_additional" class="extra-badge">Extra</span>
            </td>
            <td>{{ d.category?.name || '—' }}</td>
            <td>{{ fmt(d.price) }}</td>
            <td>
              <template v-if="d.promo_price">{{ fmt(d.promo_price) }}</template>
              <template v-else>—</template>
            </td>
            <td class="services-cell">
              <span v-if="d.available_morning" class="svc-chip">Matin</span>
              <span v-if="d.available_noon" class="svc-chip">Midi</span>
              <span v-if="d.available_evening" class="svc-chip">Soir</span>
            </td>
            <td><span class="status-badge" :class="d.is_available ? 'active' : 'inactive'">{{ d.is_available ? 'Dispo' : 'Indispo' }}</span></td>
            <td class="actions-cell">
              <Link :href="route('app.restaurant.dishes.edit', { dish: d.id, _tenant: t() })" class="btn-icon" title="Modifier">
                <i class="fa-solid fa-pen"></i>
              </Link>
              <button @click="destroy(d.id)" class="btn-icon btn-danger" title="Supprimer">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="dishes.last_page > 1" class="pagination">
      <template v-for="link in dishes.links" :key="link.label">
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
.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; }
.filter-input-wrap { position: relative; flex: 1; max-width: 320px; }
.filter-input-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 0.8rem; }
.filter-input { width: 100%; padding: 8px 12px 8px 32px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.filter-input:focus { border-color: #EF4444; }
.filter-select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; min-width: 180px; }
.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.cell-name { font-weight: 600; }
.extra-badge { font-size: 0.68rem; font-weight: 600; padding: 1px 6px; background: #FEF3C7; color: #D97706; margin-left: 6px; }
.services-cell { display: flex; gap: 4px; }
.svc-chip { font-size: 0.68rem; font-weight: 600; padding: 2px 6px; background: #EFF6FF; color: #3B82F6; }
.status-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; }
.status-badge.active { background: #D1FAE522; color: #10B981; }
.status-badge.inactive { background: #FEE2E222; color: #EF4444; }
.actions-cell { display: flex; gap: 6px; }
.btn-icon { background: none; border: 1px solid #E5E7EB; padding: 5px 8px; cursor: pointer; color: #6B7280; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; }
.btn-icon:hover { background: #F3F4F6; color: #111827; }
.btn-danger:hover { background: #FEE2E2; color: #DC2626; border-color: #FECACA; }
.pagination { display: flex; gap: 4px; margin-top: 16px; justify-content: center; }
.page-link { padding: 6px 12px; font-size: 0.78rem; border: 1px solid #E5E7EB; color: #374151; text-decoration: none; background: #fff; }
.page-link.active { background: #EF4444; color: #fff; border-color: #EF4444; }
.page-link.disabled { color: #D1D5DB; cursor: default; }
</style>
