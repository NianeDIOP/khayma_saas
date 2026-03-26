<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ company: Object, allModules: { type: Array, default: () => [] } })

const STATUS_LABELS = {
  active:       { label: 'Actif',        color: '#10B981' },
  trial:        { label: 'Essai',        color: '#F59E0B' },
  grace_period: { label: 'Grâce',        color: '#6366F1' },
  expired:      { label: 'Expiré',       color: '#EF4444' },
  suspended:    { label: 'Suspendu',     color: '#DC2626' },
  cancelled:    { label: 'Annulé',       color: '#6B7280' },
}

const subForm = useForm({ subscription_status: props.company.subscription_status })
const trialDays = ref(14)
const activeModuleIds = ref((props.company.modules || []).map(m => m.id))

function fmtDate(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }
function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0) }

function toggleActive() {
  router.patch(route('admin.companies.toggle', props.company.id))
}
function saveSubscription() {
  subForm.patch(route('admin.companies.subscription', props.company.id))
}
function extendTrial() {
  if (!confirm(`Prolonger l'essai de ${trialDays.value} jours ?`)) return
  router.patch(route('admin.companies.extend-trial', props.company.id), { days: trialDays.value })
}
function syncModules() {
  router.post(route('admin.companies.modules', props.company.id), { module_ids: activeModuleIds.value })
}
function resetPassword() {
  if (!confirm('Réinitialiser le mot de passe du propriétaire ? Un nouveau mot de passe sera généré.')) return
  router.post(route('admin.companies.reset-password', props.company.id))
}
function deleteCompany() {
  if (!confirm(`Supprimer définitivement "${props.company.name}" ? Cette action est irréversible.`)) return
  router.delete(route('admin.companies.destroy', props.company.id))
}
function toggleModuleId(id) {
  const idx = activeModuleIds.value.indexOf(id)
  if (idx === -1) activeModuleIds.value.push(id)
  else activeModuleIds.value.splice(idx, 1)
}
</script>

