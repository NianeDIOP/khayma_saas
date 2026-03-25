<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

// ── Wizard 2 étapes ───────────────────────────────────────────
const step = ref(1)

const sectors = [
    { value: 'restaurant',    label: '🍽️  Restaurant / Snack / Café' },
    { value: 'boutique',      label: '👗  Boutique / Prêt-à-porter' },
    { value: 'quincaillerie', label: '🔧  Quincaillerie / Matériaux' },
    { value: 'location',      label: '🚗  Location de véhicules / Équipements' },
    { value: 'autre',         label: '🏢  Autre activité' },
]

const form = useForm({
    // Étape 1
    name:     '',
    email:    '',
    password: '',
    password_confirmation: '',
    // Étape 2
    company_name: '',
    sector:       '',
    phone:        '',
})

// Validation locale avant de passer à l'étape 2
const step1Errors = ref({})

function validateStep1() {
    const errors = {}
    if (! form.name.trim())              errors.name     = 'Votre nom est requis.'
    if (! form.email.trim())             errors.email    = 'L\'e-mail est requis.'
    if (! form.password)                 errors.password = 'Le mot de passe est requis.'
    if (form.password.length < 8)        errors.password = 'Minimum 8 caractères.'
    if (form.password !== form.password_confirmation)
                                         errors.password_confirmation = 'Les mots de passe ne correspondent pas.'
    step1Errors.value = errors
    return Object.keys(errors).length === 0
}

function nextStep() {
    if (validateStep1()) step.value = 2
}

function submit() {
    form.post(route('register.store'))
}

const progressWidth = computed(() => step.value === 1 ? '50%' : '100%')
</script>

<template>
    <Head title="Créer un compte — Khayma" />

    <AuthLayout>
        <!-- Progress bar -->
        <div class="progress-track">
            <div class="progress-fill" :style="{ width: progressWidth }" />
        </div>

        <!-- Étapes label -->
        <div class="steps-label">
            <span :class="{ active: step === 1, done: step > 1 }">① Votre compte</span>
            <span :class="{ active: step === 2 }">② Votre entreprise</span>
        </div>

        <!-- ═══════════════════ ÉTAPE 1 ═══════════════════ -->
        <template v-if="step === 1">
            <h1 class="page-title">Créez votre compte</h1>
            <p class="page-sub">Essai gratuit 14 jours · Sans carte bancaire</p>

            <form @submit.prevent="nextStep" novalidate>
                <div class="field">
                    <label for="name">Votre nom complet</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        autocomplete="name"
                        placeholder="Aminata Diallo"
                        :class="{ 'is-error': step1Errors.name || form.errors.name }"
                    />
                    <span v-if="step1Errors.name || form.errors.name" class="field-error">
                        {{ step1Errors.name || form.errors.name }}
                    </span>
                </div>

                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="vous@exemple.com"
                        :class="{ 'is-error': step1Errors.email || form.errors.email }"
                    />
                    <span v-if="step1Errors.email || form.errors.email" class="field-error">
                        {{ step1Errors.email || form.errors.email }}
                    </span>
                </div>

                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Minimum 8 caractères"
                        :class="{ 'is-error': step1Errors.password || form.errors.password }"
                    />
                    <span v-if="step1Errors.password || form.errors.password" class="field-error">
                        {{ step1Errors.password || form.errors.password }}
                    </span>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        :class="{ 'is-error': step1Errors.password_confirmation }"
                    />
                    <span v-if="step1Errors.password_confirmation" class="field-error">
                        {{ step1Errors.password_confirmation }}
                    </span>
                </div>

                <button type="submit" class="btn-primary">
                    Continuer →
                </button>
            </form>

            <p class="auth-footer">
                Déjà un compte ?
                <Link :href="route('login')" class="link-green">Se connecter</Link>
            </p>
        </template>

        <!-- ═══════════════════ ÉTAPE 2 ═══════════════════ -->
        <template v-else>
            <h1 class="page-title">Votre entreprise</h1>
            <p class="page-sub">Personnalisez votre espace Khayma.</p>

            <form @submit.prevent="submit" novalidate>
                <div class="field">
                    <label for="company_name">Nom de l'entreprise</label>
                    <input
                        id="company_name"
                        v-model="form.company_name"
                        type="text"
                        placeholder="Restaurant Awa, Boutique Ndèye…"
                        :class="{ 'is-error': form.errors.company_name }"
                    />
                    <span v-if="form.errors.company_name" class="field-error">
                        {{ form.errors.company_name }}
                    </span>
                </div>

                <div class="field">
                    <label for="sector">Secteur d'activité</label>
                    <div class="sector-grid">
                        <label
                            v-for="s in sectors"
                            :key="s.value"
                            class="sector-option"
                            :class="{ selected: form.sector === s.value }"
                        >
                            <input
                                v-model="form.sector"
                                type="radio"
                                :value="s.value"
                                class="sector-radio"
                            />
                            {{ s.label }}
                        </label>
                    </div>
                    <span v-if="form.errors.sector" class="field-error">
                        {{ form.errors.sector }}
                    </span>
                </div>

                <div class="field">
                    <label for="phone">Téléphone <span class="optional">(optionnel)</span></label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="+221 77 000 00 00"
                        :class="{ 'is-error': form.errors.phone }"
                    />
                </div>

                <div class="btn-row">
                    <button type="button" class="btn-back" @click="step = 1">
                        ← Retour
                    </button>
                    <button type="submit" class="btn-primary flex-1" :disabled="form.processing">
                        <span v-if="form.processing">Création…</span>
                        <span v-else>Créer mon espace gratuit</span>
                    </button>
                </div>
            </form>
        </template>
    </AuthLayout>
