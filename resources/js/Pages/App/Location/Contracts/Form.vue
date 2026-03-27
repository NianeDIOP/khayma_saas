<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    assets: Array,
    customers: Array,
});

const form = useForm({
    rental_asset_id: '',
    customer_id: '',
    start_date: '',
    end_date: '',
    total_amount: '',
    deposit_amount: '',
    payment_frequency: 'monthly',
    conditions: '',
    notes: '',
});

const selectedAsset = computed(() => {
    if (!form.rental_asset_id) return null;
    return props.assets.find(a => a.id === Number(form.rental_asset_id));
});

const typeLabels = { vehicle: 'Véhicule', real_estate: 'Immobilier', equipment: 'Équipement', other: 'Autre' };

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }

function submit() {
    form.post(route('app.location.contracts.store'));
}
</script>

<template>
    <AppLayout title="Nouveau contrat">
        <div style="max-width:640px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h1 style="font-size:1.15rem;font-weight:700;">Nouveau contrat de location</h1>
                <Link :href="route('app.location.contracts.index')" style="color:#6B7280;font-size:0.82rem;text-decoration:none;">← Retour</Link>
            </div>

            <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:14px;">
                <!-- Asset -->
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Bien locatif *</label>
                    <select v-model="form.rental_asset_id" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                        <option value="">-- Sélectionner un bien --</option>
                        <option v-for="a in assets" :key="a.id" :value="a.id" :disabled="a.status === 'rented'">
                            {{ a.name }} ({{ typeLabels[a.type] }}) {{ a.status === 'rented' ? '— Loué' : '' }}
                        </option>
                    </select>
                    <div v-if="form.errors.rental_asset_id" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.rental_asset_id }}</div>
                    <div v-if="selectedAsset" style="margin-top:6px;font-size:0.78rem;color:#6B7280;">
                        Tarif jour: {{ selectedAsset.daily_rate ? fmt(selectedAsset.daily_rate) + ' F' : '—' }} |
                        Tarif mois: {{ selectedAsset.monthly_rate ? fmt(selectedAsset.monthly_rate) + ' F' : '—' }}
                    </div>
                </div>

                <!-- Customer -->
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Client *</label>
                    <select v-model="form.customer_id" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                        <option value="">-- Sélectionner un client --</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} {{ c.phone ? '(' + c.phone + ')' : '' }}</option>
                    </select>
                    <div v-if="form.errors.customer_id" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.customer_id }}</div>
                </div>

                <!-- Dates -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date début *</label>
                        <input v-model="form.start_date" type="date" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        <div v-if="form.errors.start_date" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.start_date }}</div>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date fin *</label>
                        <input v-model="form.end_date" type="date" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        <div v-if="form.errors.end_date" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.end_date }}</div>
                    </div>
                </div>

                <!-- Amounts -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Montant total *</label>
                        <input v-model.number="form.total_amount" type="number" step="0.01" min="0"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        <div v-if="form.errors.total_amount" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.total_amount }}</div>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Caution</label>
                        <input v-model.number="form.deposit_amount" type="number" step="0.01" min="0"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                </div>

                <!-- Payment frequency -->
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Fréquence de paiement *</label>
                    <select v-model="form.payment_frequency" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                        <option value="daily">Journalier</option>
                        <option value="monthly">Mensuel</option>
                        <option value="quarterly">Trimestriel</option>
                        <option value="yearly">Annuel</option>
                        <option value="one_time">Paiement unique</option>
                    </select>
                    <div v-if="form.errors.payment_frequency" style="color:#EF4444;font-size:0.75rem;margin-top:2px;">{{ form.errors.payment_frequency }}</div>
                </div>

                <!-- Conditions / Notes -->
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Conditions</label>
                    <textarea v-model="form.conditions" rows="2" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;resize:vertical;"></textarea>
                </div>
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Notes</label>
                    <textarea v-model="form.notes" rows="2" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;resize:vertical;"></textarea>
                </div>

                <div style="display:flex;gap:8px;margin-top:8px;">
                    <button type="submit" :disabled="form.processing"
                            style="padding:10px 24px;background:#111827;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">
                        {{ form.processing ? 'Enregistrement...' : 'Créer le contrat' }}
                    </button>
                    <Link :href="route('app.location.contracts.index')" style="padding:10px 24px;border:1px solid #D1D5DB;text-decoration:none;color:#374151;font-size:0.82rem;">
                        Annuler
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
