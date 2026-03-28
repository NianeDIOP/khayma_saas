<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ settings: Object })

const truthyValues = ['1', 1, true, 'true']

const form = useForm({
  app_name: props.settings.app_name || '',
  default_currency: props.settings.default_currency || '',
  trial_duration_days: props.settings.trial_duration_days || 14,
  grace_period_days: props.settings.grace_period_days || 3,
  data_retention_days: props.settings.data_retention_days || 365,
  maintenance_mode: truthyValues.includes(props.settings.maintenance_mode),
  support_email: props.settings.support_email || '',
  mail_mailer: props.settings.mail_mailer || 'log',
  mail_host: props.settings.mail_host || '',
  mail_port: props.settings.mail_port || 587,
  mail_username: props.settings.mail_username || '',
  mail_password: props.settings.mail_password || '',
  mail_from_address: props.settings.mail_from_address || '',
  mail_from_name: props.settings.mail_from_name || 'Khayma',
  sms_provider: props.settings.sms_provider || 'log',
  sms_api_url: props.settings.sms_api_url || '',
  sms_api_token: props.settings.sms_api_token || '',
  sms_from: props.settings.sms_from || 'KHAYMA',
  paydunya_mode: props.settings.paydunya_mode || 'log',
  paydunya_env: props.settings.paydunya_env || 'test',
  paydunya_master_key: props.settings.paydunya_master_key || '',
  paydunya_private_key: props.settings.paydunya_private_key || '',
  paydunya_token: props.settings.paydunya_token || '',
})

