<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ purchaseOrder: Object })
const t = () => route().params._tenant
const po = props.purchaseOrder

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const STATUS_LABELS = { draft: 'Brouillon', sent: 'Envoyé', partial: 'Partiel', received: 'Reçu', cancelled: 'Annulé' }
const STATUS_COLORS = { draft: '#6B7280', sent: '#3B82F6', partial: '#F59E0B', received: '#10B981', cancelled: '#EF4444' }

function changeStatus(s) {
  if (!confirm(`Passer le statut à "${STATUS_LABELS[s]}" ?`)) return
  router.patch(route('app.quincaillerie.purchase-orders.update-status', { _tenant: t(), purchase_order: po.id }), { status: s })
}

// Receive form
const showReceive = ref(false)
const receiveItems = ref(po.items?.map(i => ({
  purchase_order_item_id: i.id,
  product_name: i.product?.name,
  ordered: i.quantity,
  already_received: i.received_quantity,
  remaining: i.quantity - i.received_quantity,
  received_qty: 0,
})) || [])

function submitReceive() {
  router.post(route('app.quincaillerie.purchase-orders.receive', { _tenant: t(), purchase_order: po.id }), {
    items: receiveItems.value.map(i => ({
      purchase_order_item_id: i.purchase_order_item_id,
      received_qty: i.received_qty,
    }))
  })
}

function deletePO() {
  if (!confirm('Supprimer ce brouillon ?')) return
  router.delete(route('app.quincaillerie.purchase-orders.destroy', { _tenant: t(), purchase_order: po.id }))
}
</script>

<template>
  <AppLayout title="Détail BC">
    <Head title="Détail BC" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-truck" style="color:#F59E0B"></i> {{ po.reference }}</h1>
      <Link :href="route('app.quincaillerie.purchase-orders.index', { _tenant: t() })" class="btn-back">
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
        <div><span class="detail-label">Fournisseur</span><span class="detail-value">{{ po.supplier?.name || '—' }}</span></div>
        <div><span class="detail-label">Statut</span><span class="badge" :style="{ background: STATUS_COLORS[po.status] }">{{ STATUS_LABELS[po.status] }}</span></div>
        <div><span class="detail-label">Total</span><span class="detail-value font-medium">{{ fmt(po.total) }}</span></div>
        <div><span class="detail-label">Date prévue</span><span class="detail-value">{{ fmtDt(po.expected_date) }}</span></div>
        <div><span class="detail-label">Créé par</span><span class="detail-value">{{ po.user?.name || '—' }}</span></div>
        <div><span class="detail-label">Reçu le</span><span class="detail-value">{{ fmtDt(po.received_at) }}</span></div>
      </div>
      <div v-if="po.notes" class="notes-block"><strong>Notes :</strong> {{ po.notes }}</div>
    </div>

    <h3 class="section-title">Articles</h3>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Produit</th><th>Unité</th><th>Qté commandée</th><th>Qté reçue</th><th>Prix unit.</th><th>Total</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in po.items" :key="item.id">
            <td>{{ item.product?.name }}</td>
            <td>{{ item.unit?.symbol || item.unit?.name || '—' }}</td>
            <td>{{ item.quantity }}</td>
            <td>
              <span :style="{ color: item.received_quantity >= item.quantity ? '#10B981' : '#F59E0B', fontWeight: 600 }">
                {{ item.received_quantity }}
              </span>
            </td>
            <td>{{ fmt(item.unit_price) }}</td>
            <td class="font-medium">{{ fmt(item.total) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr><td colspan="5" style="text-align:right;font-weight:700;font-size:1rem">Total</td><td style="font-weight:700;font-size:1rem">{{ fmt(po.total) }}</td></tr>
        </tfoot>
      </table>
    </div>

    <!-- Paiements fournisseur liés -->
    <div v-if="po.supplier_payments?.length">
      <h3 class="section-title">Paiements fournisseur</h3>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Date</th><th>Montant</th><th>Méthode</th><th>Réf.</th></tr></thead>
          <tbody>
            <tr v-for="p in po.supplier_payments" :key="p.id">
              <td>{{ fmtDt(p.date) }}</td>
              <td class="font-medium">{{ fmt(p.amount) }}</td>
              <td>{{ p.method }}</td>
              <td>{{ p.reference || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Receive modal -->
    <div v-if="showReceive" class="modal-overlay" @click.self="showReceive = false">
      <div class="modal-content">
        <h3 class="section-title">Réception des articles</h3>
        <table class="data-table" style="margin-bottom:1rem">
          <thead><tr><th>Produit</th><th>Commandé</th><th>Déjà reçu</th><th>Restant</th><th>Qté reçue</th></tr></thead>
          <tbody>
            <tr v-for="(ri, idx) in receiveItems" :key="idx">
              <td>{{ ri.product_name }}</td>
              <td>{{ ri.ordered }}</td>
              <td>{{ ri.already_received }}</td>
              <td>{{ ri.remaining }}</td>
              <td><input v-model.number="ri.received_qty" type="number" min="0" :max="ri.remaining" step="0.01" class="form-input input-sm" /></td>
            </tr>
          </tbody>
        </table>
        <div class="form-actions">
          <button @click="showReceive = false" class="btn-outline" style="border:1px solid #6B7280;color:#6B7280">Annuler</button>
          <button @click="submitReceive" class="btn-primary"><i class="fa-solid fa-check"></i> Valider la réception</button>
        </div>
      </div>
    </div>

    <div class="actions-bar">
      <Link v-if="po.status === 'draft'"
            :href="route('app.quincaillerie.purchase-orders.edit', { _tenant: t(), purchase_order: po.id })" class="btn-primary">
        <i class="fa-solid fa-pen"></i> Modifier
      </Link>
      <button v-if="po.status === 'draft'" @click="changeStatus('sent')" class="btn-outline blue">
        <i class="fa-solid fa-paper-plane"></i> Marquer envoyé
      </button>
      <button v-if="['sent','partial'].includes(po.status)" @click="showReceive = true" class="btn-primary" style="background:#10B981">
        <i class="fa-solid fa-box-open"></i> Réceptionner
      </button>
      <button v-if="['draft','sent'].includes(po.status)" @click="changeStatus('cancelled')" class="btn-outline red">
        <i class="fa-solid fa-ban"></i> Annuler
      </button>
      <button v-if="po.status === 'draft'" @click="deletePO" class="btn-outline red">
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
.section-title{font-size:1rem;font-weight:600;margin-bottom:.75rem;color:#111}
.table-wrap{overflow-x:auto;margin-bottom:1.5rem}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.5rem .6rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.5rem .6rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.data-table tfoot td{border-bottom:none}
.actions-bar{display:flex;gap:.5rem;flex-wrap:wrap}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.btn-primary:hover{opacity:.9}
.btn-outline{background:none;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;cursor:pointer}
.btn-outline.blue{border:1px solid #3B82F6;color:#3B82F6}
.btn-outline.blue:hover{background:#EFF6FF}
.btn-outline.red{border:1px solid #EF4444;color:#EF4444}
.btn-outline.red:hover{background:#FEE2E2}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;z-index:50}
.modal-content{background:#fff;padding:1.5rem;width:90%;max-width:700px;max-height:80vh;overflow-y:auto}
.form-input{padding:.45rem .6rem;border:1px solid #D1D5DB;font-size:.875rem}
.input-sm{width:90px}
.form-actions{display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem}
</style>