</template>

<style scoped>
/* Progress bar */
.progress-track {
    height: 4px;
    background: #E5E7EB;
    margin-bottom: 12px;
}
.progress-fill {
    height: 4px;
    background: #10B981;
    transition: width 0.35s ease;
}

/* Steps label */
.steps-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    font-weight: 600;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 24px;
}
.steps-label span.active { color: #10B981; }
.steps-label span.done   { color: #6B7280; }

/* Titles */
.page-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #0F172A;
    text-align: center;
    margin-bottom: 4px;
}
.page-sub {
    font-size: 0.85rem;
    color: #6B7280;
    text-align: center;
    margin-bottom: 24px;
}

/* Fields */
.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}
.field label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.field input[type="text"],
.field input[type="email"],
.field input[type="password"],
.field input[type="tel"] {
    padding: 10px 12px;
    border: 1px solid #D1D5DB;
    background: #F9FAFB;
    font-size: 0.95rem;
    color: #0F172A;
    outline: none;
    transition: border-color 0.15s;
}
.field input:focus {
    border-color: #10B981;
    background: #fff;
}
.field input.is-error {
    border-color: #EF4444;
}
.field-error {
    font-size: 0.78rem;
    color: #EF4444;
}
.optional {
    font-weight: 400;
    text-transform: none;
    color: #9CA3AF;
}

/* Sector grid */
.sector-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.sector-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 1px solid #D1D5DB;
    background: #F9FAFB;
    font-size: 0.9rem;
    color: #374151;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}
.sector-option:hover {
    border-color: #10B981;
    background: #ECFDF5;
}
.sector-option.selected {
    border-color: #10B981;
    background: #ECFDF5;
    color: #065F46;
    font-weight: 600;
}
.sector-radio {
    display: none;
}

/* Buttons */
.btn-primary {
    width: 100%;
    padding: 12px;
    background: #10B981;
    color: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: #059669; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.flex-1 { flex: 1; }

.btn-back {
    padding: 12px 16px;
    background: transparent;
    color: #6B7280;
    font-size: 0.9rem;
    border: 1px solid #D1D5DB;
    cursor: pointer;
    transition: border-color 0.15s;
}
.btn-back:hover { border-color: #9CA3AF; color: #374151; }

.btn-row {
    display: flex;
    gap: 10px;
    margin-top: 4px;
}

/* Footer */
.auth-footer {
    text-align: center;
    font-size: 0.85rem;
    color: #6B7280;
    margin-top: 24px;
}
.link-green {
    color: #10B981;
    font-weight: 600;
    text-decoration: none;
}
.link-green:hover { text-decoration: underline; }
</style>
