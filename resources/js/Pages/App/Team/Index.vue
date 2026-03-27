<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    members: Array,
    roles: Array,
})

const page = usePage()
const company = page.props.currentCompany
const currentUser = page.props.auth?.user
const flash = page.props.flash

const roleLabels = {
    owner: 'Propriétaire',
    manager: 'Manager',
    caissier: 'Caissier',
    magasinier: 'Magasinier',
}

const roleBadgeClass = {
    owner: 'badge-owner',
    manager: 'badge-manager',
    caissier: 'badge-caissier',
    magasinier: 'badge-magasinier',
}

const isOwner = currentUser?.company_role === 'owner' || currentUser?.is_super_admin

function removeMember(member) {
    if (!confirm(`Retirer ${member.name} de l'équipe ?`)) return
    router.delete(route('app.team.destroy', { user: member.id, _tenant: company.slug }))
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function permCount(m) {
    if (m.role === 'owner') return null
    return m.permissions?.length || 0
}
</script>

<template>
    <AppLayout title="Équipe">
        <div class="team-page">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Équipe</h1>
                    <p class="page-sub">{{ members.length }} membre(s) dans l'entreprise</p>
                </div>
                <Link v-if="isOwner" :href="route('app.team.create', { _tenant: company.slug })" class="btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Ajouter un membre
                </Link>
            </div>

            <!-- Flash -->
            <div v-if="flash?.success" class="alert-success">{{ flash.success }}</div>
            <div v-if="flash?.error" class="alert-error">{{ flash.error }}</div>

            <!-- Cards -->
            <div class="member-list">
                <div v-for="m in members" :key="m.id" class="member-card">
                    <div class="member-top">
                        <div class="member-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="member-info">
                            <span class="member-name">{{ m.name }}</span>
                            <span class="member-email">{{ m.email }}</span>
                        </div>
                        <span class="role-badge" :class="roleBadgeClass[m.role]">
                            {{ roleLabels[m.role] || m.role }}
                        </span>
                    </div>

                    <div class="member-details">
                        <div class="detail-item">
                            <i class="fa-solid fa-phone" style="color:#6B7280"></i>
                            <span>{{ m.phone || 'Non renseigné' }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fa-solid fa-calendar" style="color:#6B7280"></i>
                            <span>Depuis {{ formatDate(m.joined_at) }}</span>
                        </div>
                        <div class="detail-item" v-if="m.role !== 'owner'">
                            <i class="fa-solid fa-shield-halved" style="color:#6B7280"></i>
                            <span v-if="permCount(m) > 0" class="perm-count">{{ permCount(m) }} permission(s)</span>
                            <span v-else class="perm-none">Aucune permission définie</span>
                        </div>
                        <div class="detail-item" v-else>
                            <i class="fa-solid fa-shield-halved" style="color:#F59E0B"></i>
                            <span class="perm-full">Accès complet</span>
                        </div>
                    </div>

                    <div v-if="isOwner && m.role !== 'owner'" class="member-actions">
                        <Link :href="route('app.team.edit', { user: m.id, _tenant: company.slug })"
                              class="btn-edit">
                            <i class="fa-solid fa-pen"></i> Modifier / Permissions
                        </Link>
                        <button @click="removeMember(m)" class="btn-remove" title="Retirer de l'équipe">
                            <i class="fa-solid fa-user-minus"></i>
                        </button>
                    </div>
                    <div v-else-if="m.role === 'owner'" class="member-actions">
                        <span class="owner-label"><i class="fa-solid fa-crown" style="color:#F59E0B"></i> Propriétaire</span>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="members.length === 0" class="empty-state">
                <i class="fa-solid fa-users" style="font-size:2rem;color:#D1D5DB;"></i>
                <p>Aucun membre dans l'équipe</p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.team-page { max-width: 960px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
.page-title { font-size: 1.5rem; font-weight: 800; color: #111827; margin: 0; }
.page-sub { font-size: 0.85rem; color: #6B7280; margin-top: 4px; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; background: #4F46E5; color: #fff;
    font-size: 0.85rem; font-weight: 600; border: none; cursor: pointer;
    text-decoration: none; transition: background 0.15s;
}
.btn-primary:hover { background: #4338CA; }

.alert-success { background: #D1FAE5; color: #065F46; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; font-weight: 500; }
.alert-error   { background: #FEE2E2; color: #991B1B; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; font-weight: 500; }

/* ── Member Cards ── */
.member-list { display: flex; flex-direction: column; gap: 12px; }

.member-card {
    background: #fff; border: 1px solid #E5E7EB; padding: 20px;
    transition: box-shadow 0.15s;
}
.member-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

.member-top { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
.member-avatar {
    width: 42px; height: 42px; background: #EEF2FF; color: #6366F1;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%; font-size: 1rem; flex-shrink: 0;
}
.member-info { flex: 1; min-width: 0; }
.member-name { display: block; font-size: 0.95rem; font-weight: 700; color: #111827; }
.member-email { display: block; font-size: 0.78rem; color: #6B7280; margin-top: 1px; }

.member-details {
    display: flex; flex-wrap: wrap; gap: 16px; padding: 12px 0;
    border-top: 1px solid #F3F4F6; font-size: 0.8rem; color: #374151;
}
.detail-item { display: flex; align-items: center; gap: 6px; }
.perm-count { color: #059669; font-weight: 600; }
.perm-none { color: #DC2626; font-weight: 500; font-style: italic; }
.perm-full { color: #D97706; font-weight: 600; }

.member-actions {
    display: flex; align-items: center; gap: 10px; padding-top: 12px;
    border-top: 1px solid #F3F4F6; margin-top: 2px;
}
.btn-edit {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; background: #EEF2FF; color: #4F46E5;
    font-size: 0.8rem; font-weight: 600; border: 1px solid #C7D2FE;
    cursor: pointer; text-decoration: none; transition: all 0.15s;
}
.btn-edit:hover { background: #4F46E5; color: #fff; }
.btn-remove {
    width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
    background: #FEF2F2; border: 1px solid #FECACA; cursor: pointer;
    color: #DC2626; font-size: 0.85rem; transition: all 0.15s; margin-left: auto;
}
.btn-remove:hover { background: #DC2626; color: #fff; }
.owner-label { font-size: 0.8rem; color: #92400E; font-weight: 600; display: flex; align-items: center; gap: 6px; }

.role-badge {
    display: inline-block; font-size: 0.68rem; font-weight: 700; padding: 3px 10px;
    text-transform: uppercase; letter-spacing: 0.04em; flex-shrink: 0;
}
.badge-owner     { background: #FEF3C7; color: #92400E; }
.badge-manager   { background: #DBEAFE; color: #1E40AF; }
.badge-caissier  { background: #D1FAE5; color: #065F46; }
.badge-magasinier{ background: #EDE9FE; color: #5B21B6; }

.empty-state { text-align: center; padding: 60px 20px; color: #9CA3AF; }
.empty-state p { margin-top: 12px; font-size: 0.88rem; }
</style>
