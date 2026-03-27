<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ sale: Object })
const company = usePage().props.currentCompany
const fmt = (v) => new Intl.NumberFormat('fr-FR').format(Math.round(v))
</script>

<template>
  <AppLayout title="Reçu de vente">
    <div class="receipt-page">
      <div class="receipt-actions">
        <Link :href="route('app.quincaillerie.pos.index', { _tenant: company.slug })" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Nouvelle vente
        </Link>
        <button class="btn-print" onclick="window.print()">
          <i class="fa-solid fa-print"></i> Imprimer
        </button>
      </div>

      <div class="receipt-card">
        <div class="receipt-header">
          <h1>{{ company.name }}</h1>
          <p v-if="company.address">{{ company.address }}</p>
          <p v-if="company.phone">{{ company.phone }}</p>
          <div class="receipt-ref">{{ sale.reference }}</div>
          <div class="receipt-date">{{ new Date(sale.created_at).toLocaleString('fr-FR') }}</div>
          <div class="receipt-cashier">Vendeur: {{ sale.user?.name }}</div>
        </div>

        <div v-if="sale.customer" class="receipt-customer">
          Client: <strong>{{ sale.customer.name }}</strong>
          <span v-if="sale.customer.phone"> — {{ sale.customer.phone }}</span>
        </div>

        <table class="receipt-table">
          <thead>
            <tr><th>Article</th><th>Qté</th><th>P.U.</th><th>Remise</th><th>Total</th></tr>
          </thead>
          <tbody>
            <tr v-for="item in sale.items" :key="item.id">
              <td>{{ item.product?.name }}</td>
              <td class="right">{{ item.quantity }}</td>
              <td class="right">{{ fmt(item.unit_price) }}</td>
              <td class="right">{{ fmt(item.discount || 0) }}</td>
              <td class="right">{{ fmt(item.total) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="receipt-totals">
          <div class="total-line"><span>Sous-total</span><span>{{ fmt(sale.subtotal) }} F</span></div>
          <div v-if="sale.discount_amount > 0" class="total-line"><span>Remise</span><span>-{{ fmt(sale.discount_amount) }} F</span></div>
          <div v-if="sale.tax_amount > 0" class="total-line"><span>Taxe</span><span>+{{ fmt(sale.tax_amount) }} F</span></div>
          <div class="total-line grand"><span>TOTAL</span><span>{{ fmt(sale.total) }} F</span></div>
        </div>

        <div class="receipt-payment">
          Paiement: <strong>{{ sale.payment_status === 'paid' ? (sale.payments?.[0]?.method || 'cash').toUpperCase() : 'CRÉDIT' }}</strong>
          <span v-if="sale.payment_status === 'unpaid'" class="credit-badge">Non payé</span>
        </div>

        <div v-if="sale.notes" class="receipt-notes">Notes: {{ sale.notes }}</div>
        <div class="receipt-footer">Merci pour votre achat !</div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.receipt-page {max-width:480px}
.receipt-actions {display:flex;justify-content:space-between;margin-bottom:16px}
.btn-back,.btn-print {display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:.82rem;font-weight:600;border:1px solid #D1D5DB;background:#fff;color:#374151;cursor:pointer;text-decoration:none;transition:all .15s}
.btn-back:hover,.btn-print:hover {background:#F3F4F6}
.btn-print {background:#4F46E5;color:#fff;border-color:#4F46E5}
.btn-print:hover {background:#4338CA}

.receipt-card {background:#fff;border:1px solid #E5E7EB;padding:24px}
.receipt-header {text-align:center;margin-bottom:16px}
.receipt-header h1 {font-size:1.1rem;font-weight:800;color:#111827;margin:0}
.receipt-header p {font-size:.78rem;color:#6B7280;margin:2px 0}
.receipt-ref {font-size:.88rem;font-weight:700;color:#4F46E5;margin-top:8px}
.receipt-date,.receipt-cashier {font-size:.75rem;color:#6B7280}

.receipt-customer {font-size:.82rem;color:#374151;padding:8px 0;border-bottom:1px dashed #D1D5DB;margin-bottom:12px}

.receipt-table {width:100%;border-collapse:collapse;font-size:.78rem;margin-bottom:12px}
.receipt-table th {font-weight:700;color:#6B7280;text-align:left;padding:6px 4px;border-bottom:1px solid #E5E7EB}
.receipt-table td {padding:6px 4px;color:#111827;border-bottom:1px solid #F3F4F6}
.right {text-align:right}

.receipt-totals {padding:8px 0}
.total-line {display:flex;justify-content:space-between;font-size:.82rem;padding:3px 0;color:#374151}
.total-line.grand {font-size:1rem;font-weight:900;color:#111827;border-top:2px solid #111827;padding-top:8px;margin-top:6px}

.receipt-payment {font-size:.82rem;color:#374151;padding:8px 0;border-top:1px dashed #D1D5DB}
.credit-badge {font-size:.7rem;font-weight:700;padding:2px 8px;background:#FEF3C7;color:#92400E;margin-left:8px}
.receipt-notes {font-size:.78rem;color:#6B7280;padding:8px 0;font-style:italic}
.receipt-footer {text-align:center;font-size:.82rem;color:#9CA3AF;margin-top:16px;padding-top:12px;border-top:1px dashed #D1D5DB}

@media print {.receipt-actions {display:none} .receipt-card {border:none;box-shadow:none}}
</style>
