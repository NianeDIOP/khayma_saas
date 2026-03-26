<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ services: Array })
const t = () => route().params._tenant

function destroy(id) {
  if (!confirm('Supprimer ce service ?')) return
  router.delete(route('app.restaurant.services.destroy', { service: id, _tenant: t() }))
}
</script>

<template>
  <AppLayout title="Services (horaires)">
    <Head title="Services" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-clock" style="color:#F59E0B"></i> Services (horaires)</h1>
      <Link :href="route('app.restaurant.services.create', { _tenant: t() })" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau service
      </Link>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Commandes</th>
            <th>Statut</th>
            <th style="width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="services.length === 0">
            <td colspan="6" class="empty-row">Aucun service configuré.</td>
          </tr>
          <tr v-for="s in services" :key="s.id">
            <td class="cell-name">{{ s.name }}</td>
            <td>{{ s.start_time?.substring(0,5) }}</td>
            <td>{{ s.end_time?.substring(0,5) }}</td>
            <td>{{ s.orders_count }}</td>
            <td><span class="status-badge" :class="s.is_active ? 'active' : 'inactive'">{{ s.is_active ? 'Actif' : 'Inactif' }}</span></td>
            <td class="actions-cell">
              <Link :href="route('app.restaurant.services.edit', { service: s.id, _tenant: t() })" class="btn-icon" title="Modifier">
                <i class="fa-solid fa-pen"></i>
              </Link>
              <button @click="destroy(s.id)" class="btn-icon btn-danger" title="Supprimer">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #F59E0B; }
.btn-primary { background: #F59E0B; color: #fff; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.cell-name { font-weight: 600; }
.status-badge { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; }
.status-badge.active { background: #D1FAE522; color: #10B981; }
.status-badge.inactive { background: #FEE2E222; color: #EF4444; }
.actions-cell { display: flex; gap: 6px; }
.btn-icon { background: none; border: 1px solid #E5E7EB; padding: 5px 8px; cursor: pointer; color: #6B7280; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; }
.btn-icon:hover { background: #F3F4F6; color: #111827; }
.btn-danger:hover { background: #FEE2E2; color: #DC2626; border-color: #FECACA; }
</style>
