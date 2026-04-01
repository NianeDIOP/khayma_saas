<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    config: Object,
    transactions: Object,
    topCustomers: Array,
    totalEarned: Number,
    totalRedeemed: Number,
    tiers: Array,
    filters: Object,
});

const flash = computed(() => usePage().props.flash);
const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');

const t = () => route().params._tenant

const configForm = useForm({
    points_per_amount: props.config?.points_per_amount || 1,
    amount_per_point: props.config?.amount_per_point || 1000,
    redemption_threshold: props.config?.redemption_threshold || 100,
    redemption_value: props.config?.redemption_value || 2000,
    is_active: props.config?.is_active ?? false,
});

function saveConfig() {
    configForm.put(route('app.boutique.loyalty.update-config', { _tenant: t() }));
}

function applyFilters() {
    router.get(route('app.boutique.loyalty.index', { _tenant: t() }), {
        search: search.value || undefined,
        type: type.value || undefined,
    }, { preserveState: true });
}

function formatPrice(v) {
    return new Intl.NumberFormat('fr-FR').format(v);
}

const tierForm = useForm({
    name: '',
    min_points: 0,
    bonus_multiplier: 1.5,
});

function addTier() {
    tierForm.post(route('app.boutique.loyalty.tiers.store', { _tenant: t() }), {
        onSuccess: () => tierForm.reset(),
    });
}

function deleteTier(id) {
    if (confirm('Supprimer ce palier ?')) {
        router.delete(route('app.boutique.loyalty.tiers.destroy', { _tenant: t(), tier: id }));
    }
}
</script>

