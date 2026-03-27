<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    member: Object,
    roles: Array,
    allPermissions: Array,
    activeModuleCodes: Array,
    roleDefaults: Object,
})

const page = usePage()
const company = page.props.currentCompany
const isEdit = !!props.member

const form = useForm({
    name:        props.member?.name        || '',
    email:       props.member?.email       || '',
    phone:       props.member?.phone       || '',
    password:    '',
    role:        props.member?.role        || 'manager',
    permissions: props.member?.permissions || [],
})

// When creating and no permissions yet, pre-select from role defaults
if (!isEdit && form.permissions.length === 0 && props.roleDefaults) {
    form.permissions = [...(props.roleDefaults[form.role] || [])]
}

// When role changes on create, reset permissions to role defaults
watch(() => form.role, (newRole) => {
    if (!isEdit && props.roleDefaults) {
        form.permissions = [...(props.roleDefaults[newRole] || [])]
    }
})

const roleLabels = {
    manager:    'Manager — Gestion opérationnelle complète',
    caissier:   'Caissier — Encaissements, ventes, commandes',
    magasinier: 'Magasinier — Stocks, inventaires, réceptions',
}

// Filter permission groups by active modules
const visibleGroups = computed(() => {
    return (props.allPermissions || []).filter(g => {
        if (!g.module) return true
        return (props.activeModuleCodes || []).includes(g.module)
    })
})

function togglePermission(key) {
    const idx = form.permissions.indexOf(key)
    if (idx >= 0) form.permissions.splice(idx, 1)
    else form.permissions.push(key)
}

function toggleGroup(group) {
    const keys = group.items.map(i => i.key)
    const allChecked = keys.every(k => form.permissions.includes(k))
    if (allChecked) {
        form.permissions = form.permissions.filter(p => !keys.includes(p))
    } else {
        keys.forEach(k => { if (!form.permissions.includes(k)) form.permissions.push(k) })
    }
}

function isGroupChecked(group) {
    return group.items.every(i => form.permissions.includes(i.key))
}
function isGroupPartial(group) {
    const some = group.items.some(i => form.permissions.includes(i.key))
    return some && !isGroupChecked(group)
}

function submit() {
    if (isEdit) {
        form.put(route('app.team.update', { user: props.member.id, _tenant: company.slug }))
    } else {
        form.post(route('app.team.store', { _tenant: company.slug }))
    }
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Modifier un membre' : 'Ajouter un membre'">
        <div class="form-page">
            <div class="page-header">
                <Link :href="route('app.team.index', { _tenant: company.slug })" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Retour à l'équipe
                </Link>
                <h1 class="page-title">{{ isEdit ? 'Modifier le membre' : 'Ajouter un membre' }}</h1>
                <p class="page-sub">{{ isEdit ? `Modifier le profil de ${member.name}` : 'Invitez un collaborateur dans votre entreprise' }}</p>
            </div>

            <form @submit.prevent="submit" class="member-form">
                <!-- Nom -->
                <div class="field">
                    <label for="name">Nom complet <span class="req">*</span></label>
                    <input id="name" v-model="form.name" type="text" placeholder="Mamadou Diallo"
                           :class="{ 'is-error': form.errors.name }" required />
                    <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="field">
                    <label for="email">Adresse e-mail <span class="req">*</span></label>
                    <input id="email" v-model="form.email" type="email" placeholder="nom@exemple.com"
                           :disabled="isEdit"
                           :class="{ 'is-error': form.errors.email }" required />
                    <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                    <p v-if="isEdit" class="field-hint">L'email ne peut pas être modifié.</p>
                </div>

                <!-- Téléphone -->
                <div class="field">
                    <label for="phone">Téléphone</label>
                    <input id="phone" v-model="form.phone" type="tel" placeholder="+221 77 000 00 00" />
                </div>

                <!-- Mot de passe -->
                <div class="field">
                    <label for="password">
                        Mot de passe <span v-if="!isEdit" class="req">*</span>
                    </label>
                    <input id="password" v-model="form.password" type="password"
                           :placeholder="isEdit ? 'Laisser vide pour ne pas changer' : 'Minimum 8 caractères'"
                           :class="{ 'is-error': form.errors.password }"
                           :required="!isEdit" />
                    <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                </div>

                <!-- Rôle -->
                <div class="field">
                    <label>Rôle dans l'entreprise <span class="req">*</span></label>
                    <div class="role-options">
                        <label v-for="r in roles" :key="r" class="role-option" :class="{ selected: form.role === r }">
                            <input type="radio" v-model="form.role" :value="r" class="sr-only" />
                            <div class="role-card">
                                <span class="role-name">{{ r.charAt(0).toUpperCase() + r.slice(1) }}</span>
                                <span class="role-desc">{{ roleLabels[r] }}</span>
                            </div>
                        </label>
                    </div>
                    <p v-if="form.errors.role" class="field-error">{{ form.errors.role }}</p>
                </div>

                <!-- Permissions granulaires -->
                <div class="field perm-section">
                    <label class="perm-main-label">Autorisations <span class="perm-hint">(cocher les accès autorisés)</span></label>
                    <div v-for="group in visibleGroups" :key="group.group" class="perm-group">
                        <label class="perm-group-header" @click.prevent="toggleGroup(group)">
                            <input type="checkbox" :checked="isGroupChecked(group)" :indeterminate="isGroupPartial(group)"
                                   @click.stop="toggleGroup(group)" class="perm-cb" />
                            <span class="perm-group-name">{{ group.group }}</span>
                        </label>
                        <div class="perm-items">
                            <label v-for="item in group.items" :key="item.key" class="perm-item">
                                <input type="checkbox" :checked="form.permissions.includes(item.key)"
                                       @change="togglePermission(item.key)" class="perm-cb" />
                                <span>{{ item.label }}</span>
                            </label>
                        </div>
                    </div>
                    <p v-if="form.errors.permissions" class="field-error">{{ form.errors.permissions }}</p>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <Link :href="route('app.team.index', { _tenant: company.slug })" class="btn-secondary">Annuler</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Ajouter à l\'équipe') }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.form-page { max-width: 640px; }
