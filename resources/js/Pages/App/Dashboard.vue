<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  recentUsers: Array,
  activeModules: Array,
})

const cards = [
  { label: 'Utilisateurs',   value: props.stats.users_count,   icon: 'fa-users',          color: '#3B82F6' },
  { label: 'Modules actifs',  value: props.stats.modules_count, icon: 'fa-puzzle-piece',    color: '#8B5CF6' },
  { label: 'Abonnement',      value: props.stats.subscription,  icon: 'fa-credit-card',     color: '#10B981', isText: true },
  { label: 'Plan actuel',     value: props.stats.active_plan,   icon: 'fa-star',            color: '#F59E0B', isText: true },
]
</script>

<template>
  <AppLayout title="Tableau de bord">
    <Head title="Dashboard" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-gauge-high" style="color:#10B981"></i> Tableau de bord</h1>
    </div>

    <!-- Trial banner -->
    <div v-if="stats.trial_days_left !== null" class="trial-banner">
      <i class="fa-solid fa-hourglass-half"></i>
      <span>Période d'essai : <strong>{{ stats.trial_days_left }} jour(s)</strong> restant(s)</span>
      <a href="#" class="trial-upgrade">Passer à un plan payant</a>
    </div>

    <!-- Stats cards -->
    <div class="stats-grid">
      <div v-for="card in cards" :key="card.label" class="stat-card">
        <div class="stat-icon" :style="{ background: card.color + '18', color: card.color }">
          <i :class="['fa-solid', card.icon]"></i>
        </div>
        <div class="stat-body">
          <div class="stat-value" :class="{ 'stat-value-text': card.isText }">{{ card.value }}</div>
          <div class="stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Sections -->
    <div class="sections-row">
      <!-- Active Modules -->
      <div class="section-card">
        <h2 class="section-title"><i class="fa-solid fa-puzzle-piece" style="color:#8B5CF6"></i> Modules actifs</h2>
        <div v-if="activeModules.length === 0" class="empty-msg">Aucun module activé.</div>
        <div v-for="mod in activeModules" :key="mod.id" class="module-row">
          <i :class="mod.icon" class="module-icon"></i>
          <span class="module-name">{{ mod.name }}</span>
        </div>
      </div>

      <!-- Recent Team Members -->
      <div class="section-card">
        <h2 class="section-title"><i class="fa-solid fa-users" style="color:#3B82F6"></i> Équipe récente</h2>
        <div v-if="recentUsers.length === 0" class="empty-msg">Aucun membre.</div>
        <div v-for="user in recentUsers" :key="user.id" class="user-row">
          <div class="user-avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
          <div class="user-info">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-role">{{ user.role }}</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title {
  font-size: 1.15rem; font-weight: 700; color: #111827;
  padding-left: 12px;
  border-left: 3px solid #10B981;
  display: flex; align-items: center; gap: 10px;
}

/* ── Trial banner ─── */
.trial-banner {
  background: #FEF3C7; border: 1px solid #FDE68A; padding: 12px 18px;
  display: flex; align-items: center; gap: 10px; margin-bottom: 20px;
  font-size: 0.84rem; color: #92400E;
}
.trial-upgrade {
  margin-left: auto; font-weight: 600; color: #D97706;
  text-decoration: underline; white-space: nowrap;
}

/* ── Stats Grid ─── */
.stats-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px; margin-bottom: 28px;
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
.stat-value-text { font-size: 0.95rem; font-weight: 700; text-transform: capitalize; }
.stat-label { font-size: 0.75rem; color: #6B7280; margin-top: 4px; }

/* ── Sections row ─── */
.sections-row {
  display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
}
.section-card {
  background: #fff; border: 1px solid #E5E7EB; padding: 20px;
}
.section-title {
  font-size: 0.88rem; font-weight: 700; color: #111827;
  margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
}
.empty-msg { font-size: 0.82rem; color: #9CA3AF; font-style: italic; }

/* Module rows */
.module-row {
  display: flex; align-items: center; gap: 10px; padding: 8px 0;
  border-bottom: 1px solid #F3F4F6; font-size: 0.84rem; color: #374151;
}
.module-row:last-child { border-bottom: none; }
.module-icon { width: 20px; text-align: center; font-size: 1rem; color: #6B7280; }
.module-name { font-weight: 500; }

/* User rows */
.user-row {
  display: flex; align-items: center; gap: 10px; padding: 8px 0;
  border-bottom: 1px solid #F3F4F6;
}
.user-row:last-child { border-bottom: none; }
.user-avatar {
  width: 32px; height: 32px; background: #EEF2FF; color: #4F46E5;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.82rem; flex-shrink: 0;
}
.user-info { flex: 1; }
.user-name { font-size: 0.84rem; font-weight: 600; color: #111827; }
.user-role { font-size: 0.72rem; color: #6B7280; text-transform: capitalize; }
</style>
