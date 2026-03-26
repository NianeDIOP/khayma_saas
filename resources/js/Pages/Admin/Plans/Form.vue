<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ plan: { type: Object, default: null } })
const isEdit = !!props.plan

const form = useForm({
  name: props.plan?.name || '',
  code: props.plan?.code || '',
  max_products: props.plan?.max_products ?? 300,
  max_users: props.plan?.max_users ?? 2,
  max_storage_gb: props.plan?.max_storage_gb ?? 1,
  max_transactions_month: props.plan?.max_transactions_month ?? 1000,
  api_rate_limit: props.plan?.api_rate_limit ?? 30,
  price_monthly: props.plan?.price_monthly ?? 0,
  price_quarterly: props.plan?.price_quarterly ?? 0,
  price_yearly: props.plan?.price_yearly ?? 0,
  is_active: props.plan?.is_active ?? true,
})

function submit() {
  if (isEdit) {
    form.put(route('admin.plans.update', props.plan.id))
  } else {
    form.post(route('admin.plans.store'))
  }
}
</script>

<template>
  <AdminLayout :title="isEdit ? 'Modifier le plan' : 'Nouveau plan'">
    <Head :title="isEdit ? 'Admin · Modifier plan' : 'Admin · Nouveau plan'" />

    <div class="back-link">
      <Link :href="route('admin.plans.index')"><i class="fa-solid fa-arrow-left"></i> Plans & Tarifs</Link>
    </div>

    <div class="form-card">
      <h2 class="form-title">{{ isEdit ? 'Modifier le plan' : 'Créer un plan' }}</h2>

      <form @submit.prevent="submit">
        <!-- Infos de base -->
        <div class="form-section">
          <div class="section-title">Informations</div>
          <div class="form-row">
            <div class="field">
              <label>Nom *</label>
              <input v-model="form.name" type="text" class="kh-input" placeholder="Starter, Business, Pro..." />
              <span v-if="form.errors.name" class="error">{{ form.errors.name }}</span>
            </div>
            <div class="field">
              <label>Code *</label>
              <input v-model="form.code" type="text" class="kh-input" placeholder="starter, business..." />
              <span v-if="form.errors.code" class="error">{{ form.errors.code }}</span>
            </div>
          </div>
        </div>

        <!-- Prix -->
        <div class="form-section">
          <div class="section-title">Tarification (XOF)</div>
          <div class="form-row form-row-3">
            <div class="field">
              <label>Prix mensuel *</label>
              <input v-model.number="form.price_monthly" type="number" min="0" class="kh-input" />
              <span v-if="form.errors.price_monthly" class="error">{{ form.errors.price_monthly }}</span>
            </div>
            <div class="field">
              <label>Prix trimestriel *</label>
              <input v-model.number="form.price_quarterly" type="number" min="0" class="kh-input" />
            </div>
            <div class="field">
              <label>Prix annuel *</label>
              <input v-model.number="form.price_yearly" type="number" min="0" class="kh-input" />
            </div>
          </div>
        </div>

        <!-- Limites -->
        <div class="form-section">
          <div class="section-title">Limites</div>
          <div class="form-row form-row-3">
            <div class="field">
              <label>Max produits (0 = illimité)</label>
              <input v-model.number="form.max_products" type="number" min="0" class="kh-input" />
            </div>
            <div class="field">
              <label>Max utilisateurs *</label>
              <input v-model.number="form.max_users" type="number" min="1" class="kh-input" />
            </div>
            <div class="field">
              <label>Stockage (Go) *</label>
              <input v-model.number="form.max_storage_gb" type="number" min="1" class="kh-input" />
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Transactions / mois (0 = illimité)</label>
              <input v-model.number="form.max_transactions_month" type="number" min="0" class="kh-input" />
            </div>
            <div class="field">
              <label>API rate limit (/min)</label>
              <input v-model.number="form.api_rate_limit" type="number" min="0" class="kh-input" />
            </div>
          </div>
        </div>

        <!-- Actif -->
        <div class="form-section">
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_active" />
            Plan actif (visible pour les entreprises)
          </label>
        </div>

        <div class="form-actions">
          <Link :href="route('admin.plans.index')" class="btn-cancel">Annuler</Link>
          <button type="submit" class="btn-submit" :disabled="form.processing">
            {{ isEdit ? 'Enregistrer' : 'Créer le plan' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.back-link { margin-bottom: 20px; }
.back-link a { color: #6366F1; font-size: 0.82rem; text-decoration: none; font-weight: 600; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 720px; }
.form-title { font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 20px; }
.form-section { margin-bottom: 20px; }
.section-title { font-size: 0.78rem; font-weight: 700; color: #6366F1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #EEF2FF; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
.form-row-3 { grid-template-columns: 1fr 1fr 1fr; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.kh-input { width: 100%; padding: 8px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; }
.kh-input:focus { border-color: #6366F1; outline: none; }
.error { color: #EF4444; font-size: 0.72rem; margin-top: 2px; display: block; }
.checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: #374151; cursor: pointer; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #F3F4F6; }
.btn-cancel { padding: 8px 16px; background: #F3F4F6; color: #374151; font-size: 0.82rem; font-weight: 600; text-decoration: none; }
.btn-submit { padding: 8px 20px; background: #6366F1; color: #fff; border: none; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.btn-submit:disabled { opacity: 0.6; }
</style>