.page-header { margin-bottom: 28px; }
.back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; color: #6B7280; text-decoration: none; margin-bottom: 12px; }
.back-link:hover { color: #111827; }
.page-title { font-size: 1.5rem; font-weight: 800; color: #111827; margin: 0; }
.page-sub { font-size: 0.85rem; color: #6B7280; margin-top: 4px; }

.member-form { display: flex; flex-direction: column; gap: 20px; }

.field label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
.req { color: #EF4444; }
.field input[type="text"],
.field input[type="email"],
.field input[type="tel"],
.field input[type="password"] {
    width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; font-size: 0.88rem;
    color: #111827; background: #fff; transition: border 0.15s;
}
.field input:focus { outline: none; border-color: #4F46E5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.field input:disabled { background: #F3F4F6; color: #9CA3AF; cursor: not-allowed; }
.field input.is-error { border-color: #EF4444; }
.field-error { font-size: 0.78rem; color: #EF4444; margin-top: 4px; }
.field-hint { font-size: 0.78rem; color: #9CA3AF; margin-top: 4px; }

.role-options { display: flex; flex-direction: column; gap: 10px; }
.role-option { cursor: pointer; }
.role-card {
    padding: 14px 16px; border: 2px solid #E5E7EB; background: #fff;
    transition: all 0.15s; display: flex; flex-direction: column; gap: 2px;
}
.role-option:hover .role-card { border-color: #A5B4FC; }
.role-option.selected .role-card { border-color: #4F46E5; background: #EEF2FF; }
.role-name { font-size: 0.88rem; font-weight: 700; color: #111827; }
.role-desc { font-size: 0.78rem; color: #6B7280; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

/* ── Permissions ── */
.perm-section { margin-top: 8px; }
.perm-main-label { font-size: 0.88rem !important; font-weight: 700 !important; color: #111827 !important; }
.perm-hint { font-size: 0.75rem; font-weight: 400; color: #9CA3AF; }
.perm-group { margin-top: 12px; border: 1px solid #E5E7EB; background: #FAFAFA; }
.perm-group-header {
    display: flex; align-items: center; gap: 8px; padding: 10px 14px;
    background: #F3F4F6; cursor: pointer; font-size: 0.82rem; font-weight: 700; color: #374151;
}
.perm-items { padding: 8px 14px 10px 36px; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.perm-item {
    display: flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #374151;
    cursor: pointer; padding: 3px 0;
}
.perm-item:hover { color: #111827; }
.perm-cb { width: 16px; height: 16px; accent-color: #4F46E5; cursor: pointer; flex-shrink: 0; }
.perm-group-name { font-weight: 700; }

.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
.btn-primary {
    padding: 10px 24px; background: #4F46E5; color: #fff;
    font-size: 0.85rem; font-weight: 600; border: none; cursor: pointer; transition: background 0.15s;
}
.btn-primary:hover { background: #4338CA; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-secondary {
    padding: 10px 24px; background: #fff; color: #374151;
    font-size: 0.85rem; font-weight: 600; border: 1px solid #D1D5DB; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; transition: background 0.15s;
}
.btn-secondary:hover { background: #F3F4F6; }
</style>
