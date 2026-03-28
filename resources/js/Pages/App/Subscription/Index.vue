<script setup>
import { ref } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  plans: Array,
  currentSubscription: Object,
})

const periods = [
  { key: 'monthly',   label: 'Mensuel' },
  { key: 'quarterly', label: 'Trimestriel' },
  { key: 'yearly',    label: 'Annuel' },
]

const selectedPeriod = ref('monthly')
const page = usePage()
const tenantSlug = page.props.currentCompany?.slug

function priceForPeriod(plan) {
  const map = {
    monthly:   plan.price_monthly,
    quarterly: plan.price_quarterly,
    yearly:    plan.price_yearly,
  }
  return new Intl.NumberFormat('fr-SN').format(map[selectedPeriod.value]) + ' XOF'
}

function savings(plan) {
  if (selectedPeriod.value === 'quarterly') {
    const monthly3 = plan.price_monthly * 3
    const diff = monthly3 - plan.price_quarterly
    return diff > 0 ? Math.round((diff / monthly3) * 100) : null
  }
  if (selectedPeriod.value === 'yearly') {
    const monthly12 = plan.price_monthly * 12
    const diff = monthly12 - plan.price_yearly
    return diff > 0 ? Math.round((diff / monthly12) * 100) : null
  }
  return null
}

function initiateUrl(plan) {
  return `/app/payment/initiate/${plan.id}/${selectedPeriod.value}?_tenant=${tenantSlug}`
}

function statusLabel(status) {
  const map = {
    active:       { text: 'Actif',       cls: 'sub-active' },
    trial:        { text: 'Essai',        cls: 'sub-trial' },
    grace_period: { text: 'Grâce',        cls: 'sub-grace' },
    expired:      { text: 'Expiré',       cls: 'sub-expired' },
    cancelled:    { text: 'Annulé',       cls: 'sub-expired' },
  }
  return map[status] ?? { text: status, cls: '' }
}
</script>

