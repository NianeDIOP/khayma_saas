<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({ transfer: Object });

const t = () => route().params._tenant

const statusLabels = { pending: 'En attente', completed: 'Complété', cancelled: 'Annulé' };
const statusColors = { pending: '#F59E0B', completed: '#10B981', cancelled: '#EF4444' };
</script>

<template>
    <AppLayout title="Détail transfert">
        <div style="max-width:700px;">
            <div class="page-header">
                <h1 class="page-title"><i class="fa-solid fa-right-left" style="color:#0EA5E9"></i> Transfert {{ transfer.reference }}</h1>
                <Link :href="route('app.boutique.transfers.index', { _tenant: t() })" class="btn-back">← Retour</Link>
            </div>

            <!-- Info cards -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                <div style="border:1px solid #E5E7EB;padding:12px;">
                    <div style="font-size:0.72rem;color:#6B7280;">De</div>
                    <div style="font-weight:700;">{{ transfer.from_depot?.name }}</div>
                </div>
                <div style="border:1px solid #E5E7EB;padding:12px;">
                    <div style="font-size:0.72rem;color:#6B7280;">Vers</div>
                    <div style="font-weight:700;">{{ transfer.to_depot?.name }}</div>
                </div>
                <div style="border:1px solid #E5E7EB;padding:12px;">
                    <div style="font-size:0.72rem;color:#6B7280;">Statut</div>
                    <span :style="{ padding: '2px 8px', fontSize: '0.75rem', fontWeight: 600, background: statusColors[transfer.status] + '20', color: statusColors[transfer.status] }">
                        {{ statusLabels[transfer.status] }}
                    </span>
                </div>
                <div style="border:1px solid #E5E7EB;padding:12px;">
                    <div style="font-size:0.72rem;color:#6B7280;">Par</div>
                    <div style="font-weight:600;">{{ transfer.user?.name }}</div>
                    <div style="font-size:0.72rem;color:#9CA3AF;">{{ new Date(transfer.created_at).toLocaleString('fr-FR') }}</div>
                </div>
            </div>

            <div v-if="transfer.notes" style="background:#F9FAFB;border:1px solid #E5E7EB;padding:12px;margin-bottom:20px;font-size:0.82rem;color:#6B7280;">
                {{ transfer.notes }}
            </div>

            <!-- Items -->
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Produit</th>
                        <th style="text-align:left;padding:10px;">Variante</th>
                        <th style="text-align:right;padding:10px;">Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in transfer.items" :key="item.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;font-weight:600;">{{ item.product?.name }}</td>
                        <td style="padding:10px;color:#6B7280;">{{ item.variant?.name || '—' }}</td>
                        <td style="padding:10px;text-align:right;font-weight:700;">{{ item.quantity }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #0EA5E9}
.btn-back{color:#6B7280;font-size:0.82rem;text-decoration:none}
.btn-back:hover{color:#111827}
</style>
