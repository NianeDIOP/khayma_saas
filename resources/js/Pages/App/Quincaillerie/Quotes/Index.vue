<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ quotes: Object, filters: Object })
const t = () => route().params._tenant

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.quincaillerie.quotes.index', { _tenant: t() }), {
      search: search.value || undefined,
      status: status.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

watch([search, status], applyFilters)

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const STATUS_LABELS = { draft: 'Brouillon', sent: 'Envoyé', accepted: 'Accepté', rejected: 'Rejeté', converted: 'Converti' }
const STATUS_COLORS = { draft: '#6B7280', sent: '#3B82F6', accepted: '#10B981', rejected: '#EF4444', converted: '#8B5CF6' }
</script>

<template>
  <AppLayout title="Devis">
    <Head title="Devis" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-file-invoice" style="color:#3B82F6"></i> Devis</h1>
      <Link :href="route('app.quincaillerie.quotes.create', { _tenant: t() })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau devis
      </Link>
    </div>

    <div class="filters-bar">
      <div class="filter-input-wrap">
        <i class="fa-solid fa-search"></i>
        <input v-model="search" type="text" placeholder="Réf. ou client…" class="filter-input" />
      </div>
      <select v-model="status" class="filter-select">
        <option value="">Tous statuts</option>
        <option value="draft">Brouillon</option>
        <option value="sent">Envoyé</option>
        <option value="accepted">Accepté</option>
        <option value="rejected">Rejeté</option>
        <option value="converted">Converti</option>
      </select>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Réf.</th>
            <th>Client</th>
            <th>Total</th>
            <th>Statut</th>
            <th>Validité</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="q in quotes.data" :key="q.id">
            <td class="font-medium">{{ q.reference }}</td>
            <td>{{ q.customer?.name || '—' }}</td>
            <td class="font-medium">{{ fmt(q.total) }}</td>
            <td>
              <span class="badge" :style="{ background: STATUS_COLORS[q.status] }">{{ STATUS_LABELS[q.status] }}</span>
            </td>
            <td>{{ fmtDt(q.valid_until) }}</td>
            <td>{{ fmtDt(q.created_at) }}</td>
            <td>
              <Link :href="route('app.quincaillerie.quotes.show', { _tenant: t(), quote: q.id })" class="btn-sm">
                <i class="fa-solid fa-eye"></i>
              </Link>
            </td>
          </tr>
          <tr v-if="!quotes.data.length">
            <td colspan="7" class="empty-row">Aucun devis</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="quotes.last_page > 1" class="pagination-wrap">
      <Link v-for="link in quotes.links" :key="link.label"
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
.btn-primary:hover{background:#1D4ED8}
.filters-bar{display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.filter-input-wrap{position:relative;flex:1;min-width:200px}
.filter-input-wrap i{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:#9CA3AF}
.filter-input{width:100%;padding:.5rem .75rem .5rem 2.25rem;border:1px solid #D1D5DB;font-size:.875rem}
.filter-select{padding:.5rem .75rem;border:1px solid #D1D5DB;font-size:.875rem;min-width:140px}
.flash-success{background:#D1FAE5;color:#065F46;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.6rem .75rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.6rem .75rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.badge{color:#fff;padding:.15rem .5rem;font-size:.75rem;font-weight:600;display:inline-block}
.btn-sm{padding:.25rem .5rem;font-size:.8rem;color:#2563EB;border:1px solid #2563EB;background:none;cursor:pointer;display:inline-flex;align-items:center;gap:.25rem}
.btn-sm:hover{background:#EFF6FF}
.empty-row{text-align:center;color:#9CA3AF;padding:2rem}
.pagination-wrap{display:flex;gap:.25rem;margin-top:1rem;justify-content:center}
.pagination-link{padding:.35rem .65rem;border:1px solid #D1D5DB;font-size:.8rem;color:#374151}
.pagination-link.active{background:#2563EB;color:#fff;border-color:#2563EB}
.pagination-link.disabled{opacity:.4;pointer-events:none}
</style>
