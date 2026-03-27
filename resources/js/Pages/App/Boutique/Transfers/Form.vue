<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    depots: Array,
    products: Array,
});

const form = ref({
    from_depot_id: '',
    to_depot_id: '',
    notes: '',
    items: [{ product_id: '', variant_id: null, quantity: 1 }],
});
const processing = ref(false);

function addLine() {
    form.value.items.push({ product_id: '', variant_id: null, quantity: 1 });
}

function removeLine(idx) {
    form.value.items.splice(idx, 1);
}

function getVariants(productId) {
    const product = props.products.find(p => p.id === productId);
    return product?.variants || [];
}

function submit() {
    processing.value = true;
    router.post(route('app.boutique.transfers.store'), form.value, {
        onFinish: () => { processing.value = false; },
    });
}
</script>

<template>
    <AppLayout title="Nouveau transfert">
        <div style="max-width:700px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h1 style="font-size:1.15rem;font-weight:700;">Nouveau transfert inter-dépôts</h1>
                <Link :href="route('app.boutique.transfers.index')" style="color:#6B7280;font-size:0.82rem;text-decoration:none;">← Retour</Link>
            </div>

            <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:14px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Dépôt source *</label>
                        <select v-model="form.from_depot_id" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                            <option value="">-- Sélectionner --</option>
                            <option v-for="d in depots" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Dépôt destination *</label>
                        <select v-model="form.to_depot_id" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                            <option value="">-- Sélectionner --</option>
                            <option v-for="d in depots" :key="d.id" :value="d.id" :disabled="d.id === form.from_depot_id">{{ d.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Notes</label>
                    <textarea v-model="form.notes" rows="2" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;font-size:0.82rem;"></textarea>
                </div>

                <!-- Items -->
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Produits à transférer</label>
                        <button type="button" @click="addLine" style="font-size:0.78rem;color:#0891B2;background:none;border:none;cursor:pointer;font-weight:600;">+ Ajouter ligne</button>
                    </div>
                    <div v-for="(item, idx) in form.items" :key="idx" style="display:flex;gap:8px;margin-bottom:8px;align-items:end;">
                        <div style="flex:2;">
                            <select v-model="item.product_id" style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;">
                                <option value="">Produit</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div v-if="getVariants(item.product_id).length" style="flex:1;">
                            <select v-model="item.variant_id" style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;">
                                <option :value="null">Sans variante</option>
                                <option v-for="v in getVariants(item.product_id)" :key="v.id" :value="v.id">{{ v.name }}</option>
                            </select>
                        </div>
                        <div style="flex:0 0 80px;">
                            <input v-model.number="item.quantity" type="number" min="0.01" step="0.01"
                                   style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" placeholder="Qté" />
                        </div>
                        <button type="button" @click="removeLine(idx)" v-if="form.items.length > 1"
                                style="color:#EF4444;border:none;background:none;cursor:pointer;font-size:1.1rem;">✕</button>
                    </div>
                </div>

                <button type="submit" :disabled="processing"
                        style="padding:10px;background:#0891B2;color:white;border:none;font-weight:600;cursor:pointer;font-size:0.9rem;">
                    {{ processing ? 'Transfert...' : 'Effectuer le transfert' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
