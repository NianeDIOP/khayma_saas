<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ company: Object })

const STATUS_LABELS = {
  active:       { label: 'Actif',        color: '#10B981' },
  trial:        { label: 'Essai',        color: '#F59E0B' },
  grace_period: { label: 'Grâce',        color: '#6366F1' },
  expired:      { label: 'Expiré',       color: '#EF4444' },
  suspended:    { label: 'Suspendu',     color: '#DC2626' },
  cancelled:    { label: 'Annulé',       color: '#6B7280' },
}

const subForm = useForm({ subscription_status: props.company.subscription_status })

function toggleActive() {
  router.patch(route('admin.companies.toggle', props.company.id))
}
function saveSubscription() {
  subForm.patch(route('admin.companies.subscription', props.company.id))
}
</script>

<template>
  <AdminLayout :title="company.name">
    <Head :title="`Admin · ${company.name}`" />

    <div class="back-link">
      <Link :href="route('admin.companies.index')">
        <i class="fa-solid fa-arrow-left"></i> Retour aux entreprises
      </Link>
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
          <dt>Créée le</dt><dd>{{ new Date(company.created_at).toLocaleDateString('fr-FR') }}</dd>
          <dt>Fin d'essai</dt><dd>{{ company.trial_ends_at ? new Date(company.trial_ends_at).toLocaleDateString('fr-FR') : '—' }}</dd>
        </dl>
      </div>

      <!-- Abonnement -->
      <div class="card">
        <div class="card-title">Abonnement</div>
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

        <div class="toggle-row">
          <span>Compte actif</span>
          <button @click="toggleActive"
            :class="['toggle-btn', company.is_active ? 'on' : 'off']">
            {{ company.is_active ? 'Désactiver' : 'Activer' }}
          </button>
        </div>
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
              <td>{{ u.pivot?.joined_at ? new Date(u.pivot.joined_at).toLocaleDateString('fr-FR') : '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.back-link { margin-bottom: 20px; }
.back-link a { color: #6366F1; font-size: 0.82rem; text-decoration: none; font-weight: 600; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.full { grid-column: 1 / -1; }
.card { background: #fff; border: 1px solid #E5E7EB; padding: 20px; }
.card-title { font-size: 0.85rem; font-weight: 700; color: #111827; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #F3F4F6; }
.dl { display: grid; grid-template-columns: 130px 1fr; gap: 6px 12px; font-size: 0.82rem; }
dt { color: #6B7280; font-weight: 500; }
dd { color: #111827; font-weight: 600; margin: 0; }
.badge { display: inline-block; padding: 3px 10px; font-size: 0.78rem; font-weight: 600; margin-bottom: 12px; }
.sub-form { display: flex; gap: 8px; margin-bottom: 16px; }
.kh-input { padding: 7px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; flex: 1; }
.kh-btn { background: #6366F1; color: #fff; border: none; padding: 7px 16px; cursor: pointer; font-size: 0.82rem; font-weight: 600; }
.kh-btn:disabled { opacity: 0.6; }
.toggle-row { display: flex; align-items: center; justify-content: space-between; }
.toggle-row span { font-size: 0.82rem; color: #374151; font-weight: 500; }
.toggle-btn { border: none; padding: 6px 14px; cursor: pointer; font-size: 0.78rem; font-weight: 600; }
.toggle-btn.on { background: #FEE2E2; color: #EF4444; }
.toggle-btn.off { background: #D1FAE5; color: #10B981; }
.kh-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.kh-table th { background: #F9FAFB; padding: 8px 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; }
.kh-table td { padding: 8px 12px; border-bottom: 1px solid #F3F4F6; color: #374151; }
.empty { text-align: center; color: #9CA3AF; padding: 24px; }
@media (max-width: 640px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
