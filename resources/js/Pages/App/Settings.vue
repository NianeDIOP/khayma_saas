<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ company: Object })

const page = usePage()
const tenant = page.props.currentCompany?.slug

const form = useForm({
  name:            props.company.name,
  email:           props.company.email || '',
  phone:           props.company.phone || '',
  address:         props.company.address || '',
  sector:          props.company.sector || '',
  ninea:           props.company.ninea || '',
  currency:        props.company.currency,
  timezone:        props.company.timezone,
  primary_color:   props.company.primary_color,
  secondary_color: props.company.secondary_color,
})

function submit() {
  form.put(route('app.settings.update', { _tenant: tenant }))
}
</script>

<template>
  <AppLayout title="Paramètres">
    <Head title="Paramètres" />

    <h1 class="page-title">Paramètres de l'entreprise</h1>

    <form @submit.prevent="submit" class="settings-form">
      <!-- Identité -->
      <fieldset class="form-section">
        <legend>Identité</legend>
        <div class="form-grid">
          <div class="form-group full">
            <label>Nom de l'entreprise</label>
            <input v-model="form.name" type="text" required />
            <span v-if="form.errors.name" class="form-error">{{ form.errors.name }}</span>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" />
            <span v-if="form.errors.email" class="form-error">{{ form.errors.email }}</span>
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input v-model="form.phone" type="text" />
            <span v-if="form.errors.phone" class="form-error">{{ form.errors.phone }}</span>
          </div>
          <div class="form-group full">
            <label>Adresse</label>
            <input v-model="form.address" type="text" />
            <span v-if="form.errors.address" class="form-error">{{ form.errors.address }}</span>
          </div>
          <div class="form-group">
            <label>Secteur</label>
            <select v-model="form.sector">
              <option value="">— Choisir —</option>
              <option value="restaurant">Restaurant</option>
              <option value="quincaillerie">Quincaillerie</option>
              <option value="boutique">Boutique / Commerce</option>
              <option value="location">Location</option>
              <option value="services">Services</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="form-group">
            <label>NINEA</label>
            <input v-model="form.ninea" type="text" />
          </div>
        </div>
      </fieldset>

      <!-- Préférences -->
      <fieldset class="form-section">
        <legend>Préférences</legend>
        <div class="form-grid">
          <div class="form-group">
            <label>Devise</label>
            <select v-model="form.currency">
              <option value="XOF">XOF — Franc CFA</option>
              <option value="EUR">EUR — Euro</option>
              <option value="USD">USD — Dollar</option>
            </select>
          </div>
          <div class="form-group">
            <label>Fuseau horaire</label>
            <select v-model="form.timezone">
              <option value="Africa/Dakar">Africa/Dakar (UTC+0)</option>
              <option value="Africa/Lagos">Africa/Lagos (UTC+1)</option>
              <option value="Europe/Paris">Europe/Paris (UTC+1/+2)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Couleur principale</label>
            <div class="color-input">
              <input v-model="form.primary_color" type="color" class="color-picker" />
              <span class="color-val">{{ form.primary_color }}</span>
            </div>
          </div>
          <div class="form-group">
            <label>Couleur secondaire</label>
            <div class="color-input">
              <input v-model="form.secondary_color" type="color" class="color-picker" />
              <span class="color-val">{{ form.secondary_color }}</span>
            </div>
          </div>
        </div>
      </fieldset>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
        </button>
      </div>

      <div v-if="$page.props.flash?.success" class="flash-success">
        <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-title {
  font-size: 1.15rem; font-weight: 700; color: #111827;
  margin-bottom: 20px; padding-left: 12px;
  border-left: 3px solid #8B5CF6;
}

.settings-form { max-width: 750px; }

.form-section {
  background: #fff; border: 1px solid #E5E7EB; padding: 20px 24px;
  margin-bottom: 20px;
}
.form-section legend {
  font-size: 0.88rem; font-weight: 700; color: #374151;
  padding: 0 6px;
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.form-group input, .form-group select {
  padding: 9px 12px; border: 1px solid #D1D5DB; font-size: 0.84rem;
  color: #111827; background: #fff; outline: none; transition: border 0.15s;
}
.form-group input:focus, .form-group select:focus { border-color: #6366F1; }
.form-error { font-size: 0.72rem; color: #EF4444; }

/* Color picker */
.color-input { display: flex; align-items: center; gap: 10px; }
.color-picker { width: 36px; height: 36px; border: 1px solid #D1D5DB; cursor: pointer; padding: 0; }
.color-val { font-size: 0.78rem; color: #6B7280; font-family: monospace; }

.form-actions { margin-top: 10px; }
.btn-primary {
  display: inline-flex; align-items: center; gap: 8px;
  background: #6366F1; color: #fff; padding: 10px 20px;
  font-size: 0.84rem; font-weight: 600; border: none; cursor: pointer;
  transition: background 0.15s;
}
.btn-primary:hover { background: #4F46E5; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.flash-success {
  margin-top: 16px; padding: 10px 14px; background: #D1FAE5;
  color: #065F46; font-size: 0.82rem; font-weight: 500;
  display: flex; align-items: center; gap: 8px;
}
</style>
