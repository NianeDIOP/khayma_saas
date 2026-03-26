<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ plans: Array, modules: Array })

const form = useForm({
  name: '',
  email: '',
  phone: '',
  sector: '',
  address: '',
  subscription_status: 'trial',
  owner_name: '',
  owner_email: '',
  plan_id: '',
  module_ids: [],
})

function toggleModule(id) {
  const idx = form.module_ids.indexOf(id)
  if (idx === -1) form.module_ids.push(id)
  else form.module_ids.splice(idx, 1)
}

function submit() {
  form.post(route('admin.companies.store'))
}
</script>

<template>
  <AdminLayout title="Créer une entreprise">
    <Head title="Admin · Nouvelle entreprise" />

    <div class="back-link">
      <Link :href="route('admin.companies.index')"><i class="fa-solid fa-arrow-left"></i> Entreprises</Link>
    </div>

    <div class="form-card">
      <h2 class="form-title">Créer une entreprise manuellement</h2>

      <form @submit.prevent="submit">
        <!-- Entreprise -->
        <div class="section">
          <div class="section-title">Informations de l'entreprise</div>
          <div class="form-row">
            <div class="field">
              <label>Nom *</label>
              <input v-model="form.name" type="text" class="kh-input" placeholder="Nom de l'entreprise" />
              <span v-if="form.errors.name" class="error">{{ form.errors.name }}</span>
            </div>
            <div class="field">
              <label>Email *</label>
              <input v-model="form.email" type="email" class="kh-input" placeholder="contact@entreprise.com" />
              <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Téléphone</label>
              <input v-model="form.phone" type="text" class="kh-input" placeholder="+221 77 123 45 67" />
            </div>
            <div class="field">
              <label>Secteur</label>
              <input v-model="form.sector" type="text" class="kh-input" placeholder="Restauration, Commerce..." />
            </div>
          </div>
          <div class="field">
            <label>Adresse</label>
            <input v-model="form.address" type="text" class="kh-input" placeholder="Adresse complète" />
          </div>
        </div>

        <!-- Propriétaire -->
        <div class="section">
          <div class="section-title">Propriétaire (compte créé automatiquement)</div>
          <div class="form-row">
            <div class="field">
              <label>Nom complet *</label>
              <input v-model="form.owner_name" type="text" class="kh-input" placeholder="Prénom Nom" />
              <span v-if="form.errors.owner_name" class="error">{{ form.errors.owner_name }}</span>
            </div>
            <div class="field">
              <label>Email *</label>
              <input v-model="form.owner_email" type="email" class="kh-input" placeholder="owner@email.com" />
              <span v-if="form.errors.owner_email" class="error">{{ form.errors.owner_email }}</span>
            </div>
          </div>
          <p class="hint">Un mot de passe aléatoire sera généré et affiché après la création.</p>
        </div>

        <!-- Abonnement -->
        <div class="section">
          <div class="section-title">Abonnement</div>
          <div class="form-row">
            <div class="field">
              <label>Statut</label>
              <select v-model="form.subscription_status" class="kh-input">
                <option value="trial">Essai gratuit</option>
                <option value="active">Actif</option>
              </select>
            </div>
            <div class="field">
              <label>Plan</label>
              <select v-model="form.plan_id" class="kh-input">
                <option value="">— Aucun plan —</option>
                <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Modules -->
        <div class="section">
          <div class="section-title">Modules à activer</div>
          <div class="modules-list">
            <label v-for="mod in modules" :key="mod.id" class="mod-check">
              <input type="checkbox" :checked="form.module_ids.includes(mod.id)" @change="toggleModule(mod.id)" />
              <i :class="mod.icon || 'fa-solid fa-puzzle-piece'" class="mod-icon"></i>
              <span>{{ mod.name }}</span>
            </label>
          </div>
          <div v-if="!modules?.length" class="hint">Aucun module disponible.</div>
        </div>

        <div class="form-actions">
          <Link :href="route('admin.companies.index')" class="btn-cancel">Annuler</Link>
          <button type="submit" class="btn-submit" :disabled="form.processing">Créer l'entreprise</button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.back-link { margin-bottom: 20px; }
.back-link a { font-size: 0.82rem; color: #6366F1; text-decoration: none; font-weight: 600; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 680px; }
.form-title { font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 20px; }
.section { margin-bottom: 24px; }
.section-title { font-size: 0.78rem; font-weight: 700; color: #6366F1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #EEF2FF; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { margin-bottom: 12px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.kh-input { width: 100%; padding: 8px 10px; border: 1px solid #D1D5DB; font-size: 0.82rem; background: #fff; }
.kh-input:focus { border-color: #6366F1; outline: none; }
.error { color: #EF4444; font-size: 0.72rem; }
.hint { font-size: 0.72rem; color: #9CA3AF; margin-top: 4px; }
.modules-list { display: flex; flex-wrap: wrap; gap: 8px; }
.mod-check { display: flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #E5E7EB; cursor: pointer; font-size: 0.82rem; color: #374151; }
.mod-check:hover { border-color: #6366F1; }
.mod-icon { color: #F59E0B; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #F3F4F6; }
.btn-cancel { padding: 8px 16px; background: #F3F4F6; color: #374151; font-size: 0.82rem; font-weight: 600; text-decoration: none; }
.btn-submit { padding: 8px 20px; background: #6366F1; color: #fff; border: none; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.btn-submit:disabled { opacity: 0.6; }
</style>
