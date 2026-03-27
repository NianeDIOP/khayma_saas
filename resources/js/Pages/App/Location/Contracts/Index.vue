<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    contracts: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

function applyFilters() {
    router.get(route('app.location.contracts.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
}

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—'; }

const statusLabels = { active: 'Actif', completed: 'Terminé', overdue: 'En retard', cancelled: 'Annulé', renewed: 'Renouvelé' };
const statusColors = { active: '#10B981', completed: '#6B7280', overdue: '#EF4444', cancelled: '#9CA3AF', renewed: '#8B5CF6' };
</script>

<template>
    <AppLayout title="Contrats de location">
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h1 style="font-size:1.15rem;font-weight:700;">Contrats de location</h1>
            <Link :href="route('app.location.contracts.create')" style="background:#6366F1;color:white;padding:8px 16px;font-size:0.82rem;font-weight:600;text-decoration:none;">
                + Nouveau contrat
            </Link>
        </div>

        <div v-if="$page.props.flash?.success" style="background:#ECFDF5;color:#065F46;padding:10px 14px;font-size:0.82rem;margin-bottom:12px;">
            {{ $page.props.flash.success }}
        </div>

        <!-- Filters -->
        <div class="filters-bar" style="display:flex;gap:8px;margin-bottom:16px;">
            <input v-model="search" @keyup.enter="applyFilters" placeholder="Rechercher (réf, client, bien)..."
                   style="flex:1;padding:8px 12px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            <select v-model="status" @change="applyFilters" style="padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                <option value="">Tous statuts</option>
                <option value="active">Actif</option>
                <option value="completed">Terminé</option>
                <option value="overdue">En retard</option>
                <option value="cancelled">Annulé</option>
                <option value="renewed">Renouvelé</option>
            </select>
        </div>

        <!-- Table -->
        <div class="data-table" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Référence</th>
                        <th style="text-align:left;padding:10px;">Bien</th>
                        <th style="text-align:left;padding:10px;">Client</th>
                        <th style="text-align:left;padding:10px;">Début</th>
                        <th style="text-align:left;padding:10px;">Fin</th>
                        <th style="text-align:right;padding:10px;">Montant</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:right;padding:10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in contracts.data" :key="c.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;font-weight:600;">{{ c.reference }}</td>
                        <td style="padding:10px;">{{ c.rental_asset?.name || '—' }}</td>
                        <td style="padding:10px;">{{ c.customer?.name || '—' }}</td>
                        <td style="padding:10px;">{{ fmtDt(c.start_date) }}</td>
                        <td style="padding:10px;">{{ fmtDt(c.end_date) }}</td>
                        <td style="padding:10px;text-align:right;">{{ fmt(c.total_amount) }} F</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: statusColors[c.status]+'20', color: statusColors[c.status] }">
                                {{ statusLabels[c.status] }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;">
                            <Link :href="route('app.location.contracts.show', c.id)" style="color:#6366F1;text-decoration:none;">Voir</Link>
                        </td>
                    </tr>
                    <tr v-if="contracts.data.length === 0">
                        <td colspan="8" style="padding:40px;text-align:center;color:#9CA3AF;">Aucun contrat</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="contracts.links?.length > 3" style="display:flex;gap:4px;margin-top:16px;justify-content:center;">
            <template v-for="link in contracts.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" v-html="link.label"
                      :style="{ padding:'6px 12px', fontSize:'0.78rem', border:'1px solid #D1D5DB', textDecoration:'none',
                                background: link.active ? '#111827' : 'white', color: link.active ? 'white' : '#374151' }" />
                <span v-else v-html="link.label" style="padding:6px 12px;font-size:0.78rem;color:#9CA3AF;"></span>
            </template>
        </div>
    </AppLayout>
</template>
