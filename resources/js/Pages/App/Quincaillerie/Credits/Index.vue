<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ credits: Object, customerDebts: Array, customers: Array, filters: Object })
const t = () => route().params._tenant

const customerId = ref(props.filters?.customer_id || '')
let debounce = null

function applyFilters() {
  clearTimeout(debounce)
  debounce = setTimeout(() => {
    router.get(route('app.quincaillerie.credits.index', { _tenant: t() }), {
      customer_id: customerId.value || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}
watch([customerId], applyFilters)

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const showPayModal = ref(false)
const selectedSale = ref(null)
const payAmount = ref(0)
const payMethod = ref('cash')

function openPayModal(sale) {
  selectedSale.value = sale
  const paid = (sale.payments || []).reduce((s, p) => s + parseFloat(p.amount), 0)
  payAmount.value = Math.max(0, sale.total - paid)
  payMethod.value = 'cash'
  showPayModal.value = true
}

function submitPayment() {
  router.post(route('app.quincaillerie.credits.add-payment', { _tenant: t(), sale: selectedSale.value.id }), {
    amount: payAmount.value,
    method: payMethod.value,
  }, {
    onSuccess: () => { showPayModal.value = false }
  })
}

const PAYMENT_STATUS_LABELS = { paid: 'Payé', partial: 'Partiel', unpaid: 'Impayé' }
const PAYMENT_STATUS_COLORS = { paid: '#10B981', partial: '#F59E0B', unpaid: '#EF4444' }
</script>

<template>
  <AppLayout title="Crédits clients">
    <Head title="Crédits clients" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-hand-holding-dollar" style="color:#EF4444"></i> Crédits clients</h1>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <!-- Résumé dettes par client -->
    <div v-if="customerDebts?.length" class="debt-summary">
      <h3 class="section-title">Résumé des dettes</h3>
      <div class="debt-cards">
        <div v-for="cd in customerDebts" :key="cd.customer?.id" class="debt-card">
          <div class="debt-name">{{ cd.customer?.name || 'Client inconnu' }}</div>
          <div class="debt-phone" v-if="cd.customer?.phone">{{ cd.customer.phone }}</div>
          <div class="debt-amount">{{ fmt(cd.remaining) }}</div>
          <div class="debt-detail">Total: {{ fmt(cd.total_debt) }} — Payé: {{ fmt(cd.total_paid) }}</div>
        </div>
      </div>
    </div>

    <div class="filters-bar">
      <select v-model="customerId" class="filter-select">
        <option value="">Tous les clients</option>
        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <h3 class="section-title">Ventes en crédit</h3>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Réf.</th><th>Client</th><th>Total</th><th>Payé</th><th>Reste</th><th>Statut</th><th>Date</th><th></th></tr>
        </thead>
        <tbody>
          <tr v-for="s in credits.data" :key="s.id">
            <td class="font-medium">{{ s.reference }}</td>
            <td>{{ s.customer?.name || '—' }}</td>
            <td>{{ fmt(s.total) }}</td>
            <td style="color:#10B981">{{ fmt((s.payments || []).reduce((sum, p) => sum + parseFloat(p.amount), 0)) }}</td>
            <td style="color:#EF4444;font-weight:600">
              {{ fmt(s.total - (s.payments || []).reduce((sum, p) => sum + parseFloat(p.amount), 0)) }}
            </td>
            <td><span class="badge" :style="{ background: PAYMENT_STATUS_COLORS[s.payment_status] }">{{ PAYMENT_STATUS_LABELS[s.payment_status] }}</span></td>
            <td>{{ fmtDt(s.created_at) }}</td>
            <td>
              <button v-if="s.payment_status !== 'paid'" @click="openPayModal(s)" class="btn-sm">
                <i class="fa-solid fa-coins"></i> Payer
              </button>
            </td>
          </tr>
          <tr v-if="!credits.data.length">
            <td colspan="8" class="empty-row">Aucun crédit en cours</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="credits.last_page > 1" class="pagination-wrap">
      <Link v-for="link in credits.links" :key="link.label"
            :href="link.url || '#'"
            class="pagination-link"
            :class="{ active: link.active, disabled: !link.url }"
            v-html="link.label" />
    </div>

    <!-- Payment modal -->
    <div v-if="showPayModal" class="modal-overlay" @click.self="showPayModal = false">
      <div class="modal-content">
        <h3 class="section-title">Enregistrer un paiement — {{ selectedSale?.reference }}</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>Montant</label>
            <input v-model.number="payAmount" type="number" min="0.01" step="0.01" class="form-input" />
          </div>
          <div class="form-group">
            <label>Méthode</label>
            <select v-model="payMethod" class="form-input">
              <option value="cash">Espèces</option>
              <option value="wave">Wave</option>
              <option value="om">Orange Money</option>
              <option value="card">Carte</option>
              <option value="other">Autre</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button @click="showPayModal = false" class="btn-outline-gray">Annuler</button>
          <button @click="submitPayment" class="btn-primary"><i class="fa-solid fa-check"></i> Valider</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.5rem}
.flash-success{background:#D1FAE5;color:#065F46;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.section-title{font-size:1rem;font-weight:600;margin-bottom:.75rem;color:#111}
.debt-summary{margin-bottom:1.5rem}
.debt-cards{display:flex;gap:.75rem;flex-wrap:wrap}
.debt-card{background:#FEF2F2;border:1px solid #FCA5A5;padding:.75rem 1rem;min-width:200px}
.debt-name{font-size:.9rem;font-weight:700;color:#991B1B}
.debt-phone{font-size:.75rem;color:#6B7280}
.debt-amount{font-size:1.2rem;font-weight:700;color:#DC2626;margin-top:.25rem}
.debt-detail{font-size:.75rem;color:#6B7280;margin-top:.15rem}
.filters-bar{display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.filter-select{padding:.5rem .75rem;border:1px solid #D1D5DB;font-size:.875rem;min-width:180px}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.6rem .75rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.6rem .75rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.badge{color:#fff;padding:.15rem .5rem;font-size:.75rem;font-weight:600;display:inline-block}
.btn-sm{padding:.3rem .6rem;font-size:.8rem;color:#2563EB;border:1px solid #2563EB;background:none;cursor:pointer;display:inline-flex;align-items:center;gap:.25rem}
.btn-sm:hover{background:#EFF6FF}
.empty-row{text-align:center;color:#9CA3AF;padding:2rem}
.pagination-wrap{display:flex;gap:.25rem;margin-top:1rem;justify-content:center}
.pagination-link{padding:.35rem .65rem;border:1px solid #D1D5DB;font-size:.8rem;color:#374151}
.pagination-link.active{background:#2563EB;color:#fff;border-color:#2563EB}
.pagination-link.disabled{opacity:.4;pointer-events:none}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;z-index:50}
.modal-content{background:#fff;padding:1.5rem;width:90%;max-width:500px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
.form-group{display:flex;flex-direction:column;gap:.3rem}
.form-group label{font-size:.8rem;font-weight:600;color:#374151}
.form-input{padding:.45rem .6rem;border:1px solid #D1D5DB;font-size:.875rem;width:100%}
.form-actions{display:flex;justify-content:flex-end;gap:.5rem}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.btn-outline-gray{border:1px solid #6B7280;color:#6B7280;background:none;padding:.5rem 1rem;cursor:pointer;font-weight:600}
</style>
