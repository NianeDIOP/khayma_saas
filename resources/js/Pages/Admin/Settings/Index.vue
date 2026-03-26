<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ settings: Object })

const form = useForm({
  app_name: props.settings.app_name || '',
  default_currency: props.settings.default_currency || '',
  trial_duration_days: props.settings.trial_duration_days || 14,
  grace_period_days: props.settings.grace_period_days || 3,
  data_retention_days: props.settings.data_retention_days || 365,
  maintenance_mode: props.settings.maintenance_mode || false,
  support_email: props.settings.support_email || '',
})

function submit() {
  form.put(route('admin.settings.update'))
}
</script>

<template>
  <AdminLayout title="Paramètres">
    <Head title="Admin · Paramètres" />

    <div class="toolbar">
      <h1 class="page-title">Paramètres de la plateforme</h1>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <!-- Général -->
        <div class="section">
          <div class="section-title"><i class="fa-solid fa-gear"></i> Général</div>
          <div class="form-row">
            <div class="field">
              <label>Nom de l'application</label>
              <input v-model="form.app_name" type="text" class="kh-input" placeholder="Khayma" />
              <span v-if="form.errors.app_name" class="error">{{ form.errors.app_name }}</span>
            </div>
            <div class="field">
              <label>Devise par défaut</label>
              <select v-model="form.default_currency" class="kh-input">
                <option value="XOF">XOF (Franc CFA)</option>
                <option value="EUR">EUR (Euro)</option>
                <option value="USD">USD (Dollar)</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label>Email de support</label>
            <input v-model="form.support_email" type="email" class="kh-input" placeholder="support@khayma.com" />
            <span v-if="form.errors.support_email" class="error">{{ form.errors.support_email }}</span>
          </div>
        </div>

        <!-- Abonnements -->
        <div class="section">
          <div class="section-title"><i class="fa-solid fa-clock"></i> Abonnements</div>
          <div class="form-row form-row-3">
            <div class="field">
              <label>Durée d'essai (jours)</label>
              <input v-model.number="form.trial_duration_days" type="number" min="0" class="kh-input" />
              <span v-if="form.errors.trial_duration_days" class="error">{{ form.errors.trial_duration_days }}</span>
            </div>
            <div class="field">
              <label>Période de grâce (jours)</label>
              <input v-model.number="form.grace_period_days" type="number" min="0" class="kh-input" />
            </div>
            <div class="field">
              <label>Rétention données (jours)</label>
              <input v-model.number="form.data_retention_days" type="number" min="30" class="kh-input" />
            </div>
          </div>
        </div>

        <!-- Maintenance -->
        <div class="section">
          <div class="section-title"><i class="fa-solid fa-wrench"></i> Maintenance</div>
          <label class="toggle-label">
            <input type="checkbox" v-model="form.maintenance_mode" />
            <span>Mode maintenance activé</span>
          </label>
          <p class="hint">Quand activé, seuls les administrateurs peuvent accéder à la plateforme.</p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-submit" :disabled="form.processing">
            <i class="fa-solid fa-check"></i> Enregistrer les paramètres
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { margin-bottom: 20px; }
.page-title { font-size: 1.1rem; font-weight: 700; color: #111827; padding-left: 12px; border-left: 3px solid #8B5CF6; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 660px; }
.section { margin-bottom: 24px; }
.section-title { font-size: 0.8rem; font-weight: 700; color: #8B5CF6; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #EDE9FE; display: flex; align-items: center; gap: 6px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-row-3 { grid-template-columns: 1fr 1fr 1fr; }
.field { margin-bottom: 12px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.kh-input { width: 100%; padding: 8px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; }
.kh-input:focus { border-color: #8B5CF6; outline: none; }
.error { color: #EF4444; font-size: 0.72rem; }
.toggle-label { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: #374151; cursor: pointer; font-weight: 600; }
.hint { font-size: 0.72rem; color: #9CA3AF; margin-top: 6px; }
.form-actions { padding-top: 16px; border-top: 1px solid #F3F4F6; }
.btn-submit { padding: 8px 20px; background: #8B5CF6; color: #fff; border: none; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
.btn-submit:disabled { opacity: 0.6; }
</style>
