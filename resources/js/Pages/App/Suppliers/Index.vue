<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  suppliers: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.suppliers.index', { _tenant: route().params._tenant }), {
      search: search.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch(search, applyFilters)

function destroy(id) {
  if (!confirm('Supprimer ce fournisseur ?')) return
  router.delete(route('app.suppliers.destroy', { supplier: id, _tenant: route().params._tenant }))
}
</script>

<template>
  <AppLayout title="Fournisseurs">
    <Head title="Fournisseurs" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-truck-field" style="color:#F59E0B"></i> Fournisseurs</h1>
      <Link :href="route('app.suppliers.create', { _tenant: route().params._tenant })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau fournisseur
      </Link>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="filter-input-wrap">
        <i class="fa-solid fa-search"></i>
        <input v-model="search" type="text" placeholder="Rechercher par nom, tél, email…" class="filter-input" />
      </div>
    </div>

    <!-- Flash -->
    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>NINEA</th>
            <th>Note</th>
            <th>Solde</th>
            <th style="width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="suppliers.data.length === 0">
            <td colspan="7" class="empty-row">Aucun fournisseur trouvé.</td>
          </tr>
          <tr v-for="s in suppliers.data" :key="s.id">
            <td class="cell-name">
              <Link :href="route('app.suppliers.show', { supplier: s.id, _tenant: route().params._tenant })" class="name-link">{{ s.name }}</Link>
            </td>
            <td>{{ s.phone }}</td>
            <td>{{ s.email || '—' }}</td>
            <td>{{ s.ninea || '—' }}</td>
            <td>
              <span v-if="s.rating" class="rating-badge">
                <i class="fa-solid fa-star"></i> {{ Number(s.rating).toFixed(1) }}
              </span>
              <span v-else class="text-muted">—</span>
            </td>
            <td :class="{ 'text-red': parseFloat(s.outstanding_balance) > 0 }">
              {{ Number(s.outstanding_balance).toLocaleString('fr-FR') }} F
            </td>
            <td class="actions-cell">
              <Link :href="route('app.suppliers.edit', { supplier: s.id, _tenant: route().params._tenant })" class="btn-icon" title="Modifier">
                <i class="fa-solid fa-pen"></i>
              </Link>
              <button @click="destroy(s.id)" class="btn-icon btn-danger" title="Supprimer">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="suppliers.last_page > 1" class="pagination">
      <template v-for="link in suppliers.links" :key="link.label">
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

.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }

.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.cell-name { font-weight: 600; }
.name-link { color: #D97706; text-decoration: none; }
.name-link:hover { text-decoration: underline; }
.rating-badge { font-size: 0.76rem; font-weight: 600; color: #F59E0B; }
.rating-badge i { font-size: 0.68rem; }
.text-muted { color: #D1D5DB; }
.text-red { color: #DC2626; font-weight: 600; }
.actions-cell { display: flex; gap: 6px; }
.btn-icon { background: none; border: 1px solid #E5E7EB; padding: 5px 8px; cursor: pointer; color: #6B7280; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; }
.btn-icon:hover { background: #F3F4F6; color: #111827; }
.btn-danger:hover { background: #FEE2E2; color: #DC2626; border-color: #FECACA; }

.pagination { display: flex; gap: 4px; margin-top: 16px; justify-content: center; }
.page-link { padding: 6px 12px; font-size: 0.78rem; border: 1px solid #E5E7EB; color: #374151; text-decoration: none; background: #fff; }
.page-link.active { background: #F59E0B; color: #fff; border-color: #F59E0B; }
.page-link.disabled { color: #D1D5DB; cursor: default; }
</style>
