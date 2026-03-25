<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const form = useForm({
    email:    '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Connexion — Khayma" />

    <AuthLayout>
        <h1 class="page-title">Connexion</h1>
        <p class="page-sub">Accédez à votre espace de gestion.</p>

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
    margin-bottom: 28px;
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
</style>
