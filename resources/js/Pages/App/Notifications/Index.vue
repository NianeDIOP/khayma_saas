<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ notifications: Object })

const t = () => new URLSearchParams(window.location.search).get('_tenant') || ''

function markRead(id) {
    router.post(route('app.notifications.read', { notification: id, _tenant: t() }))
}
function markAllRead() {
    router.post(route('app.notifications.read-all', { _tenant: t() }))
}

const typeIcon = { alert: 'fa-solid fa-triangle-exclamation', info: 'fa-solid fa-circle-info', success: 'fa-solid fa-circle-check' }
const typeColor = { alert: '#EF4444', info: '#3B82F6', success: '#10B981' }
</script>

<template>
  <AppLayout title="Notifications">
    <Head title="Notifications" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-bell" style="color:#F59E0B"></i> Notifications</h1>
      <button v-if="notifications.data.length" @click="markAllRead" class="btn-back" style="cursor:pointer;border:none;background:none;">
        <i class="fa-solid fa-check-double"></i> Tout marquer comme lu
      </button>
    </div>

    <div v-if="!notifications.data.length" style="text-align:center;padding:60px 20px;color:#9CA3AF;">
      <i class="fa-solid fa-bell-slash" style="font-size:2rem;margin-bottom:12px;display:block;"></i>
      Aucune notification
    </div>

    <div v-else style="display:flex;flex-direction:column;gap:6px;max-width:700px;">
      <div v-for="n in notifications.data" :key="n.id"
           :style="{ background: n.is_read ? '#FFFFFF' : '#FEF3C7', border: '1px solid ' + (n.is_read ? '#E5E7EB' : '#FDE68A'), padding: '14px 16px', display: 'flex', alignItems: 'flex-start', gap: '12px', cursor: n.is_read ? 'default' : 'pointer' }"
           @click="!n.is_read && markRead(n.id)">
        <i :class="typeIcon[n.type] || 'fa-solid fa-circle-info'" :style="{ color: typeColor[n.type] || '#3B82F6', marginTop: '2px', fontSize: '1rem' }"></i>
        <div style="flex:1;min-width:0;">
          <div style="font-size:0.85rem;font-weight:600;color:#111827;">{{ n.title }}</div>
          <div v-if="n.body" style="font-size:0.78rem;color:#6B7280;margin-top:2px;">{{ n.body }}</div>
          <div style="font-size:0.68rem;color:#9CA3AF;margin-top:4px;">{{ new Date(n.created_at).toLocaleString('fr-FR') }}</div>
        </div>
        <span v-if="!n.is_read" style="width:8px;height:8px;border-radius:50%;background:#F59E0B;flex-shrink:0;margin-top:6px;"></span>
      </div>
    </div>

    <div v-if="notifications.links && notifications.last_page > 1" style="margin-top:16px;display:flex;gap:4px;">
      <Link v-for="link in notifications.links" :key="link.label"
            :href="link.url || '#'" v-html="link.label"
            :style="{ padding: '6px 12px', fontSize: '0.78rem', border: '1px solid #E5E7EB', background: link.active ? '#F59E0B' : '#FFF', color: link.active ? '#FFF' : '#374151', pointerEvents: link.url ? 'auto' : 'none', opacity: link.url ? 1 : 0.4 }" />
    </div>
  </AppLayout>
</template>
