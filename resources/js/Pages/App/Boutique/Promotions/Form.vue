<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    promotion: { type: Object, default: null },
    products: Array,
});

const isEdit = !!props.promotion;

const t = () => route().params._tenant

const form = useForm({
    product_id: props.promotion?.product_id || '',
    name: props.promotion?.name || '',
    type: props.promotion?.type || 'percentage',
    value: props.promotion?.value || '',
    start_date: props.promotion?.start_date?.substring(0, 10) || '',
    end_date: props.promotion?.end_date?.substring(0, 10) || '',
    is_active: props.promotion?.is_active ?? true,
});

function submit() {
    if (isEdit) {
        form.put(route('app.boutique.promotions.update', { promotion: props.promotion.id, _tenant: t() }));
    } else {
        form.post(route('app.boutique.promotions.store', { _tenant: t() }));
    }
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Modifier promotion' : 'Nouvelle promotion'">
        <div style="max-width:560px;">
            <div class="page-header">
                <h1 class="page-title"><i class="fa-solid fa-tags" style="color:#F59E0B"></i> {{ isEdit ? 'Modifier la promotion' : 'Nouvelle promotion' }}</h1>
                <Link :href="route('app.boutique.promotions.index', { _tenant: t() })" class="btn-back">← Retour</Link>
            </div>

            <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Produit *</label>
                    <select v-model="form.product_id" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                        <option value="">-- Sélectionner --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ new Intl.NumberFormat('fr-FR').format(p.selling_price) }} F)</option>
                    </select>
                    <div v-if="form.errors.product_id" style="color:#EF4444;font-size:0.75rem;">{{ form.errors.product_id }}</div>
                </div>

                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Nom de la promotion *</label>
                    <input v-model="form.name" type="text" placeholder="ex: Soldes été -20%"
                           style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    <div v-if="form.errors.name" style="color:#EF4444;font-size:0.75rem;">{{ form.errors.name }}</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Type *</label>
                        <select v-model="form.type" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                            <option value="percentage">Pourcentage (%)</option>
                            <option value="fixed">Montant fixe (F)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Valeur *</label>
                        <input v-model.number="form.value" type="number" step="0.01" min="0.01"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        <div v-if="form.errors.value" style="color:#EF4444;font-size:0.75rem;">{{ form.errors.value }}</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date début *</label>
                        <input v-model="form.start_date" type="date" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        <div v-if="form.errors.start_date" style="color:#EF4444;font-size:0.75rem;">{{ form.errors.start_date }}</div>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date fin *</label>
                        <input v-model="form.end_date" type="date" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        <div v-if="form.errors.end_date" style="color:#EF4444;font-size:0.75rem;">{{ form.errors.end_date }}</div>
                    </div>
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;">
                    <input v-model="form.is_active" type="checkbox" />
                    Promotion active
                </label>

                <button type="submit" :disabled="form.processing"
                        style="padding:10px;background:#F59E0B;color:white;border:none;font-weight:600;cursor:pointer;font-size:0.9rem;">
                    {{ form.processing ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer la promotion') }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #F59E0B}
.btn-back{color:#6B7280;font-size:0.82rem;text-decoration:none}
.btn-back:hover{color:#111827}
</style>
