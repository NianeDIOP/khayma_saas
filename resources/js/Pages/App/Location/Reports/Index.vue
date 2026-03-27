<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    totalAssets: Number,
    availableAssets: Number,
    rentedAssets: Number,
    maintenanceAssets: Number,
    assetsByType: Array,
    totalRevenue: [Number, String],
    expectedPayments: [Number, String],
    receivedPayments: [Number, String],
    revenueByType: Array,
    activeContracts: Number,
    completedContracts: Number,
    overdueContracts: Number,
    renewedContracts: Number,
    totalDebts: [Number, String],
    topDebtors: Array,
    occupancyRate: [Number, String],
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

function applyFilters() {
    router.get(route('app.location.reports.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
}

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }

const typeLabels = { vehicle: 'Véhicule', real_estate: 'Immobilier', equipment: 'Équipement', other: 'Autre' };
</script>

<template>
    <AppLayout title="Rapports Location">
        <h1 style="font-size:1.15rem;font-weight:700;margin-bottom:16px;">Rapports Location</h1>

        <!-- Date filter -->
        <div style="display:flex;gap:8px;margin-bottom:20px;align-items:end;">
            <div>
                <label style="font-size:0.72rem;color:#6B7280;">Début</label>
                <input v-model="startDate" type="date" style="padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            </div>
            <div>
                <label style="font-size:0.72rem;color:#6B7280;">Fin</label>
                <input v-model="endDate" type="date" style="padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            </div>
            <button @click="applyFilters" style="padding:6px 14px;background:#111827;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">Filtrer</button>
        </div>

        <!-- KPI Row 1 - Assets -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Total biens</div>
                <div style="font-size:1.3rem;font-weight:800;color:#111827;">{{ totalAssets }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Disponibles</div>
                <div style="font-size:1.3rem;font-weight:800;color:#10B981;">{{ availableAssets }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Loués</div>
                <div style="font-size:1.3rem;font-weight:800;color:#3B82F6;">{{ rentedAssets }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Taux d'occupation</div>
                <div style="font-size:1.3rem;font-weight:800;color:#8B5CF6;">{{ occupancyRate }}%</div>
            </div>
        </div>

        <!-- KPI Row 2 - Revenue -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Revenus perçus</div>
                <div style="font-size:1.3rem;font-weight:800;color:#10B981;">{{ fmt(totalRevenue) }} F</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Paiements attendus</div>
                <div style="font-size:1.1rem;font-weight:700;color:#F59E0B;">{{ fmt(expectedPayments) }} F</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Paiements reçus</div>
                <div style="font-size:1.1rem;font-weight:700;color:#3B82F6;">{{ fmt(receivedPayments) }} F</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Dettes totales</div>
                <div style="font-size:1.3rem;font-weight:800;color:#EF4444;">{{ fmt(totalDebts) }} F</div>
            </div>
        </div>

        <!-- KPI Row 3 - Contracts -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Contrats actifs</div>
                <div style="font-size:1.3rem;font-weight:800;color:#10B981;">{{ activeContracts }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Contrats terminés</div>
                <div style="font-size:1.1rem;font-weight:700;color:#6B7280;">{{ completedContracts }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Contrats en retard</div>
                <div style="font-size:1.3rem;font-weight:800;color:#EF4444;">{{ overdueContracts }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Contrats renouvelés</div>
                <div style="font-size:1.1rem;font-weight:700;color:#8B5CF6;">{{ renewedContracts }}</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <!-- Assets by type -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Biens par type</h2>
                <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                    <thead>
                        <tr style="border-bottom:1px solid #E5E7EB;">
                            <th style="text-align:left;padding:6px;">Type</th>
                            <th style="text-align:right;padding:6px;">Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in assetsByType" :key="item.type" style="border-bottom:1px solid #F3F4F6;">
                            <td style="padding:6px;">{{ typeLabels[item.type] || item.type }}</td>
                            <td style="padding:6px;text-align:right;font-weight:600;">{{ item.count }}</td>
                        </tr>
                        <tr v-if="!assetsByType?.length">
                            <td colspan="2" style="padding:16px;text-align:center;color:#9CA3AF;">Aucune donnée</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Revenue by type -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Revenus par type</h2>
                <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                    <thead>
                        <tr style="border-bottom:1px solid #E5E7EB;">
                            <th style="text-align:left;padding:6px;">Type</th>
                            <th style="text-align:right;padding:6px;">Revenus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in revenueByType" :key="item.type" style="border-bottom:1px solid #F3F4F6;">
                            <td style="padding:6px;">{{ typeLabels[item.type] || item.type }}</td>
                            <td style="padding:6px;text-align:right;font-weight:600;">{{ fmt(item.revenue) }} F</td>
                        </tr>
                        <tr v-if="!revenueByType?.length">
                            <td colspan="2" style="padding:16px;text-align:center;color:#9CA3AF;">Aucune donnée</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top debtors -->
        <div style="border:1px solid #E5E7EB;padding:16px;">
            <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Top 10 débiteurs</h2>
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="border-bottom:1px solid #E5E7EB;">
                        <th style="text-align:left;padding:6px;">#</th>
                        <th style="text-align:left;padding:6px;">Client</th>
                        <th style="text-align:right;padding:6px;">Dette</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(d, i) in topDebtors" :key="i" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:6px;color:#6B7280;">{{ i + 1 }}</td>
                        <td style="padding:6px;font-weight:600;">{{ d.name }}</td>
                        <td style="padding:6px;text-align:right;font-weight:700;color:#EF4444;">{{ fmt(d.debt) }} F</td>
                    </tr>
                    <tr v-if="!topDebtors?.length">
                        <td colspan="3" style="padding:16px;text-align:center;color:#9CA3AF;">Aucun débiteur</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
