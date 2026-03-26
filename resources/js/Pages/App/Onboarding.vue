<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  company: Object,
  steps: Array,
})

const page = usePage()
const tenant = page.props.currentCompany?.slug

const form = useForm({
  email:   props.company.email || '',
  phone:   props.company.phone || '',
  address: props.company.address || '',
  sector:  props.company.sector || '',
  ninea:   props.company.ninea || '',
})

function submit() {
  form.put(route('app.onboarding.update', { _tenant: tenant }))
}

const completedCount = props.steps.filter(s => s.done).length
const progress = Math.round((completedCount / props.steps.length) * 100)
</script>

<template>
  <AppLayout title="Onboarding">
    <Head title="Onboarding" />

    <h1 class="page-title">Bienvenue — Complétons votre profil</h1>

    <!-- Progress bar -->
    <div class="onboard-progress">
      <div class="progress-header">
        <span>{{ completedCount }} / {{ steps.length }} étapes complétées</span>
        <span class="progress-pct">{{ progress }}%</span>
      </div>
      <div class="progress-track">
        <div class="progress-fill" :style="{ width: progress + '%' }"></div>
      </div>
    </div>

    <!-- Checklist -->
    <div class="checklist">
      <div v-for="(step, i) in steps" :key="i" class="check-item" :class="{ done: step.done }">
        <i :class="step.done ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle'" class="check-icon"></i>
        <span>{{ step.label }}</span>
      </div>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="onboard-form">
      <h2 class="form-title">Informations de l'entreprise</h2>

      <div class="form-grid">
        <div class="form-group">
          <label>Email professionnel</label>
          <input v-model="form.email" type="email" placeholder="contact@entreprise.sn" />
          <span v-if="form.errors.email" class="form-error">{{ form.errors.email }}</span>
        </div>
        <div class="form-group">
          <label>Téléphone</label>
          <input v-model="form.phone" type="text" placeholder="+221 77 123 45 67" />
          <span v-if="form.errors.phone" class="form-error">{{ form.errors.phone }}</span>
        </div>
        <div class="form-group full">
          <label>Adresse</label>
          <input v-model="form.address" type="text" placeholder="Dakar, Sénégal" />
          <span v-if="form.errors.address" class="form-error">{{ form.errors.address }}</span>
        </div>
        <div class="form-group">
          <label>Secteur d'activité</label>
          <select v-model="form.sector">
            <option value="">— Choisir —</option>
            <option value="restaurant">Restaurant</option>
            <option value="quincaillerie">Quincaillerie</option>
            <option value="boutique">Boutique / Commerce</option>
            <option value="location">Location</option>
            <option value="services">Services</option>
            <option value="autre">Autre</option>
          </select>
          <span v-if="form.errors.sector" class="form-error">{{ form.errors.sector }}</span>
        </div>
        <div class="form-group">
          <label>NINEA <small>(optionnel)</small></label>
          <input v-model="form.ninea" type="text" placeholder="Numéro NINEA" />
          <span v-if="form.errors.ninea" class="form-error">{{ form.errors.ninea }}</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-check"></i> Enregistrer
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
  border-left: 3px solid #F59E0B;
}

/* ── Progress ─── */
.onboard-progress { margin-bottom: 24px; }
.progress-header {
  display: flex; justify-content: space-between; font-size: 0.82rem;
  color: #6B7280; margin-bottom: 6px;
}
.progress-pct { font-weight: 700; color: #10B981; }
.progress-track { height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden; }
.progress-fill { height: 100%; background: #10B981; transition: width 0.4s ease; border-radius: 3px; }

/* ── Checklist ─── */
.checklist { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 28px; }
.check-item {
  display: flex; align-items: center; gap: 8px; font-size: 0.84rem;
  color: #6B7280; font-weight: 500;
}
.check-item.done { color: #10B981; }
.check-icon { font-size: 1rem; }

/* ── Form ─── */
.onboard-form {
  background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 700px;
}
.form-title {
  font-size: 0.95rem; font-weight: 700; color: #111827; margin-bottom: 18px;
  padding-bottom: 10px; border-bottom: 1px solid #E5E7EB;
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.form-group label small { font-weight: 400; color: #9CA3AF; }
.form-group input, .form-group select {
  padding: 9px 12px; border: 1px solid #D1D5DB; font-size: 0.84rem;
  color: #111827; background: #fff; outline: none; transition: border 0.15s;
}
.form-group input:focus, .form-group select:focus { border-color: #6366F1; }
.form-error { font-size: 0.72rem; color: #EF4444; }

.form-actions { margin-top: 20px; }
.btn-primary {
  display: inline-flex; align-items: center; gap: 8px;
  background: #10B981; color: #fff; padding: 10px 20px;
  font-size: 0.84rem; font-weight: 600; border: none; cursor: pointer;
  transition: background 0.15s;
}
.btn-primary:hover { background: #059669; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.flash-success {
  margin-top: 16px; padding: 10px 14px; background: #D1FAE5;
  color: #065F46; font-size: 0.82rem; font-weight: 500;
  display: flex; align-items: center; gap: 8px;
}
</style>
