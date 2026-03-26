<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  customers: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const category = ref(props.filters?.category || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.customers.index', { _tenant: route().params._tenant }), {
      search: search.value || undefined,
      category: category.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch([search, category], applyFilters)

function destroy(id) {
  if (!confirm('Supprimer ce client ?')) return
  router.delete(route('app.customers.destroy', { customer: id, _tenant: route().params._tenant }))
}

const categoryLabels = { normal: 'Normal', vip: 'VIP', professional: 'Pro' }
const categoryColors = { normal: '#6B7280', vip: '#F59E0B', professional: '#3B82F6' }
</script>

<template>
  <AppLayout title="Clients">
    <Head title="Clients" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-user-group" style="color:#2563EB"></i> Clients</h1>
      <Link :href="route('app.customers.create', { _tenant: route().params._tenant })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau client
      </Link>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="filter-input-wrap">
        <i class="fa-solid fa-search"></i>
        <input v-model="search" type="text" placeholder="Rechercher par nom, tél, email…" class="filter-input" />
      </div>
      <select v-model="category" class="filter-select">
        <option value="">Toutes catégories</option>
        <option value="normal">Normal</option>
        <option value="vip">VIP</option>
        <option value="professional">Professionnel</option>
      </select>
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
            <th>Catégorie</th>
            <th>Solde</th>
            <th style="width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="customers.data.length === 0">
            <td colspan="6" class="empty-row">Aucun client trouvé.</td>
          </tr>
          <tr v-for="c in customers.data" :key="c.id">
            <td class="cell-name">
              <Link :href="route('app.customers.show', { customer: c.id, _tenant: route().params._tenant })" class="name-link">{{ c.name }}</Link>
            </td>
            <td>{{ c.phone }}</td>
            <td>{{ c.email || '—' }}</td>
            <td>
              <span class="cat-badge" :style="{ color: categoryColors[c.category], background: categoryColors[c.category] + '18' }">
                {{ categoryLabels[c.category] || c.category }}
              </span>
            </td>
            <td :class="{ 'text-red': parseFloat(c.outstanding_balance) > 0 }">
              {{ Number(c.outstanding_balance).toLocaleString('fr-FR') }} F
            </td>
            <td class="actions-cell">
              <Link :href="route('app.customers.edit', { customer: c.id, _tenant: route().params._tenant })" class="btn-icon" title="Modifier">
                <i class="fa-solid fa-pen"></i>
              </Link>
              <button @click="destroy(c.id)" class="btn-icon btn-danger" title="Supprimer">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="customers.last_page > 1" class="pagination">
      <template v-for="link in customers.links" :key="link.label">
        <Link v-if="link.url" :href="link.url" class="page-link" :class="{ active: link.active }" v-html="link.label" />
        <span v-else class="page-link disabled" v-html="link.label" />
      </template>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #2563EB; }
.btn-primary { background: #2563EB; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-primary:hover { background: #1D4ED8; }

.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; }
.filter-input-wrap { position: relative; flex: 1; max-width: 360px; }
.filter-input-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 0.8rem; }
.filter-input { width: 100%; padding: 8px 12px 8px 32px; border: 1px solid #D1D5DB; font-size: 0.82rem; outline: none; }
.filter-input:focus { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37,99,235,0.12); }
.filter-select { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; min-width: 160px; }

.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }

.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.cell-name { font-weight: 600; }
.name-link { color: #2563EB; text-decoration: none; }
.name-link:hover { text-decoration: underline; }
.cat-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; text-transform: uppercase; letter-spacing: 0.03em; }
.text-red { color: #DC2626; font-weight: 600; }
.actions-cell { display: flex; gap: 6px; }
.btn-icon { background: none; border: 1px solid #E5E7EB; padding: 5px 8px; cursor: pointer; color: #6B7280; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; }
.btn-icon:hover { background: #F3F4F6; color: #111827; }
.btn-danger:hover { background: #FEE2E2; color: #DC2626; border-color: #FECACA; }

.pagination { display: flex; gap: 4px; margin-top: 16px; justify-content: center; }
.page-link { padding: 6px 12px; font-size: 0.78rem; border: 1px solid #E5E7EB; color: #374151; text-decoration: none; background: #fff; }
.page-link.active { background: #2563EB; color: #fff; border-color: #2563EB; }
.page-link.disabled { color: #D1D5DB; cursor: default; }
</style>
