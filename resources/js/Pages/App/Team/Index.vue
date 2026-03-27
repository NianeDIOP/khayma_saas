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

            <!-- Table -->
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Rôle</th>
                            <th>Inscrit le</th>
                            <th v-if="isOwner" style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in members" :key="m.id">
                            <td class="font-semibold">{{ m.name }}</td>
                            <td>{{ m.email }}</td>
                            <td>{{ m.phone || '—' }}</td>
                            <td>
                                <span class="role-badge" :class="roleBadgeClass[m.role]">
                                    {{ roleLabels[m.role] || m.role }}
                                </span>
                            </td>
                            <td>{{ formatDate(m.joined_at) }}</td>
                            <td v-if="isOwner">
                                <div v-if="m.role !== 'owner'" class="actions">
                                    <Link :href="route('app.team.edit', { user: m.id, _tenant: company.slug })"
                                          class="btn-icon" title="Modifier">
                                        <i class="fa-solid fa-pen"></i>
                                    </Link>
                                    <button @click="removeMember(m)" class="btn-icon btn-danger" title="Retirer">
                                        <i class="fa-solid fa-user-minus"></i>
                                    </button>
                                </div>
                                <span v-else class="text-muted">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #E5E7EB; }
.data-table th { background: #F9FAFB; padding: 12px 16px; text-align: left; font-size: 0.75rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; }
.data-table td { padding: 14px 16px; font-size: 0.88rem; color: #374151; border-bottom: 1px solid #F3F4F6; }
.data-table tbody tr:hover { background: #F9FAFB; }

.font-semibold { font-weight: 600; }
.text-muted { color: #9CA3AF; font-size: 0.8rem; }

.role-badge {
    display: inline-block; font-size: 0.7rem; font-weight: 700; padding: 3px 10px;
    text-transform: uppercase; letter-spacing: 0.04em;
}
.badge-owner     { background: #FEF3C7; color: #92400E; }
.badge-manager   { background: #DBEAFE; color: #1E40AF; }
.badge-caissier  { background: #D1FAE5; color: #065F46; }
.badge-magasinier{ background: #EDE9FE; color: #5B21B6; }

.actions { display: flex; gap: 8px; }
.btn-icon {
    width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
    background: #F3F4F6; border: 1px solid #E5E7EB; cursor: pointer;
    color: #374151; font-size: 0.85rem; transition: all 0.15s; text-decoration: none;
}
.btn-icon:hover { background: #E5E7EB; }
.btn-danger:hover { background: #FEE2E2; color: #DC2626; border-color: #FECACA; }
</style>
