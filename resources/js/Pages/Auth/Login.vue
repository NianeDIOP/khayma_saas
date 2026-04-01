<script setup>
import { ref } from 'vue'
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const mode = ref('email') // 'email' | 'otp'
const otpSent = ref(false)

const form = useForm({
    email:    '',
    password: '',
    remember: false,
})

const otpForm = useForm({
    phone: '',
    code:  '',
    remember: false,
})

function submit() {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    })
}

function sendOtp() {
    otpForm.post(route('otp.send'), {
        preserveScroll: true,
        onSuccess: () => { otpSent.value = true },
    })
}

function verifyOtp() {
    otpForm.post(route('otp.verify'))
}
</script>

<template>
    <Head title="Connexion — Khayma" />

    <AuthLayout>
        <h1 class="page-title">Connexion</h1>
        <p class="page-sub">Accédez à votre espace de gestion.</p>

        <!-- Tab switcher -->
        <div class="tab-bar">
            <button :class="['tab-btn', { active: mode === 'email' }]" @click="mode = 'email'; otpSent = false">
                E-mail
            </button>
            <button :class="['tab-btn', { active: mode === 'otp' }]" @click="mode = 'otp'">
                Téléphone / OTP
            </button>
        </div>

        <!-- ── Email/Password form ─────────────────── -->
        <template v-if="mode === 'email'">
            <!-- Erreur globale -->
            <div v-if="form.errors.email" class="alert-error">
                {{ form.errors.email }}
            </div>

            <form @submit.prevent="submit" novalidate>
                <!-- E-mail -->
                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="vous@exemple.com"
                        :class="{ 'is-error': form.errors.email }"
                        required
                    />
                </div>

                <!-- Mot de passe -->
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :class="{ 'is-error': form.errors.password }"
                        required
                    />
                    <span v-if="form.errors.password" class="field-error">
                        {{ form.errors.password }}
                    </span>
                </div>

                <!-- Se souvenir de moi -->
                <div class="field-check">
                    <label>
                        <input v-model="form.remember" type="checkbox" />
                        <span>Se souvenir de moi</span>
                    </label>
                </div>

                <!-- Bouton -->
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <span v-if="form.processing">Connexion…</span>
                    <span v-else>Se connecter</span>
                </button>
            </form>
        </template>

        <!-- ── OTP form ────────────────────────────── -->
        <template v-else>
            <div v-if="otpForm.errors.phone" class="alert-error">
                {{ otpForm.errors.phone }}
            </div>
            <div v-if="otpForm.errors.code" class="alert-error">
                {{ otpForm.errors.code }}
            </div>

            <form @submit.prevent="otpSent ? verifyOtp() : sendOtp()" novalidate>
                <div class="field">
                    <label for="phone">Numéro de téléphone</label>
                    <input
                        id="phone"
                        v-model="otpForm.phone"
                        type="tel"
                        autocomplete="tel"
                        placeholder="+221 77 000 00 00"
                        :class="{ 'is-error': otpForm.errors.phone }"
                        :disabled="otpSent"
                        required
                    />
                </div>

                <template v-if="otpSent">
                    <div class="field">
                        <label for="otp-code">Code OTP (6 chiffres)</label>
                        <input
                            id="otp-code"
                            v-model="otpForm.code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="123456"
                            :class="{ 'is-error': otpForm.errors.code }"
                            required
                        />
                    </div>

                    <div class="field-check">
                        <label>
                            <input v-model="otpForm.remember" type="checkbox" />
                            <span>Se souvenir de moi</span>
                        </label>
                    </div>
                </template>

                <button type="submit" class="btn-primary" :disabled="otpForm.processing">
                    <span v-if="otpForm.processing">Envoi…</span>
                    <span v-else-if="!otpSent">Envoyer le code</span>
                    <span v-else>Vérifier le code</span>
                </button>

                <button v-if="otpSent" type="button" class="btn-link" @click="otpSent = false">
                    Renvoyer le code
                </button>
            </form>
        </template>

        <p class="auth-footer">
            Pas encore de compte ?
            <Link :href="route('register')" class="link-green">Créer un compte gratuit</Link>
        </p>
    </AuthLayout>
</template>

<style scoped>
.page-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0F172A;
    text-align: center;
    margin-bottom: 4px;
}
.page-sub {
    font-size: 0.85rem;
    color: #6B7280;
    text-align: center;
    margin-bottom: 20px;
}
.tab-bar {
    display: flex;
    gap: 0;
    margin-bottom: 24px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    overflow: hidden;
}
.tab-btn {
    flex: 1;
    padding: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    background: #F9FAFB;
    color: #6B7280;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.tab-btn.active {
    background: #10B981;
    color: #fff;
}
.tab-btn:not(.active):hover {
    background: #F3F4F6;
}
.alert-error {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #B91C1C;
    font-size: 0.85rem;
    padding: 10px 14px;
    margin-bottom: 20px;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 18px;
}
.field label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.field input {
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
.field-check {
    margin-bottom: 22px;
}
.field-check label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    color: #374151;
    cursor: pointer;
}
.field-check input[type="checkbox"] {
    accent-color: #10B981;
    width: 16px;
    height: 16px;
}
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
    letter-spacing: 0.02em;
}
.btn-primary:hover:not(:disabled) {
    background: #059669;
}
.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
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
.link-green:hover {
    text-decoration: underline;
}
.btn-link {
    display: block;
    width: 100%;
    text-align: center;
    margin-top: 12px;
    background: none;
    border: none;
    color: #10B981;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}
.btn-link:hover {
    text-decoration: underline;
}
</style>
