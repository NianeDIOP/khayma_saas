<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ messages: Object, unreadCount: Number })

function deleteMsg(msg) {
  if (!confirm('Supprimer ce message ?')) return
  router.delete(route('admin.contact-messages.destroy', msg.id))
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <AdminLayout title="Messages de contact">
    <Head title="Admin · Messages" />

    <div class="toolbar">
      <h1 class="page-title">
        Messages de contact
        <span v-if="unreadCount" class="unread-badge">{{ unreadCount }} non lu{{ unreadCount > 1 ? 's' : '' }}</span>
      </h1>
    </div>

    <div class="kh-card">
      <table class="kh-table">
        <thead>
          <tr>
            <th style="width:6px"></th>
            <th>Nom</th>
            <th>Email</th>
            <th>Sujet</th>
            <th style="width:140px">Date</th>
            <th style="width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="msg in messages.data" :key="msg.id" :class="{ unread: !msg.is_read }">
            <td><span v-if="!msg.is_read" class="dot-unread"></span></td>
            <td class="fw-600">{{ msg.name }}</td>
            <td>{{ msg.email }}</td>
            <td>{{ msg.subject || '—' }}</td>
            <td>{{ formatDate(msg.created_at) }}</td>
            <td>
              <div class="actions">
                <Link :href="route('admin.contact-messages.show', msg.id)" class="action-btn view"><i class="fa-solid fa-eye"></i></Link>
                <button class="action-btn delete" @click="deleteMsg(msg)"><i class="fa-solid fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <tr v-if="!messages.data.length">
            <td colspan="6" class="text-center text-muted" style="padding:2rem">Aucun message reçu.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.35rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; }
.unread-badge { background: #EF4444; color: #fff; font-size: 0.72rem; padding: 0.2rem 0.65rem; font-weight: 600; }
.kh-card { background: #fff; border: 1px solid #E2E8F0; }
.kh-table { width: 100%; border-collapse: collapse; }
.kh-table th { background: #F8FAFC; padding: 0.75rem 1rem; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748B; text-align: left; border-bottom: 1px solid #E2E8F0; }
.kh-table td { padding: 0.75rem 1rem; font-size: 0.88rem; border-bottom: 1px solid #F1F5F9; }
.fw-600 { font-weight: 600; }
tr.unread { background: #F0FDF4; }
.dot-unread { display: inline-block; width: 8px; height: 8px; background: #10B981; border-radius: 50%; }
.text-center { text-align: center; }
.text-muted { color: #94A3B8; }
.actions { display: flex; gap: 0.35rem; }
.action-btn { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #fff; cursor: pointer; font-size: 0.78rem; color: #64748B; transition: all 0.15s; text-decoration: none; }
.action-btn.view:hover { color: #3B82F6; border-color: #3B82F6; }
.action-btn.delete:hover { color: #EF4444; border-color: #EF4444; }
</style>
