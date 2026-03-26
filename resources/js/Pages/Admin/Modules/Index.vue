<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ modules: Array })

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) }

function toggleModule(mod) {
  router.patch(route('admin.modules.toggle', mod.id))
}
</script>

<template>
  <AdminLayout title="Modules">
    <Head title="Admin · Modules" />

    <div class="toolbar">
      <h1 class="page-title">Modules métier</h1>
      <Link :href="route('admin.modules.create')" class="kh-btn">
        <i class="fa-solid fa-plus"></i> Nouveau module
      </Link>
    </div>

    <div class="modules-grid">
      <div v-for="mod in modules" :key="mod.id" class="mod-card" :class="{ inactive: !mod.is_active }">
        <div class="mod-icon-wrap">
          <i :class="mod.icon || 'fa-solid fa-puzzle-piece'" class="mod-icon"></i>
        </div>
        <div class="mod-body">
          <div class="mod-header">
            <span class="mod-name">{{ mod.name }}</span>
            <span class="mod-code">{{ mod.code }}</span>
          </div>
          <p class="mod-desc">{{ mod.description || 'Aucune description' }}</p>
          <div class="mod-meta">
            <span><i class="fa-solid fa-money-bill"></i> Frais : {{ fmt(mod.installation_fee) }} XOF</span>
            <span><i class="fa-solid fa-building"></i> {{ mod.companies_count || 0 }} entreprise{{ (mod.companies_count || 0) > 1 ? 's' : '' }}</span>
          </div>
        </div>
        <div class="mod-actions">
          <button @click="toggleModule(mod)" :class="['toggle-btn', mod.is_active ? 'on' : 'off']">
            {{ mod.is_active ? 'Actif' : 'Inactif' }}
          </button>
          <Link :href="route('admin.modules.edit', mod.id)" class="action-btn"><i class="fa-solid fa-pen"></i></Link>
        </div>
      </div>
    </div>

    <div v-if="!modules?.length" class="empty">Aucun module configuré.</div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.1rem; font-weight: 700; color: #111827; padding-left: 12px; border-left: 3px solid #F59E0B; }
.kh-btn { background: #F59E0B; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }

.modules-grid { display: flex; flex-direction: column; gap: 12px; }
.mod-card { background: #fff; border: 1px solid #E5E7EB; padding: 18px; display: flex; align-items: center; gap: 16px; }
.mod-card.inactive { opacity: 0.5; }
.mod-icon-wrap { width: 50px; height: 50px; background: #FEF3C7; color: #F59E0B; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; border-radius: 8px; }
.mod-body { flex: 1; }
.mod-header { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.mod-name { font-size: 0.92rem; font-weight: 700; color: #111827; }
.mod-code { background: #FEF3C7; color: #92400E; padding: 1px 8px; font-size: 0.7rem; font-weight: 600; }
.mod-desc { font-size: 0.78rem; color: #6B7280; margin-bottom: 8px; }
.mod-meta { display: flex; gap: 16px; font-size: 0.75rem; color: #374151; }
.mod-meta i { color: #F59E0B; margin-right: 4px; }
.mod-actions { display: flex; flex-direction: column; gap: 6px; align-items: center; }
.toggle-btn { border: none; padding: 5px 12px; font-size: 0.75rem; font-weight: 600; cursor: pointer; }
.toggle-btn.on { background: #D1FAE5; color: #10B981; }
.toggle-btn.off { background: #FEE2E2; color: #EF4444; }
.action-btn { background: #F3F4F6; color: #374151; padding: 6px 10px; font-size: 0.78rem; text-decoration: none; }
.action-btn:hover { background: #E5E7EB; }
.empty { text-align: center; color: #9CA3AF; padding: 40px; font-size: 0.85rem; }
</style>
