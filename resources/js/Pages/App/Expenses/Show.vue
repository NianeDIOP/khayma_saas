<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ expense: Object })
</script>

<template>
  <AppLayout :title="'Dépense #' + expense.id">
    <Head :title="'Dépense #' + expense.id" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-money-bill-trend-up" style="color:#DC2626"></i> Dépense #{{ expense.id }}</h1>
      <div class="header-actions">
        <Link :href="route('app.expenses.edit', { expense: expense.id, _tenant: route().params._tenant })" class="btn-edit">
          <i class="fa-solid fa-pen"></i> Modifier
        </Link>
        <Link :href="route('app.expenses.index', { _tenant: route().params._tenant })" class="btn-back">
          <i class="fa-solid fa-arrow-left"></i> Retour
        </Link>
      </div>
    </div>

    <div class="detail-card">
      <div class="info-row"><span class="label">Catégorie</span><span class="cat-badge">{{ expense.expense_category?.name }}</span></div>
      <div class="info-row"><span class="label">Montant</span><span class="text-bold text-red">{{ Number(expense.amount).toLocaleString('fr-FR') }} F CFA</span></div>
      <div class="info-row"><span class="label">Date</span><span>{{ new Date(expense.date).toLocaleDateString('fr-FR') }}</span></div>
      <div class="info-row"><span class="label">Fournisseur</span><span>{{ expense.supplier?.name || '—' }}</span></div>
      <div class="info-row"><span class="label">Enregistré par</span><span>{{ expense.user?.name || '—' }}</span></div>
      <div class="info-row" v-if="expense.description"><span class="label">Description</span><span>{{ expense.description }}</span></div>
      <div class="info-row" v-if="expense.receipt_url">
        <span class="label">Reçu</span>
        <a :href="expense.receipt_url" target="_blank" class="receipt-link"><i class="fa-solid fa-external-link-alt"></i> Voir le reçu</a>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #DC2626; }
.header-actions { display: flex; gap: 8px; }
.btn-edit { background: #F59E0B; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-edit:hover { background: #D97706; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.btn-back:hover { color: #111827; }
.detail-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 600px; }
.info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #F3F4F6; font-size: 0.85rem; }
.info-row .label { color: #6B7280; min-width: 140px; }
.cat-badge { background: #FEE2E2; color: #DC2626; font-size: 0.72rem; font-weight: 600; padding: 2px 8px; }
.text-bold { font-weight: 700; }
.text-red { color: #DC2626; }
.receipt-link { color: #3B82F6; text-decoration: none; font-size: 0.82rem; }
.receipt-link:hover { text-decoration: underline; }
</style>
