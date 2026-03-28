<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const t = () => route().params._tenant

const props = defineProps({
    asset: { type: Object, default: null },
});

const isEdit = !!props.asset;

const form = useForm({
    name: props.asset?.name || '',
    description: props.asset?.description || '',
    type: props.asset?.type || 'vehicle',
    daily_rate: props.asset?.daily_rate || '',
    monthly_rate: props.asset?.monthly_rate || '',
    status: props.asset?.status || 'available',
    characteristics: props.asset?.characteristics || {},
    inspection_notes: props.asset?.inspection_notes || '',
    is_active: props.asset?.is_active ?? true,
});

// Dynamic characteristics
const charKey = ref('');
const charVal = ref('');

function addCharacteristic() {
    if (charKey.value && charVal.value) {
        form.characteristics = { ...form.characteristics, [charKey.value]: charVal.value };
        charKey.value = '';
        charVal.value = '';
    }
}

function removeCharacteristic(key) {
    const copy = { ...form.characteristics };
    delete copy[key];
    form.characteristics = copy;
}

function submit() {
    if (isEdit) {
        form.put(route('app.location.assets.update', { asset: props.asset.id, _tenant: t() }));
    } else {
        form.post(route('app.location.assets.store', { _tenant: t() }));
    }
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Modifier bien' : 'Nouveau bien'">
        <div style="max-width:640px;">
            <div class="page-header">
                <h1 class="page-title"><i class="fa-solid fa-building" style="color:#0EA5E9"></i> {{ isEdit ? 'Modifier le bien' : 'Nouveau bien locatif' }}</h1>
                <Link :href="route('app.location.assets.index', { _tenant: t() })" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</Link>
            </div>

            <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Nom du bien *</label>
                    <input v-model="form.name" type="text" placeholder="ex: Appartement F3 Centre-ville"
                           style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    <div v-if="form.errors.name" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Description</label>
                    <textarea v-model="form.description" rows="3"
                              style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;resize:vertical;"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Type *</label>
                        <select v-model="form.type" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                            <option value="vehicle">Véhicule</option>
                            <option value="real_estate">Immobilier</option>
                            <option value="equipment">Équipement</option>
                            <option value="other">Autre</option>
                        </select>
                        <div v-if="form.errors.type" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.type }}</div>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Statut *</label>
                        <select v-model="form.status" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                            <option value="available">Disponible</option>
                            <option value="rented">Loué</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="out_of_service">Hors service</option>
                        </select>
                        <div v-if="form.errors.status" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.status }}</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Tarif journalier (F)</label>
                        <input v-model.number="form.daily_rate" type="number" step="0.01" min="0"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Tarif mensuel (F)</label>
                        <input v-model.number="form.monthly_rate" type="number" step="0.01" min="0"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                </div>

                <!-- Characteristics -->
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Caractéristiques</label>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <input v-model="charKey" type="text" placeholder="Clé (ex: superficie)" style="flex:1;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        <input v-model="charVal" type="text" placeholder="Valeur (ex: 120 m²)" style="flex:1;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        <button type="button" @click="addCharacteristic" style="padding:6px 12px;background:#6366F1;color:white;border:none;cursor:pointer;font-size:0.82rem;">+</button>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
                        <span v-for="(val, key) in form.characteristics" :key="key"
                              style="background:#EEF2FF;color:#4338CA;padding:4px 8px;font-size:0.75rem;display:inline-flex;align-items:center;gap:4px;">
                            {{ key }}: {{ val }}
                            <button type="button" @click="removeCharacteristic(key)" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:0.8rem;">×</button>
                        </span>
                    </div>
                </div>

                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Notes d'inspection</label>
                    <textarea v-model="form.inspection_notes" rows="2"
                              style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;resize:vertical;"></textarea>
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;">
                    <input v-model="form.is_active" type="checkbox" />
                    Bien actif
                </label>

                <div style="display:flex;gap:8px;margin-top:8px;">
                    <button type="submit" :disabled="form.processing"
                            style="padding:10px 24px;background:#111827;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">
                        {{ form.processing ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer le bien') }}
                    </button>
                    <Link :href="route('app.location.assets.index', { _tenant: t() })" style="padding:10px 24px;border:1px solid #D1D5DB;text-decoration:none;color:#374151;font-size:0.82rem;">
                        Annuler
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #0EA5E9; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #F3F4F6; color: #374151; font-size: 0.78rem; font-weight: 600; text-decoration: none; border: 1px solid #E5E7EB; transition: all 0.15s; }
.btn-back:hover { background: #E5E7EB; }
</style>
