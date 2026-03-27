<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    variants: Object,
    products: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const productId = ref(props.filters?.product_id || '');

function applyFilters() {
    router.get(route('app.boutique.variants.index'), {
        search: search.value || undefined,
        product_id: productId.value || undefined,
    }, { preserveState: true });
}

function destroy(id) {
    if (confirm('Supprimer cette variante ?')) {
        router.delete(route('app.boutique.variants.destroy', id));
    }
}
</script>

<template>
    <AppLayout title="Variantes produits">
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h1 style="font-size:1.15rem;font-weight:700;">Variantes produits</h1>
            <Link :href="route('app.boutique.variants.create')" style="background:#6366F1;color:white;padding:8px 16px;font-size:0.82rem;font-weight:600;text-decoration:none;">
                + Nouvelle variante
            </Link>
        </div>

        <!-- Filters -->
        <div class="filters-bar" style="display:flex;gap:8px;margin-bottom:16px;">
            <input v-model="search" @keyup.enter="applyFilters" placeholder="Rechercher..."
                   style="flex:1;padding:8px 12px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            <select v-model="productId" @change="applyFilters" style="padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                <option value="">Tous produits</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="data-table" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Produit</th>
                        <th style="text-align:left;padding:10px;">Variante</th>
                        <th style="text-align:left;padding:10px;">SKU</th>
                        <th style="text-align:left;padding:10px;">Code-barres</th>
                        <th style="text-align:right;padding:10px;">Prix</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:right;padding:10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="v in variants.data" :key="v.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;">{{ v.product?.name }}</td>
                        <td style="padding:10px;font-weight:600;">{{ v.name }}</td>
                        <td style="padding:10px;color:#6B7280;">{{ v.sku || '—' }}</td>
                        <td style="padding:10px;color:#6B7280;">{{ v.barcode || '—' }}</td>
                        <td style="padding:10px;text-align:right;">{{ v.price_override ? new Intl.NumberFormat('fr-FR').format(v.price_override) + ' F' : 'Par défaut' }}</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding: '2px 8px', fontSize: '0.72rem', fontWeight: 600, background: v.is_active ? '#ECFDF5' : '#FEF2F2', color: v.is_active ? '#065F46' : '#991B1B' }">
                                {{ v.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;">
                            <Link :href="route('app.boutique.variants.edit', v.id)" style="color:#6366F1;text-decoration:none;margin-right:8px;">Modifier</Link>
                            <button @click="destroy(v.id)" style="color:#EF4444;border:none;background:none;cursor:pointer;">Supprimer</button>
                        </td>
                    </tr>
                    <tr v-if="variants.data.length === 0">
                        <td colspan="7" style="padding:40px;text-align:center;color:#9CA3AF;">Aucune variante</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
