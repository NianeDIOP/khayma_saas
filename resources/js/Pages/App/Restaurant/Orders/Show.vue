<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ order: Object })
const t = () => route().params._tenant

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) : '—' }

const TYPE_LABELS = { table: 'Sur table', takeaway: 'À emporter', delivery: 'Livraison' }
const STATUS_LABELS = { pending: 'En attente', completed: 'Terminée', cancelled: 'Annulée' }
const PAY_LABELS = { cash: 'Cash', wave: 'Wave', om: 'Orange Money', card: 'Carte', other: 'Autre' }

const showCancel = ref(false)
const cancelForm = useForm({ cancel_reason: '' })

function cancelOrder() {
  cancelForm.post(route('app.restaurant.orders.cancel', { order: props.order.id, _tenant: t() }))
}
</script>

<template>
  <AppLayout title="Détail commande">
    <Head :title="'Commande ' + order.reference" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-clipboard-list" style="color:#EF4444"></i> {{ order.reference }}</h1>
      <div class="header-actions">
        <button v-if="order.status !== 'cancelled'" @click="showCancel = !showCancel" class="btn-cancel">
          <i class="fa-solid fa-ban"></i> Annuler
        </button>
        <Link :href="route('app.restaurant.orders.index', { _tenant: t() })" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Retour
        </Link>
      </div>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <!-- Cancel form -->
    <div v-if="showCancel" class="cancel-card">
      <form @submit.prevent="cancelOrder">
        <div class="field">
          <label>Motif d'annulation *</label>
          <textarea v-model="cancelForm.cancel_reason" rows="2" required></textarea>
        </div>
        <button type="submit" class="btn-confirm-cancel" :disabled="cancelForm.processing">Confirmer l'annulation</button>
      </form>
    </div>

    <div class="detail-grid">
      <div class="detail-card">
        <div class="card-title">Informations</div>
        <div class="info-row"><span>Type</span><strong>{{ TYPE_LABELS[order.type] }}</strong></div>
        <div class="info-row" v-if="order.table_number"><span>Table</span><strong>{{ order.table_number }}</strong></div>
        <div class="info-row" v-if="order.delivery_address"><span>Adresse</span><strong>{{ order.delivery_address }}</strong></div>
        <div class="info-row" v-if="order.delivery_person"><span>Livreur</span><strong>{{ order.delivery_person }}</strong></div>
        <div class="info-row" v-if="order.customer_name"><span>Client</span><strong>{{ order.customer_name }}</strong></div>
        <div class="info-row"><span>Service</span><strong>{{ order.service?.name || '—' }}</strong></div>
        <div class="info-row"><span>Caissier</span><strong>{{ order.user?.name || '—' }}</strong></div>
        <div class="info-row"><span>Date</span><strong>{{ fmtDt(order.created_at) }}</strong></div>
        <div class="info-row"><span>Statut</span><strong>{{ STATUS_LABELS[order.status] }}</strong></div>
        <div class="info-row"><span>Paiement</span><strong>{{ PAY_LABELS[order.payment_method] }} — {{ order.payment_status === 'paid' ? 'Payé' : 'En attente' }}</strong></div>
        <div class="info-row" v-if="order.cancel_reason"><span>Motif annul.</span><strong class="text-red">{{ order.cancel_reason }}</strong></div>
      </div>

      <div class="detail-card">
        <div class="card-title">Articles</div>
        <table class="items-table">
          <thead><tr><th>Plat</th><th>Qté</th><th>P.U.</th><th>Total</th></tr></thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id">
              <td class="cell-name">{{ item.dish?.name || '—' }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ fmt(item.unit_price) }}</td>
              <td class="cell-total">{{ fmt(item.total) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="totals-block">
          <div class="total-row"><span>Sous-total</span><span>{{ fmt(order.subtotal) }}</span></div>
          <div class="total-row" v-if="order.discount_amount > 0"><span>Remise</span><span>-{{ fmt(order.discount_amount) }}</span></div>
          <div class="total-row total-final"><span>TOTAL</span><span>{{ fmt(order.total) }}</span></div>
        </div>
      </div>
    </div>

    <div v-if="order.notes" class="notes-card">
      <div class="card-title">Notes</div>
      <p>{{ order.notes }}</p>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #EF4444; }
.header-actions { display: flex; gap: 10px; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-cancel { font-size: 0.82rem; background: #FEE2E2; color: #EF4444; border: 1px solid #FECACA; padding: 6px 14px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: 600; }
.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.cancel-card { background: #FEF2F2; border: 1px solid #FECACA; padding: 16px; margin-bottom: 16px; max-width: 500px; }
.cancel-card .field { margin-bottom: 12px; }
.cancel-card .field label { font-size: 0.82rem; font-weight: 600; color: #991B1B; display: block; margin-bottom: 6px; }
.cancel-card .field textarea { width: 100%; padding: 8px 12px; border: 1px solid #FECACA; font-size: 0.82rem; outline: none; font-family: inherit; }
.btn-confirm-cancel { background: #EF4444; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.detail-card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.card-title { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #F3F4F6; }
.info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.82rem; color: #374151; border-bottom: 1px solid #F9FAFB; }
.text-red { color: #EF4444; }

.items-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.items-table th { background: #F9FAFB; text-align: left; padding: 8px 10px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.items-table td { padding: 8px 10px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.cell-name { font-weight: 600; }
.cell-total { font-weight: 700; }

.totals-block { border-top: 2px solid #E5E7EB; margin-top: 10px; padding-top: 10px; }
.total-row { display: flex; justify-content: space-between; font-size: 0.82rem; color: #374151; padding: 3px 0; }
.total-final { font-size: 0.95rem; font-weight: 800; color: #111827; border-top: 1px solid #E5E7EB; margin-top: 6px; padding-top: 8px; }

.notes-card { background: #fff; border: 1px solid #E5E7EB; padding: 16px; }
.notes-card p { font-size: 0.82rem; color: #374151; }
@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