<template>
  <AdminLayout :title="company.name">
    <Head :title="`Admin · ${company.name}`" />

    <div class="top-bar">
      <Link :href="route('admin.companies.index')" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Entreprises
      </Link>
      <div class="top-actions">
        <Link :href="route('admin.companies.edit', company.id)" class="action-btn edit">
          <i class="fa-solid fa-pen"></i> Modifier
        </Link>
        <button @click="deleteCompany" class="action-btn delete">
          <i class="fa-solid fa-trash"></i> Supprimer
        </button>
      </div>
    </div>

    <div class="detail-grid">
      <!-- Infos générales -->
      <div class="card">
        <div class="card-title">Informations générales</div>
        <dl class="dl">
          <dt>Nom</dt><dd>{{ company.name }}</dd>
          <dt>Slug</dt><dd>{{ company.slug }}</dd>
          <dt>Secteur</dt><dd>{{ company.sector || '—' }}</dd>
          <dt>Email</dt><dd>{{ company.email || '—' }}</dd>
          <dt>Téléphone</dt><dd>{{ company.phone || '—' }}</dd>
          <dt>NINEA</dt><dd>{{ company.ninea || '—' }}</dd>
          <dt>Créée le</dt><dd>{{ fmtDate(company.created_at) }}</dd>
          <dt>Fin d'essai</dt><dd>{{ fmtDate(company.trial_ends_at) }}</dd>
        </dl>
      </div>

      <!-- Abonnement + Actions -->
      <div class="card">
        <div class="card-title">Abonnement & Actions</div>
        <div class="status-row">
          <span class="badge"
            :style="{ background: (STATUS_LABELS[company.subscription_status]?.color || '#6B7280') + '22',
                      color: STATUS_LABELS[company.subscription_status]?.color || '#6B7280' }">
            {{ STATUS_LABELS[company.subscription_status]?.label || company.subscription_status }}
          </span>
        </div>
        <form @submit.prevent="saveSubscription" class="sub-form">
          <select v-model="subForm.subscription_status" class="kh-input">
            <option v-for="(s, k) in STATUS_LABELS" :key="k" :value="k">{{ s.label }}</option>
          </select>
          <button type="submit" class="kh-btn" :disabled="subForm.processing">Enregistrer</button>
        </form>

        <!-- Extend trial -->
        <div class="mini-section">
          <label class="mini-label">Prolonger l'essai</label>
          <div class="inline-form">
            <input v-model.number="trialDays" type="number" min="1" max="90" class="kh-input small" />
            <span class="unit">jours</span>
            <button @click="extendTrial" class="kh-btn-sm">Prolonger</button>
          </div>
        </div>

        <div class="toggle-row">
          <span>Compte actif</span>
          <button @click="toggleActive" :class="['toggle-btn', company.is_active ? 'on' : 'off']">
            {{ company.is_active ? 'Désactiver' : 'Activer' }}
          </button>
        </div>
        <div class="toggle-row" style="margin-top:10px;">
          <span>Mot de passe propriétaire</span>
          <button @click="resetPassword" class="kh-btn-sm reset">
            <i class="fa-solid fa-key"></i> Réinitialiser
          </button>
        </div>
      </div>

      <!-- Modules -->
      <div class="card full">
        <div class="card-title">Modules ({{ company.modules?.length || 0 }})</div>
        <div class="modules-grid">
          <label v-for="mod in allModules" :key="mod.id" class="mod-check" :class="{ checked: activeModuleIds.includes(mod.id) }">
            <input type="checkbox" :checked="activeModuleIds.includes(mod.id)" @change="toggleModuleId(mod.id)" />
            <i :class="mod.icon || 'fa-solid fa-puzzle-piece'" class="mod-ico"></i>
            <span>{{ mod.name }}</span>
          </label>
        </div>
        <button v-if="allModules.length" @click="syncModules" class="kh-btn" style="margin-top:12px;">
          Enregistrer les modules
        </button>
      </div>

      <!-- Utilisateurs -->
      <div class="card full">
        <div class="card-title">Utilisateurs ({{ company.users?.length || 0 }})</div>
        <table class="kh-table">
          <thead>
            <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Rejoint le</th></tr>
          </thead>
          <tbody>
            <tr v-if="!company.users?.length">
              <td colspan="4" class="empty">Aucun utilisateur.</td>
            </tr>
            <tr v-for="u in company.users" :key="u.id">
              <td>{{ u.name }}</td>
              <td>{{ u.email }}</td>
              <td>{{ u.pivot?.role || '—' }}</td>
              <td>{{ u.pivot?.joined_at ? fmtDate(u.pivot.joined_at) : '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Abonnements historique -->
      <div v-if="company.subscriptions?.length" class="card full">
        <div class="card-title">Historique abonnements</div>
        <table class="kh-table">
          <thead>
            <tr><th>Plan</th><th>Module</th><th>Période</th><th>Montant</th><th>Statut</th><th>Début</th><th>Fin</th></tr>
          </thead>
          <tbody>
            <tr v-for="s in company.subscriptions" :key="s.id">
              <td>{{ s.plan?.name || '—' }}</td>
              <td>{{ s.module?.name || '—' }}</td>
              <td>{{ s.billing_period }}</td>
              <td class="amount">{{ fmt(s.amount_paid) }}</td>
              <td>
                <span class="badge" :style="{ background: (STATUS_LABELS[s.status]?.color || '#6B7280') + '22', color: STATUS_LABELS[s.status]?.color || '#6B7280' }">
                  {{ STATUS_LABELS[s.status]?.label || s.status }}
                </span>
              </td>
              <td>{{ fmtDate(s.starts_at) }}</td>
              <td>{{ fmtDate(s.ends_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.back-link { color: #6366F1; font-size: 0.82rem; text-decoration: none; font-weight: 600; }
.top-actions { display: flex; gap: 8px; }
.action-btn { padding: 6px 14px; font-size: 0.78rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
.action-btn.edit { background: #EEF2FF; color: #6366F1; }
.action-btn.delete { background: #FEE2E2; color: #EF4444; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.full { grid-column: 1 / -1; }
.card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.card-title { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #F3F4F6; }
.dl { display: grid; grid-template-columns: 130px 1fr; gap: 6px 12px; font-size: 0.82rem; }
dt { color: #6B7280; font-weight: 500; }
dd { color: #111827; font-weight: 600; margin: 0; }
.badge { display: inline-block; padding: 3px 10px; font-size: 0.78rem; font-weight: 600; }
.status-row { margin-bottom: 12px; }
.sub-form { display: flex; gap: 8px; margin-bottom: 16px; }
.kh-input { padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; flex: 1; }
.kh-input.small { width: 70px; flex: none; }
.kh-btn { background: #6366F1; color: #fff; border: none; padding: 7px 16px; cursor: pointer; font-size: 0.82rem; font-weight: 600; }
.kh-btn:disabled { opacity: 0.6; }
.kh-btn-sm { background: #6366F1; color: #fff; border: none; padding: 5px 12px; cursor: pointer; font-size: 0.78rem; font-weight: 600; }
.kh-btn-sm.reset { background: #F59E0B; }
.mini-section { margin-bottom: 14px; }
.mini-label { font-size: 0.78rem; font-weight: 600; color: #374151; display: block; margin-bottom: 6px; }
.inline-form { display: flex; align-items: center; gap: 6px; }
.unit { font-size: 0.78rem; color: #6B7280; }
.toggle-row { display: flex; align-items: center; justify-content: space-between; }
.toggle-row span { font-size: 0.82rem; color: #374151; font-weight: 500; }
.toggle-btn { border: none; padding: 6px 14px; cursor: pointer; font-size: 0.78rem; font-weight: 600; }
.toggle-btn.on { background: #FEE2E2; color: #EF4444; }
.toggle-btn.off { background: #D1FAE5; color: #10B981; }
.modules-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.mod-check { display: flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #E5E7EB; cursor: pointer; font-size: 0.82rem; color: #374151; }
.mod-check.checked { border-color: #6366F1; background: #EEF2FF; }
.mod-ico { color: #F59E0B; }
.kh-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.kh-table th { background: #F9FAFB; padding: 8px 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.kh-table td { padding: 8px 12px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.amount { font-weight: 700; }
.empty { text-align: center; color: #9CA3AF; padding: 24px; }
@media (max-width: 640px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
