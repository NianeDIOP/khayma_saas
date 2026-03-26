<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ product: Object })
</script>

<template>
  <AppLayout :title="product.name">
    <Head :title="product.name" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-box" style="color:#10B981"></i> {{ product.name }}</h1>
      <div class="header-actions">
        <Link :href="route('app.products.edit', { product: product.id, _tenant: route().params._tenant })" class="btn-edit">
          <i class="fa-solid fa-pen"></i> Modifier
        </Link>
        <Link :href="route('app.products.index', { _tenant: route().params._tenant })" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Retour
        </Link>
      </div>
    </div>

    <div class="detail-grid">
      <div class="detail-card">
        <h3>Informations</h3>
        <div class="info-row"><span class="label">Description</span><span>{{ product.description || '—' }}</span></div>
        <div class="info-row"><span class="label">Catégorie</span><span>{{ product.category?.name || '—' }}</span></div>
        <div class="info-row"><span class="label">Unité</span><span>{{ product.unit?.name || '—' }}</span></div>
        <div class="info-row"><span class="label">Code-barres</span><span>{{ product.barcode || '—' }}</span></div>
        <div class="info-row">
          <span class="label">Statut</span>
          <span class="status-badge" :class="product.is_active ? 'active' : 'inactive'">{{ product.is_active ? 'Actif' : 'Inactif' }}</span>
        </div>
      </div>

      <div class="detail-card">
        <h3>Prix & Stock</h3>
        <div class="info-row"><span class="label">Prix d'achat</span><span>{{ Number(product.purchase_price).toLocaleString('fr-FR') }} F</span></div>
        <div class="info-row"><span class="label">Prix de vente</span><span class="text-green">{{ Number(product.selling_price).toLocaleString('fr-FR') }} F</span></div>
        <div class="info-row">
          <span class="label">Marge</span>
          <span class="text-green">{{ Number(product.selling_price - product.purchase_price).toLocaleString('fr-FR') }} F</span>
        </div>
        <div class="info-row"><span class="label">Seuil alerte</span><span>{{ product.min_stock_alert }}</span></div>
      </div>

      <div v-if="product.stock_items && product.stock_items.length" class="detail-card full">
        <h3>Stock par dépôt</h3>
        <table class="stock-table">
          <thead>
            <tr><th>Dépôt</th><th>Quantité</th></tr>
          </thead>
          <tbody>
            <tr v-for="si in product.stock_items" :key="si.id">
              <td>{{ si.depot?.name }}</td>
              <td :class="{ 'text-red': parseFloat(si.quantity) <= product.min_stock_alert }">{{ si.quantity }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #10B981; }
.header-actions { display: flex; gap: 10px; }
.btn-edit { font-size: 0.82rem; color: #10B981; text-decoration: none; display: flex; align-items: center; gap: 6px; border: 1px solid #10B981; padding: 6px 14px; }
.btn-edit:hover { background: #D1FAE5; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.detail-card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.detail-card.full { grid-column: 1 / -1; }
.detail-card h3 { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 14px; }
.info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #F3F4F6; font-size: 0.82rem; }
.info-row .label { color: #6B7280; }
.text-green { color: #10B981; font-weight: 600; }
.text-red { color: #DC2626; font-weight: 600; }
.status-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; text-transform: uppercase; }
.status-badge.active { color: #10B981; background: #D1FAE5; }
.status-badge.inactive { color: #6B7280; background: #F3F4F6; }
.stock-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.stock-table th { text-align: left; padding: 8px 12px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB; font-weight: 600; color: #374151; }
.stock-table td { padding: 8px 12px; border-bottom: 1px solid #F3F4F6; }
</style>
