<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    transfers: Object,
    filters: Object,
});

const status = ref(props.filters?.status || '');

function applyFilters() {
    router.get(route('app.boutique.transfers.index'), {
        status: status.value || undefined,
    }, { preserveState: true });
}

const statusLabels = { pending: 'En attente', completed: 'Complété', cancelled: 'Annulé' };
const statusColors = { pending: '#F59E0B', completed: '#10B981', cancelled: '#EF4444' };
</script>

<template>
    <AppLayout title="Transferts inter-dépôts">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h1 style="font-size:1.15rem;font-weight:700;">Transferts inter-dépôts</h1>
            <Link :href="route('app.boutique.transfers.create')" style="background:#0891B2;color:white;padding:8px 16px;font-size:0.82rem;font-weight:600;text-decoration:none;">
                + Nouveau transfert
            </Link>
        </div>

        <div style="display:flex;gap:8px;margin-bottom:16px;">
            <select v-model="status" @change="applyFilters" style="padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                <option value="">Tous statuts</option>
                <option value="pending">En attente</option>
                <option value="completed">Complété</option>
                <option value="cancelled">Annulé</option>
            </select>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Référence</th>
                        <th style="text-align:left;padding:10px;">De</th>
                        <th style="text-align:left;padding:10px;">Vers</th>
                        <th style="text-align:left;padding:10px;">Par</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:left;padding:10px;">Date</th>
                        <th style="text-align:right;padding:10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in transfers.data" :key="t.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;font-weight:600;font-family:monospace;">{{ t.reference }}</td>
                        <td style="padding:10px;">{{ t.from_depot?.name }}</td>
                        <td style="padding:10px;">{{ t.to_depot?.name }}</td>
                        <td style="padding:10px;color:#6B7280;">{{ t.user?.name }}</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding: '2px 8px', fontSize: '0.72rem', fontWeight: 600, background: statusColors[t.status] + '20', color: statusColors[t.status] }">
                                {{ statusLabels[t.status] }}
                            </span>
                        </td>
                        <td style="padding:10px;color:#6B7280;">{{ new Date(t.created_at).toLocaleDateString('fr-FR') }}</td>
                        <td style="padding:10px;text-align:right;">
                            <Link :href="route('app.boutique.transfers.show', t.id)" style="color:#0891B2;text-decoration:none;">Détail</Link>
                        </td>
                    </tr>
                    <tr v-if="transfers.data.length === 0">
                        <td colspan="7" style="padding:40px;text-align:center;color:#9CA3AF;">Aucun transfert</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