<template>
  <AppLayout title="Abonnement">
    <Head title="Abonnement &amp; Paiement" />

    <!-- Current subscription banner -->
    <div v-if="currentSubscription" class="sub-banner">
      <div class="sub-banner-left">
        <i class="fa-solid fa-circle-check sub-icon"></i>
        <div>
          <div class="sub-banner-title">Abonnement actuel : <strong>{{ currentSubscription.plan?.name }}</strong></div>
          <div class="sub-banner-meta">
            Expire le {{ new Date(currentSubscription.ends_at).toLocaleDateString('fr-SN') }}
            &nbsp;·&nbsp;
            <span :class="['sub-status', statusLabel(currentSubscription.status).cls]">
              {{ statusLabel(currentSubscription.status).text }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Period switcher -->
    <div class="period-tabs">
      <button
        v-for="p in periods"
        :key="p.key"
        :class="['period-tab', { active: selectedPeriod === p.key }]"
        @click="selectedPeriod = p.key"
      >
        {{ p.label }}
        <span v-if="p.key !== 'monthly' && plans.length" class="tab-saving">
          <template v-if="savings(plans[0])">-{{ savings(plans[0]) }}%</template>
        </span>
      </button>
    </div>

    <!-- Plans grid -->
    <div class="plans-grid">
      <div v-for="plan in plans" :key="plan.id" class="plan-card">
        <div class="plan-name">{{ plan.name }}</div>
        <div class="plan-price">{{ priceForPeriod(plan) }}</div>
        <div class="plan-period">/ {{ { monthly: 'mois', quarterly: '3 mois', yearly: 'an' }[selectedPeriod] }}</div>

        <ul class="plan-features">
          <li><i class="fa-solid fa-box-open"></i> {{ plan.max_products === 0 ? 'Produits illimités' : plan.max_products + ' produits max' }}</li>
          <li><i class="fa-solid fa-users"></i> {{ plan.max_users + ' utilisateurs' }}</li>
          <li><i class="fa-solid fa-hard-drive"></i> {{ plan.max_storage_gb + ' Go stockage' }}</li>
          <li><i class="fa-solid fa-receipt"></i> {{ plan.max_transactions_month === 0 ? 'Transactions illimitées' : plan.max_transactions_month + ' transactions/mois' }}</li>
        </ul>

        <a
          :href="initiateUrl(plan)"
          class="plan-cta"
        >
          <i class="fa-solid fa-credit-card"></i>
          Souscrire avec PayDunya
        </a>

        <p class="plan-methods">
          <img src="https://img.icons8.com/?size=20&id=85280&format=png" alt="Wave" title="Wave" />
          <img src="https://img.icons8.com/?size=20&id=85280&format=png" alt="Orange Money" title="Orange Money" />
          Wave · Orange Money · Free Money
        </p>
      </div>
    </div>

    <div v-if="!plans.length" class="empty-plans">
      <i class="fa-solid fa-circle-info"></i>
      Aucun plan disponible pour le moment. Contactez le support.
    </div>
  </AppLayout>
</template>

<style scoped>
.sub-banner {
  display: flex; align-items: center; justify-content: space-between;
  background: #F0FDF4; border: 1px solid #BBF7D0; padding: 14px 18px; margin-bottom: 24px;
}
.sub-banner-left { display: flex; align-items: center; gap: 12px; }
.sub-icon { font-size: 1.4rem; color: #10B981; }
.sub-banner-title { font-size: 0.88rem; color: #111827; font-weight: 600; }
.sub-banner-meta { font-size: 0.76rem; color: #6B7280; margin-top: 2px; }
.sub-status { font-weight: 700; }
.sub-active  { color: #10B981; }
.sub-trial   { color: #F59E0B; }
.sub-grace   { color: #F97316; }
.sub-expired { color: #EF4444; }

.period-tabs { display: flex; gap: 6px; margin-bottom: 20px; }
.period-tab {
  padding: 7px 18px; border: 1px solid #D1D5DB; background: #fff;
  font-size: 0.8rem; font-weight: 600; cursor: pointer; color: #4B5563;
  display: flex; align-items: center; gap: 6px; transition: all 0.15s;
}
.period-tab.active { border-color: #10B981; background: #10B981; color: #fff; }
.tab-saving { background: #D1FAE5; color: #065F46; font-size: 0.68rem; padding: 2px 5px; border-radius: 4px; }
.period-tab.active .tab-saving { background: rgba(255,255,255,0.25); color: #fff; }

.plans-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
@media (max-width: 900px) { .plans-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 600px) { .plans-grid { grid-template-columns: 1fr; } }

.plan-card {
  background: #fff; border: 1px solid #E5E7EB; padding: 20px 18px;
  display: flex; flex-direction: column; gap: 6px;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.plan-card:hover { border-color: #10B981; box-shadow: 0 2px 12px rgba(16,185,129,0.08); }

.plan-name { font-size: 0.95rem; font-weight: 700; color: #111827; }
.plan-price { font-size: 1.6rem; font-weight: 800; color: #10B981; margin-top: 4px; }
.plan-period { font-size: 0.74rem; color: #9CA3AF; margin-top: -4px; margin-bottom: 12px; }

.plan-features { list-style: none; padding: 0; margin: 0 0 16px; display: flex; flex-direction: column; gap: 7px; }
.plan-features li { font-size: 0.78rem; color: #374151; display: flex; align-items: center; gap: 7px; }
.plan-features li i { width: 14px; color: #10B981; flex-shrink: 0; }

.plan-cta {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  padding: 9px 0; background: #10B981; color: #fff;
  font-size: 0.82rem; font-weight: 700; text-decoration: none;
  border: none; cursor: pointer; transition: background 0.15s; margin-top: auto;
}
.plan-cta:hover { background: #059669; }

.plan-methods { font-size: 0.68rem; color: #9CA3AF; text-align: center; margin-top: 6px; display: flex; align-items: center; justify-content: center; gap: 4px; }
.plan-methods img { width: 0; height: 0; } /* hide broken icons, keep text */

.empty-plans {
  text-align: center; padding: 48px; color: #6B7280; font-size: 0.88rem;
  border: 1px dashed #D1D5DB; display: flex; align-items: center; justify-content: center; gap: 8px;
}
</style>