<template>
    <AppLayout title="Fidélité">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-heart" style="color:#EC4899"></i> Programme de fidélité</h1>
        </div>

        <div v-if="flash?.success" style="background:#ECFDF5;border:1px solid #6EE7B7;padding:10px 16px;margin-bottom:16px;color:#065F46;font-size:0.85rem;">
            {{ flash.success }}
        </div>

        <!-- Stats cards -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.75rem;color:#6B7280;">Points distribués</div>
                <div style="font-size:1.3rem;font-weight:800;color:#8B5CF6;">{{ formatPrice(totalEarned) }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.75rem;color:#6B7280;">Points utilisés</div>
                <div style="font-size:1.3rem;font-weight:800;color:#EF4444;">{{ formatPrice(totalRedeemed) }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.75rem;color:#6B7280;">Top client</div>
                <div style="font-size:1rem;font-weight:700;color:#111827;">{{ topCustomers[0]?.name || '—' }}</div>
                <div style="font-size:0.78rem;color:#8B5CF6;">{{ topCustomers[0]?.loyalty_points || 0 }} pts</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <!-- Config -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Configuration</h2>
                <form @submit.prevent="saveConfig" style="display:flex;flex-direction:column;gap:10px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label style="font-size:0.72rem;color:#6B7280;">Points par palier</label>
                            <input v-model.number="configForm.points_per_amount" type="number" min="1"
                                   style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        </div>
                        <div>
                            <label style="font-size:0.72rem;color:#6B7280;">Montant par point (F)</label>
                            <input v-model.number="configForm.amount_per_point" type="number" min="1"
                                   style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label style="font-size:0.72rem;color:#6B7280;">Seuil de rédemption (pts)</label>
                            <input v-model.number="configForm.redemption_threshold" type="number" min="1"
                                   style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        </div>
                        <div>
                            <label style="font-size:0.72rem;color:#6B7280;">Valeur rédemption (F)</label>
                            <input v-model.number="configForm.redemption_value" type="number" min="1"
                                   style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        </div>
                    </div>
                    <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;">
                        <input v-model="configForm.is_active" type="checkbox" />
                        Programme actif
                    </label>
                    <div style="font-size:0.75rem;color:#6B7280;background:#F9FAFB;padding:8px;">
                        Exemple : {{ configForm.points_per_amount }} point(s) gagné(s) pour chaque {{ formatPrice(configForm.amount_per_point) }} F dépensé(s).
                        {{ configForm.redemption_threshold }} points = {{ formatPrice(configForm.redemption_value) }} F de réduction.
                    </div>
                    <button type="submit" :disabled="configForm.processing"
                            style="padding:8px;background:#8B5CF6;color:white;border:none;font-weight:600;cursor:pointer;font-size:0.82rem;">
                        {{ configForm.processing ? 'Enregistrement...' : 'Sauvegarder' }}
                    </button>
                </form>
            </div>

            <!-- Top customers -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Top clients (par points)</h2>
                <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                    <tbody>
                        <tr v-for="c in topCustomers" :key="c.id" style="border-bottom:1px solid #F3F4F6;">
                            <td style="padding:6px 0;font-weight:600;">{{ c.name }}</td>
                            <td style="padding:6px 0;color:#6B7280;">{{ c.phone }}</td>
                            <td style="padding:6px 0;text-align:right;font-weight:700;color:#8B5CF6;">{{ c.loyalty_points }} pts</td>
                        </tr>
                        <tr v-if="topCustomers.length === 0">
                            <td colspan="3" style="padding:20px;text-align:center;color:#9CA3AF;">Aucun client avec des points</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transactions -->
        <div style="margin-top:20px;">
            <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Paliers de fidélité</h2>
            <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                <div v-for="tier in tiers" :key="tier.id"
                     style="border:1px solid #E5E7EB;padding:10px 16px;display:flex;align-items:center;gap:12px;">
                    <div>
                        <div style="font-weight:700;font-size:0.88rem;">{{ tier.name }}</div>
                        <div style="font-size:0.72rem;color:#6B7280;">
                            ≥ {{ formatPrice(tier.min_points) }} pts — ×{{ tier.bonus_multiplier }}
                        </div>
                    </div>
                    <button @click="deleteTier(tier.id)"
                            style="padding:2px 8px;background:#FEE2E2;color:#B91C1C;border:none;font-size:0.72rem;font-weight:600;cursor:pointer;">
                        ✕
                    </button>
                </div>
                <div v-if="!tiers.length" style="color:#9CA3AF;font-size:0.82rem;">Aucun palier défini.</div>
            </div>
            <form @submit.prevent="addTier" style="display:flex;gap:8px;align-items:flex-end;margin-bottom:20px;flex-wrap:wrap;">
                <div>
                    <label style="font-size:0.72rem;color:#6B7280;">Nom</label>
                    <input v-model="tierForm.name" type="text" placeholder="Bronze"
                           style="display:block;padding:6px 10px;border:1px solid #D1D5DB;font-size:0.82rem;width:120px;" />
                </div>
                <div>
                    <label style="font-size:0.72rem;color:#6B7280;">Points min</label>
                    <input v-model.number="tierForm.min_points" type="number" min="0"
                           style="display:block;padding:6px 10px;border:1px solid #D1D5DB;font-size:0.82rem;width:100px;" />
                </div>
                <div>
                    <label style="font-size:0.72rem;color:#6B7280;">Multiplicateur</label>
                    <input v-model.number="tierForm.bonus_multiplier" type="number" min="1" max="10" step="0.1"
                           style="display:block;padding:6px 10px;border:1px solid #D1D5DB;font-size:0.82rem;width:100px;" />
                </div>
                <button type="submit" :disabled="tierForm.processing"
                        style="padding:7px 16px;background:#8B5CF6;color:white;border:none;font-weight:600;font-size:0.82rem;cursor:pointer;">
                    + Ajouter
                </button>
            </form>
        </div>

        <div style="margin-top:20px;">
            <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Historique des transactions</h2>
            <div style="display:flex;gap:8px;margin-bottom:12px;">
                <input v-model="search" @keyup.enter="applyFilters" placeholder="Rechercher client..."
                       style="flex:1;padding:8px 12px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                <select v-model="type" @change="applyFilters" style="padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                    <option value="">Tous types</option>
                    <option value="earn">Gain</option>
                    <option value="redeem">Utilisation</option>
                </select>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Date</th>
                        <th style="text-align:left;padding:10px;">Client</th>
                        <th style="text-align:center;padding:10px;">Type</th>
                        <th style="text-align:right;padding:10px;">Points</th>
                        <th style="text-align:left;padding:10px;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in transactions.data" :key="t.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;color:#6B7280;">{{ new Date(t.created_at).toLocaleDateString('fr-FR') }}</td>
                        <td style="padding:10px;font-weight:600;">{{ t.customer?.name }}</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding: '2px 8px', fontSize: '0.72rem', fontWeight: 600, background: t.type === 'earn' ? '#ECFDF5' : '#FEF2F2', color: t.type === 'earn' ? '#065F46' : '#991B1B' }">
                                {{ t.type === 'earn' ? 'Gain' : 'Utilisation' }}
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;font-weight:700;" :style="{ color: t.type === 'earn' ? '#10B981' : '#EF4444' }">
                            {{ t.type === 'earn' ? '+' : '-' }}{{ t.points }}
                        </td>
                        <td style="padding:10px;color:#6B7280;">{{ t.description }}</td>
                    </tr>
                    <tr v-if="transactions.data.length === 0">
                        <td colspan="5" style="padding:40px;text-align:center;color:#9CA3AF;">Aucune transaction</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #EC4899}
</style>
