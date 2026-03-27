<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ quote: Object })
const t = () => route().params._tenant

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const STATUS_LABELS = { draft: 'Brouillon', sent: 'Envoyé', accepted: 'Accepté', rejected: 'Rejeté', converted: 'Converti' }
const STATUS_COLORS = { draft: '#6B7280', sent: '#3B82F6', accepted: '#10B981', rejected: '#EF4444', converted: '#8B5CF6' }

function changeStatus(newStatus) {
  if (!confirm(`Passer le statut à "${STATUS_LABELS[newStatus]}" ?`)) return
  router.patch(route('app.quincaillerie.quotes.update-status', { _tenant: t(), quote: props.quote.id }), { status: newStatus })
}

function convertToSale() {
  if (!confirm('Convertir ce devis en vente ? Le stock sera déduit.')) return
  router.post(route('app.quincaillerie.quotes.convert', { _tenant: t(), quote: props.quote.id }))
}

function deleteQuote() {
  if (!confirm('Supprimer ce brouillon ?')) return
  router.delete(route('app.quincaillerie.quotes.destroy', { _tenant: t(), quote: props.quote.id }))
}
</script>

<template>
  <AppLayout title="Détail devis">
    <Head title="Détail devis" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-file-invoice" style="color:#3B82F6"></i> {{ quote.reference }}</h1>
      <Link :href="route('app.quincaillerie.quotes.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="flash-error">
      <i class="fa-solid fa-circle-xmark"></i> {{ $page.props.flash.error }}
    </div>

    <div class="detail-card">
      <div class="detail-grid">
        <div><span class="detail-label">Client</span><span class="detail-value">{{ quote.customer?.name || '—' }}</span></div>
        <div><span class="detail-label">Statut</span><span class="badge" :style="{ background: STATUS_COLORS[quote.status] }">{{ STATUS_LABELS[quote.status] }}</span></div>
        <div><span class="detail-label">Total</span><span class="detail-value font-medium">{{ fmt(quote.total) }}</span></div>
        <div><span class="detail-label">Validité</span><span class="detail-value">{{ fmtDt(quote.valid_until) }}</span></div>
        <div><span class="detail-label">Créé par</span><span class="detail-value">{{ quote.user?.name || '—' }}</span></div>
        <div><span class="detail-label">Date</span><span class="detail-value">{{ fmtDt(quote.created_at) }}</span></div>
      </div>

      <div v-if="quote.notes" class="notes-block">
        <strong>Notes :</strong> {{ quote.notes }}
      </div>

      <div v-if="quote.converted_sale" class="converted-block">
        <i class="fa-solid fa-check-circle" style="color:#10B981"></i>
        Converti en vente <strong>{{ quote.converted_sale.reference }}</strong>
      </div>
    </div>

    <h3 class="section-title">Lignes</h3>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Unité</th>
            <th>Qté</th>
            <th>Prix unit.</th>
            <th>Remise</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in quote.items" :key="item.id">
            <td>{{ item.product?.name }}</td>
            <td>{{ item.unit?.symbol || item.unit?.name || '—' }}</td>
            <td>{{ item.quantity }}</td>
            <td>{{ fmt(item.unit_price) }}</td>
            <td>{{ fmt(item.discount) }}</td>
            <td class="font-medium">{{ fmt(item.total) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="text-right font-medium">Sous-total</td>
            <td class="font-medium">{{ fmt(quote.subtotal) }}</td>
          </tr>
          <tr v-if="quote.discount_amount > 0">
            <td colspan="5" class="text-right">Remise</td>
            <td>-{{ fmt(quote.discount_amount) }}</td>
          </tr>
          <tr>
            <td colspan="5" class="text-right" style="font-weight:700;font-size:1rem">Total</td>
            <td style="font-weight:700;font-size:1rem">{{ fmt(quote.total) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="actions-bar">
      <Link v-if="['draft','sent'].includes(quote.status)"
            :href="route('app.quincaillerie.quotes.edit', { _tenant: t(), quote: quote.id })" class="btn-primary">
        <i class="fa-solid fa-pen"></i> Modifier
      </Link>
      <button v-if="quote.status === 'draft'" @click="changeStatus('sent')" class="btn-outline blue">
        <i class="fa-solid fa-paper-plane"></i> Marquer envoyé
      </button>
      <button v-if="quote.status === 'sent'" @click="changeStatus('accepted')" class="btn-outline green">
        <i class="fa-solid fa-check"></i> Accepter
      </button>
      <button v-if="quote.status === 'sent'" @click="changeStatus('rejected')" class="btn-outline red">
        <i class="fa-solid fa-xmark"></i> Rejeter
      </button>
      <button v-if="quote.status === 'accepted'" @click="convertToSale" class="btn-primary" style="background:#10B981">
        <i class="fa-solid fa-arrow-right-arrow-left"></i> Convertir en vente
      </button>
      <button v-if="quote.status === 'draft'" @click="deleteQuote" class="btn-outline red">
        <i class="fa-solid fa-trash"></i> Supprimer
      </button>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.5rem}
.btn-back{color:#6B7280;font-size:.875rem;display:inline-flex;align-items:center;gap:.3rem}
.flash-success{background:#D1FAE5;color:#065F46;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.flash-error{background:#FEE2E2;color:#991B1B;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.detail-card{background:#fff;border:1px solid #E5E7EB;padding:1.25rem;margin-bottom:1.5rem}
.detail-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.detail-label{display:block;font-size:.75rem;color:#6B7280;text-transform:uppercase;font-weight:600;margin-bottom:.2rem}
.detail-value{font-size:.95rem;color:#111}
.font-medium{font-weight:500}
.badge{color:#fff;padding:.15rem .5rem;font-size:.75rem;font-weight:600;display:inline-block}
.notes-block{margin-top:1rem;padding:.75rem;background:#F9FAFB;font-size:.875rem;color:#374151}
.converted-block{margin-top:1rem;padding:.75rem;background:#D1FAE5;font-size:.875rem;display:flex;align-items:center;gap:.5rem}
.section-title{font-size:1rem;font-weight:600;margin-bottom:.75rem;color:#111}
.table-wrap{overflow-x:auto;margin-bottom:1.5rem}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.5rem .6rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.5rem .6rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.data-table tfoot td{border-bottom:none;padding:.4rem .6rem}
.text-right{text-align:right}
.actions-bar{display:flex;gap:.5rem;flex-wrap:wrap}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.btn-primary:hover{opacity:.9}
.btn-outline{background:none;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;cursor:pointer}
.btn-outline.blue{border:1px solid #3B82F6;color:#3B82F6}
.btn-outline.blue:hover{background:#EFF6FF}
.btn-outline.green{border:1px solid #10B981;color:#10B981}
.btn-outline.green:hover{background:#D1FAE5}
.btn-outline.red{border:1px solid #EF4444;color:#EF4444}
.btn-outline.red:hover{background:#FEE2E2}
</style>
