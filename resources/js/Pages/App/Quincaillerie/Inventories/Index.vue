<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ inventories: Object, depots: Array, filters: Object })
const t = () => route().params._tenant

const status = ref(props.filters?.status || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.quincaillerie.inventories.index', { _tenant: t() }), {
      status: status.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}
watch([status], applyFilters)

function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const STATUS_LABELS = { in_progress: 'En cours', validated: 'Validé' }
const STATUS_COLORS = { in_progress: '#F59E0B', validated: '#10B981' }
</script>

<template>
  <AppLayout title="Inventaires">
    <Head title="Inventaires" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-clipboard-check" style="color:#8B5CF6"></i> Inventaires</h1>
      <Link :href="route('app.quincaillerie.inventories.create', { _tenant: t() })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouvel inventaire
      </Link>
    </div>

    <div class="filters-bar">
      <select v-model="status" class="filter-select">
        <option value="">Tous statuts</option>
        <option value="in_progress">En cours</option>
        <option value="validated">Validé</option>
      </select>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Réf.</th><th>Dépôt</th><th>Statut</th><th>Créé par</th><th>Date</th><th>Validé le</th><th></th></tr>
        </thead>
        <tbody>
          <tr v-for="inv in inventories.data" :key="inv.id">
            <td class="font-medium">{{ inv.reference }}</td>
            <td>{{ inv.depot?.name || '—' }}</td>
            <td><span class="badge" :style="{ background: STATUS_COLORS[inv.status] }">{{ STATUS_LABELS[inv.status] }}</span></td>
            <td>{{ inv.user?.name || '—' }}</td>
            <td>{{ fmtDt(inv.created_at) }}</td>
            <td>{{ fmtDt(inv.validated_at) }}</td>
            <td>
              <Link :href="route('app.quincaillerie.inventories.show', { _tenant: t(), inventory: inv.id })" class="btn-sm">
                <i class="fa-solid fa-eye"></i>
              </Link>
            </td>
          </tr>
          <tr v-if="!inventories.data.length">
            <td colspan="7" class="empty-row">Aucun inventaire</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="inventories.last_page > 1" class="pagination-wrap">
      <Link v-for="link in inventories.links" :key="link.label"
            :href="link.url || '#'"
            class="pagination-link"
            :class="{ active: link.active, disabled: !link.url }"
            v-html="link.label" />
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #8B5CF6}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.filters-bar{display:flex;gap:.75rem;margin-bottom:1rem}
.filter-select{padding:.5rem .75rem;border:1px solid #D1D5DB;font-size:.875rem;min-width:140px}
.flash-success{background:#D1FAE5;color:#065F46;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.6rem .75rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.6rem .75rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.badge{color:#fff;padding:.15rem .5rem;font-size:.75rem;font-weight:600;display:inline-block}
.btn-sm{padding:.25rem .5rem;font-size:.8rem;color:#2563EB;border:1px solid #2563EB;background:none;cursor:pointer;display:inline-flex;align-items:center;gap:.25rem}
.empty-row{text-align:center;color:#9CA3AF;padding:2rem}
.pagination-wrap{display:flex;gap:.25rem;margin-top:1rem;justify-content:center}
.pagination-link{padding:.35rem .65rem;border:1px solid #D1D5DB;font-size:.8rem;color:#374151}
.pagination-link.active{background:#2563EB;color:#fff;border-color:#2563EB}
.pagination-link.disabled{opacity:.4;pointer-events:none}
</style>
