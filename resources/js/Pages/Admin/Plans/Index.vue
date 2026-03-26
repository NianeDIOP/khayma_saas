<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ plans: Array })

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) }

function deletePlan(plan) {
  if (!confirm(`Supprimer le plan "${plan.name}" ?`)) return
  router.delete(route('admin.plans.destroy', plan.id))
}
</script>

<template>
  <AdminLayout title="Plans & Tarifs">
    <Head title="Admin · Plans" />

    <div class="toolbar">
      <h1 class="page-title">Plans & Tarifs</h1>
      <Link :href="route('admin.plans.create')" class="kh-btn">
        <i class="fa-solid fa-plus"></i> Nouveau plan
      </Link>
    </div>

    <div class="plans-grid">
      <div v-for="plan in plans" :key="plan.id" class="plan-card" :class="{ inactive: !plan.is_active }">
        <div class="plan-header">
          <div class="plan-name">{{ plan.name }}</div>
          <span class="plan-code">{{ plan.code }}</span>
          <span v-if="!plan.is_active" class="badge-off">Inactif</span>
        </div>

        <div class="plan-prices">
          <div class="price-item">
            <span class="price-label">Mensuel</span>
            <span class="price-value">{{ fmt(plan.price_monthly) }} <small>XOF</small></span>
          </div>
          <div class="price-item">
            <span class="price-label">Trimestriel</span>
            <span class="price-value">{{ fmt(plan.price_quarterly) }} <small>XOF</small></span>
          </div>
          <div class="price-item">
            <span class="price-label">Annuel</span>
            <span class="price-value">{{ fmt(plan.price_yearly) }} <small>XOF</small></span>
          </div>
        </div>

        <div class="plan-limits">
          <div class="limit"><i class="fa-solid fa-box"></i> {{ plan.max_products === 0 ? 'Illimité' : plan.max_products }} produits</div>
          <div class="limit"><i class="fa-solid fa-users"></i> {{ plan.max_users }} utilisateurs</div>
          <div class="limit"><i class="fa-solid fa-hard-drive"></i> {{ plan.max_storage_gb }} Go</div>
          <div class="limit"><i class="fa-solid fa-exchange-alt"></i> {{ plan.max_transactions_month === 0 ? 'Illimité' : fmt(plan.max_transactions_month) }} trans/mois</div>
          <div class="limit"><i class="fa-solid fa-bolt"></i> {{ plan.api_rate_limit }}/min API</div>
        </div>

        <div class="plan-footer">
          <span class="sub-count">{{ plan.subscriptions_count || 0 }} abonnement{{ (plan.subscriptions_count || 0) > 1 ? 's' : '' }}</span>
          <div class="plan-actions">
            <Link :href="route('admin.plans.edit', plan.id)" class="action-btn edit"><i class="fa-solid fa-pen"></i></Link>
            <button @click="deletePlan(plan)" class="action-btn delete"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!plans?.length" class="empty">Aucun plan configuré.</div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.1rem; font-weight: 700; color: #111827; padding-left: 12px; border-left: 3px solid #6366F1; }
.kh-btn { background: #6366F1; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }

.plans-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
.plan-card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.plan-card.inactive { opacity: 0.6; border-color: #D1D5DB; }
.plan-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.plan-name { font-size: 1.1rem; font-weight: 700; color: #111827; }
.plan-code { background: #EEF2FF; color: #6366F1; padding: 2px 8px; font-size: 0.72rem; font-weight: 600; }
.badge-off { background: #FEE2E2; color: #EF4444; padding: 2px 8px; font-size: 0.72rem; font-weight: 600; margin-left: auto; }

.plan-prices { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #F3F4F6; }
.price-item { text-align: center; }
.price-label { display: block; font-size: 0.7rem; color: #6B7280; margin-bottom: 4px; }
.price-value { font-size: 0.95rem; font-weight: 700; color: #111827; }
.price-value small { font-size: 0.7rem; color: #6B7280; font-weight: 400; }

.plan-limits { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.limit { font-size: 0.78rem; color: #374151; display: flex; align-items: center; gap: 8px; }
.limit i { width: 16px; color: #6366F1; font-size: 0.75rem; }

.plan-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #F3F4F6; }
.sub-count { font-size: 0.75rem; color: #6B7280; }
.plan-actions { display: flex; gap: 6px; }
.action-btn { border: none; padding: 6px 10px; cursor: pointer; font-size: 0.78rem; background: #F3F4F6; color: #374151; }
.action-btn:hover { background: #E5E7EB; }
.action-btn.delete:hover { background: #FEE2E2; color: #EF4444; }
.empty { text-align: center; color: #9CA3AF; padding: 40px; font-size: 0.85rem; }
</style>
