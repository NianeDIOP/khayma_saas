<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    payments: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

function applyFilters() {
    router.get(route('app.location.payments.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
}

function markOverdue() {
    if (!confirm('Marquer tous les paiements en retard ?')) return;
    router.post(route('app.location.payments.mark-overdue'));
}

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—'; }

const statusLabels = { pending: 'En attente', paid: 'Payé', partial: 'Partiel', overdue: 'En retard' };
const statusColors = { pending: '#F59E0B', paid: '#10B981', partial: '#3B82F6', overdue: '#EF4444' };
const methodLabels = { cash: 'Espèces', wave: 'Wave', om: 'Orange Money', card: 'Carte', bank_transfer: 'Virement', other: 'Autre' };

// Record payment modal
const showPayment = ref(false);
const selectedPayment = ref(null);
const payForm = ref({ amount_paid: '', payment_date: '', method: 'cash', reference: '', notes: '' });

function openPayment(p) {
    selectedPayment.value = p;
    payForm.value = { amount_paid: p.amount - p.amount_paid, payment_date: new Date().toISOString().split('T')[0], method: 'cash', reference: '', notes: '' };
    showPayment.value = true;
}

function submitPayment() {
    router.post(route('app.location.payments.record', selectedPayment.value.id), payForm.value, {
        onSuccess: () => { showPayment.value = false; }
    });
}
</script>

<template>
    <AppLayout title="Paiements location">
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h1 style="font-size:1.15rem;font-weight:700;">Paiements de location</h1>
            <button @click="markOverdue" style="background:#EF4444;color:white;padding:8px 16px;font-size:0.82rem;font-weight:600;border:none;cursor:pointer;">
                Marquer retards
            </button>
        </div>

        <div v-if="$page.props.flash?.success" style="background:#ECFDF5;color:#065F46;padding:10px 14px;font-size:0.82rem;margin-bottom:12px;">
            {{ $page.props.flash.success }}
        </div>

        <!-- Filters -->
        <div class="filters-bar" style="display:flex;gap:8px;margin-bottom:16px;">
            <input v-model="search" @keyup.enter="applyFilters" placeholder="Rechercher (réf, client)..."
                   style="flex:1;padding:8px 12px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            <select v-model="status" @change="applyFilters" style="padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                <option value="">Tous statuts</option>
                <option value="pending">En attente</option>
                <option value="paid">Payé</option>
                <option value="partial">Partiel</option>
                <option value="overdue">En retard</option>
            </select>
        </div>

        <!-- Table -->
        <div class="data-table" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Contrat</th>
                        <th style="text-align:left;padding:10px;">Bien</th>
                        <th style="text-align:left;padding:10px;">Client</th>
                        <th style="text-align:left;padding:10px;">Échéance</th>
                        <th style="text-align:right;padding:10px;">Montant</th>
                        <th style="text-align:right;padding:10px;">Payé</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:left;padding:10px;">Méthode</th>
                        <th style="text-align:right;padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in payments.data" :key="p.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;">
                            <Link v-if="p.rental_contract" :href="route('app.location.contracts.show', p.rental_contract.id)" style="color:#6366F1;text-decoration:none;font-weight:600;">
                                {{ p.rental_contract.reference }}
                            </Link>
                        </td>
                        <td style="padding:10px;">{{ p.rental_contract?.rental_asset?.name || '—' }}</td>
                        <td style="padding:10px;">{{ p.rental_contract?.customer?.name || '—' }}</td>
                        <td style="padding:10px;">{{ fmtDt(p.due_date) }}</td>
                        <td style="padding:10px;text-align:right;">{{ fmt(p.amount) }} F</td>
                        <td style="padding:10px;text-align:right;">{{ fmt(p.amount_paid) }} F</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: statusColors[p.status]+'20', color: statusColors[p.status] }">
                                {{ statusLabels[p.status] }}
                            </span>
                        </td>
                        <td style="padding:10px;">{{ p.method ? methodLabels[p.method] : '—' }}</td>
                        <td style="padding:10px;text-align:right;">
                            <button v-if="p.status !== 'paid'" @click="openPayment(p)"
                                    style="color:#10B981;background:none;border:none;cursor:pointer;font-size:0.82rem;text-decoration:underline;">
                                Payer
                            </button>
                        </td>
                    </tr>
                    <tr v-if="payments.data.length === 0">
                        <td colspan="9" style="padding:40px;text-align:center;color:#9CA3AF;">Aucun paiement</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="payments.links?.length > 3" style="display:flex;gap:4px;margin-top:16px;justify-content:center;">
            <template v-for="link in payments.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" v-html="link.label"
                      :style="{ padding:'6px 12px', fontSize:'0.78rem', border:'1px solid #D1D5DB', textDecoration:'none',
                                background: link.active ? '#111827' : 'white', color: link.active ? 'white' : '#374151' }" />
                <span v-else v-html="link.label" style="padding:6px 12px;font-size:0.78rem;color:#9CA3AF;"></span>
            </template>
        </div>

        <!-- Payment modal -->
        <div v-if="showPayment" style="position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:50;">
            <div style="background:white;padding:24px;width:400px;max-width:90vw;">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">Enregistrer un paiement</h3>
                <div style="font-size:0.82rem;color:#6B7280;margin-bottom:12px;">
                    Reste à payer: <strong>{{ fmt(selectedPayment.amount - selectedPayment.amount_paid) }} F</strong>
                </div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Montant *</label>
                        <input v-model.number="payForm.amount_paid" type="number" step="0.01" min="0.01"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date *</label>
                        <input v-model="payForm.payment_date" type="date" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Méthode *</label>
                        <select v-model="payForm.method" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                            <option value="cash">Espèces</option>
                            <option value="wave">Wave</option>
                            <option value="om">Orange Money</option>
                            <option value="card">Carte</option>
                            <option value="bank_transfer">Virement</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Référence</label>
                        <input v-model="payForm.reference" type="text" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button @click="submitPayment" style="padding:8px 20px;background:#111827;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">Valider</button>
                    <button @click="showPayment = false" style="padding:8px 20px;border:1px solid #D1D5DB;background:white;cursor:pointer;font-size:0.82rem;">Annuler</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
