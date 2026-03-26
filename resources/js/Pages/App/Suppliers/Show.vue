<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ supplier: Object })
</script>

<template>
  <AppLayout title="Fiche fournisseur">
    <Head :title="supplier.name" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-truck-field" style="color:#F59E0B"></i> {{ supplier.name }}</h1>
      <div class="header-actions">
        <Link :href="route('app.suppliers.edit', { supplier: supplier.id, _tenant: route().params._tenant })" class="btn-secondary">
          <i class="fa-solid fa-pen"></i> Modifier
        </Link>
        <Link :href="route('app.suppliers.index', { _tenant: route().params._tenant })" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Liste
        </Link>
      </div>
    </div>

    <div class="detail-card">
      <div class="detail-grid">
        <div class="detail-item">
          <div class="detail-label">Téléphone</div>
          <div class="detail-value">{{ supplier.phone }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Email</div>
          <div class="detail-value">{{ supplier.email || '—' }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Adresse</div>
          <div class="detail-value">{{ supplier.address || '—' }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">NINEA</div>
          <div class="detail-value">{{ supplier.ninea || '—' }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">RIB</div>
          <div class="detail-value">{{ supplier.rib || '—' }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Note</div>
          <div class="detail-value">
            <span v-if="supplier.rating" class="rating-badge">
              <i class="fa-solid fa-star"></i> {{ Number(supplier.rating).toFixed(1) }} / 5
            </span>
            <span v-else class="text-muted">Non évalué</span>
          </div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Solde dû</div>
          <div class="detail-value" :class="{ 'text-red': parseFloat(supplier.outstanding_balance) > 0 }">
            {{ Number(supplier.outstanding_balance).toLocaleString('fr-FR') }} F CFA
          </div>
        </div>
        <div class="detail-item full" v-if="supplier.notes">
          <div class="detail-label">Notes internes</div>
          <div class="detail-value notes-text">{{ supplier.notes }}</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #F59E0B; }
.header-actions { display: flex; gap: 10px; }
.btn-secondary { background: #fff; border: 1px solid #D1D5DB; padding: 8px 14px; font-size: 0.82rem; font-weight: 600; color: #374151; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-secondary:hover { background: #F3F4F6; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }

.detail-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 640px; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.detail-item.full { grid-column: 1 / -1; }
.detail-label { font-size: 0.72rem; font-weight: 600; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.detail-value { font-size: 0.88rem; color: #111827; font-weight: 500; }
.rating-badge { font-weight: 600; color: #F59E0B; }
.text-muted { color: #D1D5DB; font-style: italic; }
.text-red { color: #DC2626; font-weight: 700; }
.notes-text { white-space: pre-line; font-size: 0.84rem; color: #374151; background: #F9FAFB; padding: 10px; border: 1px solid #F3F4F6; }
</style>
