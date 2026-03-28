<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({ asset: Object });

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—'; }

const typeLabels = { vehicle: 'Véhicule', real_estate: 'Immobilier', equipment: 'Équipement', other: 'Autre' };
const statusLabels = { available: 'Disponible', rented: 'Loué', maintenance: 'Maintenance', out_of_service: 'Hors service' };
const statusColors = { available: '#10B981', rented: '#3B82F6', maintenance: '#F59E0B', out_of_service: '#EF4444' };
const contractStatusLabels = { active: 'Actif', completed: 'Terminé', overdue: 'En retard', cancelled: 'Annulé', renewed: 'Renouvelé' };
const contractStatusColors = { active: '#10B981', completed: '#6B7280', overdue: '#EF4444', cancelled: '#9CA3AF', renewed: '#8B5CF6' };
</script>

<template>
    <AppLayout title="Détail bien">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-building" style="color:#0EA5E9"></i> {{ asset.name }}</h1>
            <div class="header-actions">
                <Link :href="route('app.location.assets.edit', asset.id)" class="btn-edit">Modifier</Link>
                <Link :href="route('app.location.assets.index')" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</Link>
            </div>
        </div>

        <!-- Info cards -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Type</div>
                <div style="font-size:1rem;font-weight:700;">{{ typeLabels[asset.type] }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Tarif journalier</div>
                <div style="font-size:1rem;font-weight:700;color:#3B82F6;">{{ asset.daily_rate ? fmt(asset.daily_rate) + ' F' : '—' }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Tarif mensuel</div>
                <div style="font-size:1rem;font-weight:700;color:#10B981;">{{ asset.monthly_rate ? fmt(asset.monthly_rate) + ' F' : '—' }}</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;margin-bottom:4px;">Statut</div>
                <span :style="{ padding:'4px 12px', fontSize:'0.82rem', fontWeight:600, background: statusColors[asset.status]+'20', color: statusColors[asset.status] }">
                    {{ statusLabels[asset.status] }}
                </span>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;margin-bottom:4px;">État</div>
                <span :style="{ padding:'4px 12px', fontSize:'0.82rem', fontWeight:600, background: asset.is_active ? '#ECFDF5' : '#FEF2F2', color: asset.is_active ? '#065F46' : '#991B1B' }">
                    {{ asset.is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>

        <!-- Description -->
        <div v-if="asset.description" style="border:1px solid #E5E7EB;padding:16px;margin-bottom:20px;">
            <div style="font-size:0.78rem;font-weight:600;color:#374151;margin-bottom:4px;">Description</div>
            <p style="font-size:0.82rem;color:#6B7280;white-space:pre-wrap;">{{ asset.description }}</p>
        </div>

        <!-- Characteristics -->
        <div v-if="asset.characteristics && Object.keys(asset.characteristics).length" style="border:1px solid #E5E7EB;padding:16px;margin-bottom:20px;">
            <div style="font-size:0.78rem;font-weight:600;color:#374151;margin-bottom:8px;">Caractéristiques</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                <span v-for="(val, key) in asset.characteristics" :key="key"
                      style="background:#EEF2FF;color:#4338CA;padding:4px 10px;font-size:0.78rem;">
                    {{ key }}: {{ val }}
                </span>
            </div>
        </div>

        <!-- Inspection notes -->
        <div v-if="asset.inspection_notes" style="border:1px solid #E5E7EB;padding:16px;margin-bottom:20px;">
            <div style="font-size:0.78rem;font-weight:600;color:#374151;margin-bottom:4px;">Notes d'inspection</div>
            <p style="font-size:0.82rem;color:#6B7280;white-space:pre-wrap;">{{ asset.inspection_notes }}</p>
        </div>

        <!-- Contracts history -->
        <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Historique des contrats</h2>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Référence</th>
                        <th style="text-align:left;padding:10px;">Client</th>
                        <th style="text-align:left;padding:10px;">Début</th>
                        <th style="text-align:left;padding:10px;">Fin</th>
                        <th style="text-align:right;padding:10px;">Montant</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in asset.contracts" :key="c.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;">
                            <Link :href="route('app.location.contracts.show', c.id)" style="color:#6366F1;text-decoration:none;font-weight:600;">{{ c.reference }}</Link>
                        </td>
                        <td style="padding:10px;">{{ c.customer?.name || '—' }}</td>
                        <td style="padding:10px;">{{ fmtDt(c.start_date) }}</td>
                        <td style="padding:10px;">{{ fmtDt(c.end_date) }}</td>
                        <td style="padding:10px;text-align:right;">{{ fmt(c.total_amount) }} F</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: contractStatusColors[c.status]+'20', color: contractStatusColors[c.status] }">
                                {{ contractStatusLabels[c.status] }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="!asset.contracts?.length">
                        <td colspan="6" style="padding:30px;text-align:center;color:#9CA3AF;">Aucun contrat pour ce bien</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #0EA5E9; }
.header-actions { display: flex; gap: 8px; align-items: center; }
.btn-edit { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #F59E0B; color: #fff; font-size: 0.82rem; font-weight: 600; text-decoration: none; transition: background 0.15s; }
.btn-edit:hover { background: #D97706; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #F3F4F6; color: #374151; font-size: 0.78rem; font-weight: 600; text-decoration: none; border: 1px solid #E5E7EB; transition: all 0.15s; }
.btn-back:hover { background: #E5E7EB; }
</style>
