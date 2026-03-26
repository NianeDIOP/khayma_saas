<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ sessions: Object, openSession: Object })
const t = () => route().params._tenant

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) + ' XOF' }
function fmtDt(d) { return d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) : '—' }

const openForm = useForm({ opening_amount: 0, service_id: '', notes: '' })
const closeForm = useForm({ closing_amount: 0, notes: '' })

function openCash() {
  openForm.post(route('app.restaurant.cash-sessions.open', { _tenant: t() }))
}
function closeCash() {
  closeForm.post(route('app.restaurant.cash-sessions.close', { cashSession: props.openSession.id, _tenant: t() }))
}
</script>

<template>
  <AppLayout title="Caisse">
    <Head title="Caisse" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-cash-register" style="color:#10B981"></i> Gestion de caisse</h1>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>

    <div v-if="$page.props.errors?.session" class="flash-error">
      <i class="fa-solid fa-circle-exclamation"></i> {{ $page.props.errors.session }}
    </div>

    <!-- Open/Close cash session -->
    <div class="dual-grid">
      <div class="action-card" v-if="!openSession">
        <div class="card-title"><i class="fa-solid fa-lock-open" style="color:#10B981"></i> Ouvrir la caisse</div>
        <form @submit.prevent="openCash">
          <div class="field">
            <label>Montant initial (XOF)</label>
            <input v-model.number="openForm.opening_amount" type="number" min="0" step="100" required />
          </div>
          <div class="field">
            <label>Notes</label>
            <textarea v-model="openForm.notes" rows="2"></textarea>
          </div>
          <button type="submit" class="btn-open" :disabled="openForm.processing">
            <i class="fa-solid fa-play"></i> Ouvrir
          </button>
        </form>
      </div>

      <div class="action-card" v-if="openSession">
        <div class="card-title"><i class="fa-solid fa-lock" style="color:#EF4444"></i> Fermer la caisse</div>
        <div class="session-info">
          <div class="info-row"><span>Ouverte à :</span> <strong>{{ fmtDt(openSession.opened_at) }}</strong></div>
          <div class="info-row"><span>Montant initial :</span> <strong>{{ fmt(openSession.opening_amount) }}</strong></div>
          <div class="info-row" v-if="openSession.service"><span>Service :</span> <strong>{{ openSession.service.name }}</strong></div>
        </div>
        <form @submit.prevent="closeCash">
          <div class="field">
            <label>Montant en caisse (XOF)</label>
            <input v-model.number="closeForm.closing_amount" type="number" min="0" step="100" required />
          </div>
          <div class="field">
            <label>Notes</label>
            <textarea v-model="closeForm.notes" rows="2"></textarea>
          </div>
          <button type="submit" class="btn-close-cash" :disabled="closeForm.processing">
            <i class="fa-solid fa-stop"></i> Fermer la caisse
          </button>
        </form>
      </div>
    </div>

    <!-- History -->
    <h2 class="section-title">Historique des sessions</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Ouverture</th>
            <th>Fermeture</th>
            <th>Service</th>
            <th>Caissier</th>
            <th>Initial</th>
            <th>Attendu</th>
            <th>Clôturé</th>
            <th>Écart</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="sessions.data.length === 0">
            <td colspan="8" class="empty-row">Aucune session de caisse.</td>
          </tr>
          <tr v-for="s in sessions.data" :key="s.id">
            <td>{{ fmtDt(s.opened_at) }}</td>
            <td>{{ fmtDt(s.closed_at) }}</td>
            <td>{{ s.service?.name || '—' }}</td>
            <td>{{ s.user?.name || '—' }}</td>
            <td>{{ fmt(s.opening_amount) }}</td>
            <td>{{ s.expected_amount != null ? fmt(s.expected_amount) : '—' }}</td>
            <td>{{ s.closing_amount != null ? fmt(s.closing_amount) : '—' }}</td>
            <td :class="s.closing_amount != null && s.expected_amount != null ? (s.closing_amount - s.expected_amount >= 0 ? 'text-green' : 'text-red') : ''">
              {{ s.closing_amount != null && s.expected_amount != null ? fmt(s.closing_amount - s.expected_amount) : '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #10B981; }
.flash-success { background: #D1FAE5; border: 1px solid #6EE7B7; padding: 10px 16px; font-size: 0.82rem; color: #065F46; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.flash-error { background: #FEE2E2; border: 1px solid #FECACA; padding: 10px 16px; font-size: 0.82rem; color: #991B1B; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.dual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
.action-card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.card-title { font-size: 0.88rem; font-weight: 700; color: #111827; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; padding-bottom: 10px; border-bottom: 1px solid #F3F4F6; }
.session-info { margin-bottom: 16px; }
.info-row { font-size: 0.82rem; color: #374151; display: flex; justify-content: space-between; padding: 4px 0; }
.field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.field label { font-size: 0.82rem; font-weight: 600; color: #374151; }
.field input, .field textarea { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.88rem; outline: none; font-family: inherit; }
.field input:focus, .field textarea:focus { border-color: #10B981; }
.btn-open { background: #10B981; color: #fff; padding: 10px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-close-cash { background: #EF4444; color: #fff; padding: 10px 20px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.section-title { font-size: 0.92rem; font-weight: 700; color: #111827; margin-bottom: 12px; }
.table-wrap { background: #fff; border: 1px solid #E5E7EB; overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { background: #F9FAFB; text-align: left; padding: 10px 14px; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty-row { text-align: center; color: #9CA3AF; font-style: italic; padding: 24px 14px !important; }
.text-green { color: #10B981; font-weight: 600; }
.text-red { color: #EF4444; font-weight: 600; }
@media (max-width: 768px) { .dual-grid { grid-template-columns: 1fr; } }
</style>
