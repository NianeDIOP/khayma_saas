<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ customer: Object })

const categoryLabels = { normal: 'Normal', vip: 'VIP', professional: 'Professionnel' }
const categoryColors = { normal: '#6B7280', vip: '#F59E0B', professional: '#3B82F6' }
</script>

<template>
  <AppLayout title="Fiche client">
    <Head :title="customer.name" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-user" style="color:#2563EB"></i> {{ customer.name }}</h1>
      <div class="header-actions">
        <Link :href="route('app.customers.edit', { customer: customer.id, _tenant: route().params._tenant })" class="btn-secondary">
          <i class="fa-solid fa-pen"></i> Modifier
        </Link>
        <Link :href="route('app.customers.index', { _tenant: route().params._tenant })" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Liste
        </Link>
      </div>
    </div>

    <div class="detail-card">
      <div class="detail-grid">
        <div class="detail-item">
          <div class="detail-label">Téléphone</div>
          <div class="detail-value">{{ customer.phone }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Email</div>
          <div class="detail-value">{{ customer.email || '—' }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Adresse</div>
          <div class="detail-value">{{ customer.address || '—' }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">NIF</div>
          <div class="detail-value">{{ customer.nif || '—' }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Catégorie</div>
          <div class="detail-value">
            <span class="cat-badge" :style="{ color: categoryColors[customer.category], background: categoryColors[customer.category] + '18' }">
              {{ categoryLabels[customer.category] }}
            </span>
          </div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Points fidélité</div>
          <div class="detail-value">{{ customer.loyalty_points }}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Solde impayé</div>
          <div class="detail-value" :class="{ 'text-red': parseFloat(customer.outstanding_balance) > 0 }">
            {{ Number(customer.outstanding_balance).toLocaleString('fr-FR') }} F CFA
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #2563EB; }
.header-actions { display: flex; gap: 10px; }
.btn-secondary { background: #fff; border: 1px solid #D1D5DB; padding: 8px 14px; font-size: 0.82rem; font-weight: 600; color: #374151; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-secondary:hover { background: #F3F4F6; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }

.detail-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 640px; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.detail-label { font-size: 0.72rem; font-weight: 600; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.detail-value { font-size: 0.88rem; color: #111827; font-weight: 500; }
.cat-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; text-transform: uppercase; }
.text-red { color: #DC2626; font-weight: 700; }
</style>
