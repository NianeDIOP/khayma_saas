<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const t = () => route().params._tenant

const props = defineProps({
    assets: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');
const status = ref(props.filters?.status || '');

function applyFilters() {
    router.get(route('app.location.assets.index', { _tenant: t() }), {
        search: search.value || undefined,
        type: type.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
}

function destroy(id) {
    if (confirm('Supprimer ce bien ?')) {
        router.delete(route('app.location.assets.destroy', { asset: id, _tenant: t() }));
    }
}

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }

const typeLabels = { vehicle: 'Véhicule', real_estate: 'Immobilier', equipment: 'Équipement', other: 'Autre' };
const typeColors = { vehicle: '#3B82F6', real_estate: '#10B981', equipment: '#F59E0B', other: '#6B7280' };
const statusLabels = { available: 'Disponible', rented: 'Loué', maintenance: 'Maintenance', out_of_service: 'Hors service' };
const statusColors = { available: '#10B981', rented: '#3B82F6', maintenance: '#F59E0B', out_of_service: '#EF4444' };
</script>

<template>
    <AppLayout title="Biens locatifs">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-building" style="color:#0EA5E9"></i> Biens locatifs</h1>
            <Link :href="route('app.location.assets.create', { _tenant: t() })" class="btn-primary">
                + Nouveau bien
            </Link>
        </div>

        <div v-if="$page.props.flash?.success" style="background:#ECFDF5;color:#065F46;padding:10px 14px;font-size:0.82rem;margin-bottom:12px;">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" style="background:#FEF2F2;color:#991B1B;padding:10px 14px;font-size:0.82rem;margin-bottom:12px;">
            {{ $page.props.flash.error }}
        </div>

        <!-- Filters -->
        <div class="filters-bar" style="display:flex;gap:8px;margin-bottom:16px;">
            <input v-model="search" @keyup.enter="applyFilters" placeholder="Rechercher..."
                   style="flex:1;padding:8px 12px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            <select v-model="type" @change="applyFilters" style="padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                <option value="">Tous types</option>
                <option value="vehicle">Véhicule</option>
                <option value="real_estate">Immobilier</option>
                <option value="equipment">Équipement</option>
                <option value="other">Autre</option>
            </select>
            <select v-model="status" @change="applyFilters" style="padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                <option value="">Tous statuts</option>
                <option value="available">Disponible</option>
                <option value="rented">Loué</option>
                <option value="maintenance">Maintenance</option>
                <option value="out_of_service">Hors service</option>
            </select>
        </div>

        <!-- Table -->
        <div class="data-table" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Nom</th>
                        <th style="text-align:left;padding:10px;">Type</th>
                        <th style="text-align:right;padding:10px;">Tarif/jour</th>
                        <th style="text-align:right;padding:10px;">Tarif/mois</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:right;padding:10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in assets.data" :key="a.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;font-weight:600;">{{ a.name }}</td>
                        <td style="padding:10px;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: typeColors[a.type]+'20', color: typeColors[a.type] }">
                                {{ typeLabels[a.type] }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;">{{ a.daily_rate ? fmt(a.daily_rate) + ' F' : '—' }}</td>
                        <td style="padding:10px;text-align:right;">{{ a.monthly_rate ? fmt(a.monthly_rate) + ' F' : '—' }}</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: statusColors[a.status]+'20', color: statusColors[a.status] }">
                                {{ statusLabels[a.status] }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;">
                            <Link :href="route('app.location.assets.show', { asset: a.id, _tenant: t() })" style="color:#6366F1;text-decoration:none;margin-right:8px;">Voir</Link>
                            <Link :href="route('app.location.assets.edit', { asset: a.id, _tenant: t() })" style="color:#F59E0B;text-decoration:none;margin-right:8px;">Modifier</Link>
                            <button @click="destroy(a.id)" style="color:#EF4444;border:none;background:none;cursor:pointer;">Supprimer</button>
                        </td>
                    </tr>
                    <tr v-if="assets.data.length === 0">
                        <td colspan="6" style="padding:40px;text-align:center;color:#9CA3AF;">Aucun bien locatif</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="assets.links?.length > 3" style="display:flex;gap:4px;margin-top:16px;justify-content:center;">
            <template v-for="link in assets.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" v-html="link.label"
                      :style="{ padding:'6px 12px', fontSize:'0.78rem', border:'1px solid #D1D5DB', textDecoration:'none',
                                background: link.active ? '#111827' : 'white', color: link.active ? 'white' : '#374151' }" />
                <span v-else v-html="link.label" style="padding:6px 12px;font-size:0.78rem;color:#9CA3AF;"></span>
            </template>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #0EA5E9; }
.btn-primary { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #4F46E5; color: #fff; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: background 0.15s; }
.btn-primary:hover { background: #4338CA; }
</style>
