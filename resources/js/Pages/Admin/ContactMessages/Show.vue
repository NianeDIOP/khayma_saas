<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ message: Object })

function deleteMsg() {
  if (!confirm('Supprimer ce message ?')) return
  router.delete(route('admin.contact-messages.destroy', props.message.id))
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <AdminLayout title="Message">
    <Head title="Admin · Message" />

    <div class="toolbar">
      <Link :href="route('admin.contact-messages.index')" class="kh-btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
      <button class="kh-btn-danger" @click="deleteMsg">
        <i class="fa-solid fa-trash"></i> Supprimer
      </button>
    </div>

    <div class="kh-card msg-detail">
      <div class="msg-header">
        <div class="msg-from">
          <div class="msg-avatar">{{ message.name.charAt(0).toUpperCase() }}</div>
          <div>
            <div class="msg-name">{{ message.name }}</div>
            <div class="msg-email">{{ message.email }} <span v-if="message.phone">· {{ message.phone }}</span></div>
          </div>
        </div>
        <div class="msg-date">{{ formatDate(message.created_at) }}</div>
      </div>

      <div v-if="message.subject" class="msg-subject">{{ message.subject }}</div>

      <div class="msg-body">{{ message.message }}</div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.kh-btn-outline { display: inline-flex; align-items: center; gap: 0.4rem; background: #fff; color: #64748B; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 600; border: 1px solid #E2E8F0; cursor: pointer; text-decoration: none; }
.kh-btn-danger { display: inline-flex; align-items: center; gap: 0.4rem; background: #FEF2F2; color: #EF4444; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 600; border: 1px solid #FECACA; cursor: pointer; }
.kh-card { background: #fff; border: 1px solid #E2E8F0; }
.msg-detail { padding: 2rem; }
.msg-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #F1F5F9; }
.msg-from { display: flex; align-items: center; gap: 0.85rem; }
.msg-avatar { width: 44px; height: 44px; background: #10B981; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.1rem; font-weight: 700; }
.msg-name { font-size: 1rem; font-weight: 700; }
.msg-email { font-size: 0.85rem; color: #64748B; }
.msg-date { font-size: 0.8rem; color: #94A3B8; }
.msg-subject { font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; }
.msg-body { font-size: 0.95rem; line-height: 1.8; color: #334155; white-space: pre-wrap; }
</style>
