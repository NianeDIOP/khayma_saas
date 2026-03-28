<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    assets: Array,
    contracts: Array,
    expiringContracts: Array,
    overduePayments: Array,
    filters: Object,
});

const typeFilter = ref(props.filters?.type || '');

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—'; }

const typeLabels = { vehicle: 'Véhicule', real_estate: 'Immobilier', equipment: 'Équipement', other: 'Autre' };
const typeColors = { vehicle: '#3B82F6', real_estate: '#10B981', equipment: '#F59E0B', other: '#6B7280' };
const statusLabels = { available: 'Disponible', rented: 'Loué', maintenance: 'Maintenance', out_of_service: 'Hors service' };
const statusColors = { available: '#10B981', rented: '#3B82F6', maintenance: '#F59E0B', out_of_service: '#EF4444' };

const filteredAssets = computed(() => {
    if (!typeFilter.value) return props.assets;
    return props.assets.filter(a => a.type === typeFilter.value);
});

// Group contracts by asset for timeline display
const contractsByAsset = computed(() => {
    const map = {};
    props.contracts.forEach(c => {
        if (!map[c.rental_asset_id]) map[c.rental_asset_id] = [];
        map[c.rental_asset_id].push(c);
    });
    return map;
});

function daysRemaining(endDate) {
    const diff = Math.ceil((new Date(endDate) - new Date()) / (1000 * 60 * 60 * 24));
    return diff;
}
</script>

<template>
    <AppLayout title="Calendrier location">
        <h1 class="page-title"><i class="fa-solid fa-calendar-days" style="color:#8B5CF6"></i> Calendrier des locations</h1>

        <!-- Alerts -->
        <div v-if="expiringContracts.length || overduePayments.length" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
            <!-- Expiring contracts -->
            <div v-if="expiringContracts.length" style="border:2px solid #F59E0B;padding:16px;">
                <h3 style="font-size:0.88rem;font-weight:700;color:#F59E0B;margin-bottom:8px;">
                    ⚠ Contrats expirant bientôt ({{ expiringContracts.length }})
                </h3>
                <div v-for="ec in expiringContracts" :key="ec.id" style="padding:6px 0;border-bottom:1px solid #FEF3C7;font-size:0.82rem;">
                    <Link :href="route('app.location.contracts.show', ec.id)" style="color:#6366F1;text-decoration:none;font-weight:600;">{{ ec.reference }}</Link>
                    — {{ ec.rental_asset?.name }} ({{ ec.customer?.name }})
                    <span style="color:#F59E0B;font-weight:600;margin-left:4px;">{{ daysRemaining(ec.end_date) }}j restants</span>
                </div>
            </div>

            <!-- Overdue payments -->
            <div v-if="overduePayments.length" style="border:2px solid #EF4444;padding:16px;">
                <h3 style="font-size:0.88rem;font-weight:700;color:#EF4444;margin-bottom:8px;">
                    🔴 Paiements en retard ({{ overduePayments.length }})
                </h3>
                <div v-for="op in overduePayments" :key="op.id" style="padding:6px 0;border-bottom:1px solid #FEE2E2;font-size:0.82rem;">
                    {{ op.rental_contract?.rental_asset?.name }} — {{ op.rental_contract?.customer?.name }}
                    <span style="color:#EF4444;font-weight:600;margin-left:4px;">{{ fmt(op.amount - op.amount_paid) }} F dû le {{ fmtDt(op.due_date) }}</span>
                </div>
            </div>
        </div>

        <!-- Type filter -->
        <div style="display:flex;gap:8px;margin-bottom:16px;">
            <button @click="typeFilter = ''" :style="{ padding:'6px 14px', fontSize:'0.82rem', border:'1px solid #D1D5DB', cursor:'pointer', background: !typeFilter ? '#111827' : 'white', color: !typeFilter ? 'white' : '#374151' }">
                Tous
            </button>
            <button v-for="(label, key) in typeLabels" :key="key" @click="typeFilter = key"
                    :style="{ padding:'6px 14px', fontSize:'0.82rem', border:'1px solid #D1D5DB', cursor:'pointer', background: typeFilter === key ? typeColors[key] : 'white', color: typeFilter === key ? 'white' : '#374151' }">
                {{ label }}
            </button>
        </div>

        <!-- Asset timeline table -->
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;min-width:180px;">Bien</th>
                        <th style="text-align:left;padding:10px;">Type</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:left;padding:10px;">Contrat actuel</th>
                        <th style="text-align:left;padding:10px;">Client</th>
                        <th style="text-align:left;padding:10px;">Période</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in filteredAssets" :key="a.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;font-weight:600;">
                            <Link :href="route('app.location.assets.show', a.id)" style="color:#374151;text-decoration:none;">{{ a.name }}</Link>
                        </td>
                        <td style="padding:10px;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: typeColors[a.type]+'20', color: typeColors[a.type] }">
                                {{ typeLabels[a.type] }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: statusColors[a.status]+'20', color: statusColors[a.status] }">
                                {{ statusLabels[a.status] }}
                            </span>
                        </td>
                        <td style="padding:10px;" colspan="3">
                            <div v-if="contractsByAsset[a.id]?.length">
                                <div v-for="ct in contractsByAsset[a.id]" :key="ct.id" style="padding:2px 0;">
                                    <Link :href="route('app.location.contracts.show', ct.id)" style="color:#6366F1;text-decoration:none;font-weight:600;">{{ ct.reference }}</Link>
                                    <span style="margin-left:8px;">{{ ct.customer?.name }}</span>
                                    <span style="margin-left:8px;color:#6B7280;">{{ fmtDt(ct.start_date) }} → {{ fmtDt(ct.end_date) }}</span>
                                </div>
                            </div>
                            <span v-else style="color:#9CA3AF;">Aucun contrat actif</span>
                        </td>
                    </tr>
                    <tr v-if="filteredAssets.length === 0">
                        <td colspan="6" style="padding:40px;text-align:center;color:#9CA3AF;">Aucun bien</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #8B5CF6; margin-bottom: 20px; }
</style>
