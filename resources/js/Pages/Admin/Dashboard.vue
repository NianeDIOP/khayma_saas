<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  stats: Object,
})

const cards = [
  { label: 'Entreprises total',   value: props.stats.companies_total,   icon: 'fa-building',        color: '#3B82F6' },
  { label: 'Actives',             value: props.stats.companies_active,  icon: 'fa-circle-check',    color: '#10B981' },
  { label: 'En essai',            value: props.stats.companies_trial,   icon: 'fa-hourglass-half',  color: '#F59E0B' },
  { label: 'Expirées / Suspendues', value: props.stats.companies_expired, icon: 'fa-circle-xmark',  color: '#EF4444' },
  { label: 'Utilisateurs',        value: props.stats.users_total,       icon: 'fa-users',           color: '#8B5CF6' },
  { label: 'Super admins',        value: props.stats.users_admin,       icon: 'fa-user-shield',     color: '#D97706' },
]
</script>

<template>
  <AdminLayout title="Tableau de bord Admin">
    <Head title="Admin · Dashboard" />

    <h1 class="page-title">Vue d'ensemble</h1>

    <div class="stats-grid">
      <div v-for="card in cards" :key="card.label" class="stat-card">
        <div class="stat-icon" :style="{ background: card.color + '18', color: card.color }">
          <i :class="['fa-solid', card.icon]"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value">{{ card.value }}</div>
          <div class="stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <Link :href="route('admin.companies.index')" class="ql-btn">
        <i class="fa-solid fa-building"></i> Gérer les entreprises
      </Link>
      <Link :href="route('admin.users.index')" class="ql-btn ql-btn-alt">
        <i class="fa-solid fa-users"></i> Gérer les utilisateurs
      </Link>
    </div>
  </AdminLayout>
</template>

<style scoped>
.page-title {
  font-size: 1.15rem; font-weight: 700; color: #111827;
  margin-bottom: 24px; padding-left: 12px;
  border-left: 3px solid #6366F1;
}
.stats-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px; margin-bottom: 32px;
}
.stat-card {
  background: #fff; border: 1px solid #E5E7EB;
  padding: 20px 18px; display: flex; align-items: center; gap: 14px;
}
.stat-icon {
  width: 44px; height: 44px; display: flex; align-items: center;
  justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
.stat-value { font-size: 1.5rem; font-weight: 800; color: #111827; line-height: 1; }
.stat-label { font-size: 0.75rem; color: #6B7280; margin-top: 4px; }
.quick-links { display: flex; gap: 12px; flex-wrap: wrap; }
.ql-btn {
  display: inline-flex; align-items: center; gap: 8px;
  background: #6366F1; color: #fff; padding: 10px 18px;
  font-size: 0.82rem; font-weight: 600; text-decoration: none;
}
.ql-btn-alt { background: #374151; }
</style>
