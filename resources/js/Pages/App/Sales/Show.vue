<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ sale: Object })

const statusLabels = { completed: 'Terminée', cancelled: 'Annulée', refunded: 'Remboursée' }
const statusColors = { completed: '#10B981', cancelled: '#EF4444', refunded: '#F59E0B' }
const methodLabels = { cash: 'Espèces', wave: 'Wave', om: 'Orange Money', card: 'Carte bancaire', other: 'Autre' }
</script>

<template>
  <AppLayout :title="sale.reference">
    <Head :title="sale.reference" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-receipt" style="color:#F59E0B"></i> {{ sale.reference }}</h1>
      <Link :href="route('app.sales.index', { _tenant: route().params._tenant })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour aux ventes
      </Link>
    </div>

    <div class="detail-grid">
      <div class="detail-card">
        <h3>Informations</h3>
        <div class="info-row"><span class="label">Date</span><span>{{ new Date(sale.created_at).toLocaleString('fr-FR') }}</span></div>
        <div class="info-row"><span class="label">Client</span><span>{{ sale.customer?.name || 'Client de passage' }}</span></div>
        <div class="info-row"><span class="label">Vendeur</span><span>{{ sale.user?.name || '—' }}</span></div>
        <div class="info-row"><span class="label">Type</span><span>{{ sale.type }}</span></div>
        <div class="info-row">
          <span class="label">Statut</span>
          <span class="status-badge" :style="{ color: statusColors[sale.status], background: statusColors[sale.status] + '18' }">
            {{ statusLabels[sale.status] }}
          </span>
        </div>
        <div class="info-row" v-if="sale.notes"><span class="label">Notes</span><span>{{ sale.notes }}</span></div>
      </div>

      <div class="detail-card">
        <h3>Montants</h3>
        <div class="info-row"><span class="label">Sous-total</span><span>{{ Number(sale.subtotal).toLocaleString('fr-FR') }} F</span></div>
        <div class="info-row"><span class="label">Remise</span><span>-{{ Number(sale.discount_amount).toLocaleString('fr-FR') }} F</span></div>
        <div class="info-row"><span class="label">TVA</span><span>{{ Number(sale.tax_amount).toLocaleString('fr-FR') }} F</span></div>
        <div class="info-row total"><span class="label">TOTAL</span><span>{{ Number(sale.total).toLocaleString('fr-FR') }} F</span></div>
      </div>

      <div class="detail-card full">
        <h3>Articles</h3>
        <table class="items-table">
          <thead>
            <tr><th>Produit</th><th>Qté</th><th>P.U.</th><th>Remise</th><th>Total</th></tr>
          </thead>
          <tbody>
            <tr v-for="item in sale.items" :key="item.id">
              <td class="cell-name">{{ item.product?.name }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ Number(item.unit_price).toLocaleString('fr-FR') }} F</td>
              <td>{{ Number(item.discount).toLocaleString('fr-FR') }} F</td>
              <td class="text-bold">{{ Number(item.total).toLocaleString('fr-FR') }} F</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="sale.payments && sale.payments.length" class="detail-card full">
        <h3>Paiements</h3>
        <table class="items-table">
          <thead>
            <tr><th>Date</th><th>Mode</th><th>Montant</th><th>Référence</th></tr>
          </thead>
          <tbody>
            <tr v-for="p in sale.payments" :key="p.id">
              <td>{{ new Date(p.created_at).toLocaleDateString('fr-FR') }}</td>
              <td>{{ methodLabels[p.method] || p.method }}</td>
              <td class="text-bold">{{ Number(p.amount).toLocaleString('fr-FR') }} F</td>
              <td>{{ p.reference || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #F59E0B; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.detail-card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.detail-card.full { grid-column: 1 / -1; }
.detail-card h3 { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 14px; }
.info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #F3F4F6; font-size: 0.82rem; }
.info-row .label { color: #6B7280; }
.info-row.total { border-top: 2px solid #F59E0B; font-size: 1rem; font-weight: 800; }
.status-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; text-transform: uppercase; }
.items-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.items-table th { text-align: left; padding: 8px 12px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB; font-weight: 600; color: #374151; }
.items-table td { padding: 8px 12px; border-bottom: 1px solid #F3F4F6; }
.cell-name { font-weight: 600; }
.text-bold { font-weight: 700; }
</style>
