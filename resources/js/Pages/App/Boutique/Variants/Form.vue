<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    variant: { type: Object, default: null },
    products: Array,
});

const isEdit = !!props.variant;

const form = useForm({
    product_id: props.variant?.product_id || '',
    name: props.variant?.name || '',
    sku: props.variant?.sku || '',
    barcode: props.variant?.barcode || '',
    price_override: props.variant?.price_override || '',
    purchase_price_override: props.variant?.purchase_price_override || '',
    attributes: props.variant?.attributes || {},
    is_active: props.variant?.is_active ?? true,
});

// Dynamic attributes
const attrKey = defineModel('attrKey', { default: '' });
const attrVal = defineModel('attrVal', { default: '' });

function addAttribute() {
    if (attrKey.value && attrVal.value) {
        form.attributes = { ...form.attributes, [attrKey.value]: attrVal.value };
        attrKey.value = '';
        attrVal.value = '';
    }
}

function removeAttribute(key) {
    const copy = { ...form.attributes };
    delete copy[key];
    form.attributes = copy;
}

function submit() {
    if (isEdit) {
        form.put(route('app.boutique.variants.update', props.variant.id));
    } else {
        form.post(route('app.boutique.variants.store'));
    }
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Modifier variante' : 'Nouvelle variante'">
        <div style="max-width:640px;">
            <div class="page-header">
                <h1 class="page-title"><i class="fa-solid fa-swatchbook" style="color:#6366F1"></i> {{ isEdit ? 'Modifier la variante' : 'Nouvelle variante' }}</h1>
                <Link :href="route('app.boutique.variants.index')" class="btn-back">← Retour</Link>
            </div>

            <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Produit *</label>
                    <select v-model="form.product_id" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                        <option value="">-- Sélectionner --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <div v-if="form.errors.product_id" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.product_id }}</div>
                </div>

                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Nom de la variante *</label>
                    <input v-model="form.name" type="text" placeholder="ex: Rouge - XL"
                           style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    <div v-if="form.errors.name" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.name }}</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">SKU</label>
                        <input v-model="form.sku" type="text" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Code-barres</label>
                        <input v-model="form.barcode" type="text" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Prix vente (override)</label>
                        <input v-model.number="form.price_override" type="number" step="0.01" min="0"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" placeholder="Laisser vide = prix du produit" />
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Prix achat (override)</label>
                        <input v-model.number="form.purchase_price_override" type="number" step="0.01" min="0"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" placeholder="Laisser vide = prix du produit" />
                    </div>
                </div>

                <!-- Attributes -->
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Attributs</label>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <input v-model="attrKey" type="text" placeholder="Clé (ex: couleur)" style="flex:1;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        <input v-model="attrVal" type="text" placeholder="Valeur (ex: Rouge)" style="flex:1;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        <button type="button" @click="addAttribute" style="padding:6px 12px;background:#6366F1;color:white;border:none;cursor:pointer;font-size:0.82rem;">+</button>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
                        <span v-for="(val, key) in form.attributes" :key="key"
                              style="background:#EEF2FF;color:#4338CA;padding:4px 8px;font-size:0.75rem;display:inline-flex;align-items:center;gap:4px;">
                            {{ key }}: {{ val }}
                            <button type="button" @click="removeAttribute(key)" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:0.8rem;">×</button>
                        </span>
                    </div>
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;">
                    <input v-model="form.is_active" type="checkbox" />
                    Variante active
                </label>

                <button type="submit" :disabled="form.processing"
                        style="padding:10px;background:#6366F1;color:white;border:none;font-weight:600;cursor:pointer;font-size:0.9rem;">
                    {{ form.processing ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer la variante') }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #6366F1}
.btn-back{color:#6B7280;font-size:0.82rem;text-decoration:none}
.btn-back:hover{color:#111827}
</style>