function submit() {
  form
    .transform((data) => ({
      ...data,
      maintenance_mode: data.maintenance_mode ? '1' : '0',
      mail_port: data.mail_port ? Number(data.mail_port) : null,
    }))
    .put(route('admin.settings.update'))
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

        <!-- Email / SMTP -->
        <div class="section">
          <div class="section-title"><i class="fa-solid fa-envelope"></i> Email (SMTP)</div>
          <div class="form-row form-row-3">
            <div class="field">
              <label>Mailer</label>
              <select v-model="form.mail_mailer" class="kh-input">
                <option value="log">log</option>
                <option value="smtp">smtp</option>
                <option value="sendmail">sendmail</option>
              </select>
              <span v-if="form.errors.mail_mailer" class="error">{{ form.errors.mail_mailer }}</span>
            </div>
            <div class="field">
              <label>Port SMTP</label>
              <input v-model.number="form.mail_port" type="number" min="1" class="kh-input" placeholder="587" />
              <span v-if="form.errors.mail_port" class="error">{{ form.errors.mail_port }}</span>
            </div>
            <div class="field">
              <label>Host SMTP</label>
              <input v-model="form.mail_host" type="text" class="kh-input" placeholder="smtp.example.com" />
              <span v-if="form.errors.mail_host" class="error">{{ form.errors.mail_host }}</span>
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Nom d'utilisateur SMTP</label>
              <input v-model="form.mail_username" type="text" class="kh-input" placeholder="noreply@example.com" />
              <span v-if="form.errors.mail_username" class="error">{{ form.errors.mail_username }}</span>
            </div>
            <div class="field">
              <label>Mot de passe SMTP</label>
              <input v-model="form.mail_password" type="password" class="kh-input" placeholder="********" autocomplete="new-password" />
              <span v-if="form.errors.mail_password" class="error">{{ form.errors.mail_password }}</span>
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Email expéditeur</label>
              <input v-model="form.mail_from_address" type="email" class="kh-input" placeholder="noreply@khayma.sn" />
              <span v-if="form.errors.mail_from_address" class="error">{{ form.errors.mail_from_address }}</span>
            </div>
            <div class="field">
              <label>Nom expéditeur</label>
              <input v-model="form.mail_from_name" type="text" class="kh-input" placeholder="Khayma SaaS" />
              <span v-if="form.errors.mail_from_name" class="error">{{ form.errors.mail_from_name }}</span>
            </div>
          </div>
          <p class="hint">En mode <strong>log</strong>, les emails sont écrits dans <code>storage/logs/laravel.log</code>.</p>
        </div>

        <!-- SMS -->
        <div class="section">
          <div class="section-title"><i class="fa-solid fa-comment-sms"></i> SMS</div>
          <div class="form-row form-row-3">
            <div class="field">
              <label>Provider</label>
              <select v-model="form.sms_provider" class="kh-input">
                <option value="log">log</option>
                <option value="fake">fake</option>
                <option value="api">api</option>
              </select>
              <span v-if="form.errors.sms_provider" class="error">{{ form.errors.sms_provider }}</span>
            </div>
            <div class="field">
              <label>Expéditeur SMS</label>
              <input v-model="form.sms_from" type="text" class="kh-input" placeholder="KHAYMA" maxlength="20" />
              <span v-if="form.errors.sms_from" class="error">{{ form.errors.sms_from }}</span>
            </div>
            <div class="field">
              <label>API URL</label>
              <input v-model="form.sms_api_url" type="url" class="kh-input" placeholder="https://api.provider.com/sms/send" />
              <span v-if="form.errors.sms_api_url" class="error">{{ form.errors.sms_api_url }}</span>
            </div>
          </div>
          <div class="field">
            <label>API Token</label>
            <input v-model="form.sms_api_token" type="password" class="kh-input" placeholder="token" autocomplete="new-password" />
            <span v-if="form.errors.sms_api_token" class="error">{{ form.errors.sms_api_token }}</span>
          </div>
          <p class="hint" v-if="form.sms_provider === 'api'">Pour le mode API, renseigner URL + token.</p>
          <p class="hint" v-else>En mode <strong>{{ form.sms_provider }}</strong>, aucun SMS réel n'est envoyé.</p>
        </div>

        <!-- PayDunya -->
        <div class="section">
          <div class="section-title"><i class="fa-solid fa-credit-card"></i> PayDunya</div>
          <div class="form-row form-row-3">
            <div class="field">
              <label>Mode</label>
              <select v-model="form.paydunya_mode" class="kh-input">
                <option value="log">log</option>
                <option value="fake">fake</option>
                <option value="api">api</option>
              </select>
              <span v-if="form.errors.paydunya_mode" class="error">{{ form.errors.paydunya_mode }}</span>
            </div>
            <div class="field">
              <label>Environnement</label>
              <select v-model="form.paydunya_env" class="kh-input">
                <option value="test">Sandbox / Test</option>
                <option value="live">Production / Live</option>
              </select>
              <span v-if="form.errors.paydunya_env" class="error">{{ form.errors.paydunya_env }}</span>
            </div>
            <div class="field">
              <label>Master Key</label>
              <input v-model="form.paydunya_master_key" type="password" class="kh-input" placeholder="PAYDUNYA-MASTER-KEY" autocomplete="new-password" />
              <span v-if="form.errors.paydunya_master_key" class="error">{{ form.errors.paydunya_master_key }}</span>
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Private Key</label>
              <input v-model="form.paydunya_private_key" type="password" class="kh-input" placeholder="PAYDUNYA-PRIVATE-KEY" autocomplete="new-password" />
              <span v-if="form.errors.paydunya_private_key" class="error">{{ form.errors.paydunya_private_key }}</span>
            </div>
            <div class="field">
              <label>Token</label>
              <input v-model="form.paydunya_token" type="password" class="kh-input" placeholder="PAYDUNYA-TOKEN" autocomplete="new-password" />
              <span v-if="form.errors.paydunya_token" class="error">{{ form.errors.paydunya_token }}</span>
            </div>
          </div>
          <p class="hint" v-if="form.paydunya_mode === 'api'">Mode <strong>api</strong> — les paiements réels seront traités via l'environnement <strong>{{ form.paydunya_env }}</strong>.</p>
          <p class="hint" v-else>En mode <strong>{{ form.paydunya_mode }}</strong>, aucun paiement réel n'est traité.</p>
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
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 980px; }
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

@media (max-width: 900px) {
  .form-row,
  .form-row-3 { grid-template-columns: 1fr; }
}
</style>
