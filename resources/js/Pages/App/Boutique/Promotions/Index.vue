<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    promotions: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const activeOnly = ref(props.filters?.active_only || false);

function applyFilters() {
    router.get(route('app.boutique.promotions.index'), {
        search: search.value || undefined,
        active_only: activeOnly.value ? 1 : undefined,
    }, { preserveState: true });
}

function destroy(id) {
    if (confirm('Supprimer cette promotion ?')) {
        router.delete(route('app.boutique.promotions.destroy', id));
    }
}

function formatPrice(v) {
    return new Intl.NumberFormat('fr-FR').format(v);
}
</script>

<template>
    <AppLayout title="Promotions">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-tags" style="color:#F59E0B"></i> Promotions</h1>
            <Link :href="route('app.boutique.promotions.create')" class="btn-primary">
                + Nouvelle promotion
            </Link>
        </div>

        <div style="display:flex;gap:8px;margin-bottom:16px;">
            <input v-model="search" @keyup.enter="applyFilters" placeholder="Rechercher..."
                   style="flex:1;padding:8px 12px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            <label style="display:flex;align-items:center;gap:4px;font-size:0.82rem;color:#6B7280;">
                <input v-model="activeOnly" @change="applyFilters" type="checkbox" />
                Actives uniquement
            </label>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Nom</th>
                        <th style="text-align:left;padding:10px;">Produit</th>
                        <th style="text-align:center;padding:10px;">Type</th>
                        <th style="text-align:right;padding:10px;">Valeur</th>
                        <th style="text-align:center;padding:10px;">Période</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:right;padding:10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in promotions.data" :key="p.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;font-weight:600;">{{ p.name }}</td>
                        <td style="padding:10px;">{{ p.product?.name }}</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding: '2px 8px', fontSize: '0.72rem', fontWeight: 600, background: p.type === 'percentage' ? '#EEF2FF' : '#FEF3C7', color: p.type === 'percentage' ? '#4338CA' : '#92400E' }">
                                {{ p.type === 'percentage' ? 'Pourcentage' : 'Montant fixe' }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;font-weight:600;">
                            {{ p.type === 'percentage' ? p.value + '%' : formatPrice(p.value) + ' F' }}
                        </td>
                        <td style="padding:10px;text-align:center;font-size:0.78rem;color:#6B7280;">
                            {{ new Date(p.start_date).toLocaleDateString('fr-FR') }} → {{ new Date(p.end_date).toLocaleDateString('fr-FR') }}
                        </td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding: '2px 8px', fontSize: '0.72rem', fontWeight: 600, background: p.is_active ? '#ECFDF5' : '#FEF2F2', color: p.is_active ? '#065F46' : '#991B1B' }">
                                {{ p.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;">
                            <Link :href="route('app.boutique.promotions.edit', p.id)" style="color:#F59E0B;text-decoration:none;margin-right:8px;">Modifier</Link>
                            <button @click="destroy(p.id)" style="color:#EF4444;border:none;background:none;cursor:pointer;">Supprimer</button>
                        </td>
                    </tr>
                    <tr v-if="promotions.data.length === 0">
                        <td colspan="7" style="padding:40px;text-align:center;color:#9CA3AF;">Aucune promotion</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #F59E0B}
.btn-primary{background:#F59E0B;color:white;padding:8px 16px;font-size:0.82rem;font-weight:600;text-decoration:none;border:none;cursor:pointer}
.btn-primary:hover{background:#D97706}
</style>
