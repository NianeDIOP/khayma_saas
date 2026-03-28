<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({ contract: Object });
const c = props.contract;

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v || 0); }
function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—'; }

const statusLabels = { active: 'Actif', completed: 'Terminé', overdue: 'En retard', cancelled: 'Annulé', renewed: 'Renouvelé' };
const statusColors = { active: '#10B981', completed: '#6B7280', overdue: '#EF4444', cancelled: '#9CA3AF', renewed: '#8B5CF6' };
const payStatusLabels = { pending: 'En attente', paid: 'Payé', partial: 'Partiel', overdue: 'En retard' };
const payStatusColors = { pending: '#F59E0B', paid: '#10B981', partial: '#3B82F6', overdue: '#EF4444' };
const freqLabels = { daily: 'Journalier', monthly: 'Mensuel', quarterly: 'Trimestriel', yearly: 'Annuel', one_time: 'Unique' };
const methodLabels = { cash: 'Espèces', wave: 'Wave', om: 'Orange Money', card: 'Carte', bank_transfer: 'Virement', other: 'Autre' };

// Status update
function changeStatus(newStatus) {
    if (!confirm(`Passer le statut à "${statusLabels[newStatus]}" ?`)) return;
    router.patch(route('app.location.contracts.update-status', c.id), { status: newStatus });
}

// Renew modal
const showRenew = ref(false);
const renewForm = ref({
    start_date: '',
    end_date: '',
    total_amount: '',
    deposit_amount: '',
    payment_frequency: c.payment_frequency || 'monthly',
    conditions: c.conditions || '',
    notes: '',
});

function submitRenew() {
    router.post(route('app.location.contracts.renew', c.id), renewForm.value);
}

// Deposit return
const showDeposit = ref(false);
const depositAmount = ref(c.deposit_amount || 0);

function submitDeposit() {
    router.post(route('app.location.contracts.return-deposit', c.id), {
        deposit_returned_amount: depositAmount.value,
    });
}

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

// Totals
const totalPaid = (c.payments || []).reduce((s, p) => s + Number(p.amount_paid || 0), 0);
const totalDue = (c.payments || []).reduce((s, p) => s + Number(p.amount || 0), 0);
</script>

<template>
    <AppLayout title="Détail contrat">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-file-contract" style="color:#10B981"></i> {{ c.reference }}</h1>
            <Link :href="route('app.location.contracts.index')" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</Link>
        </div>

        <div v-if="$page.props.flash?.success" style="background:#ECFDF5;color:#065F46;padding:10px 14px;font-size:0.82rem;margin-bottom:12px;">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" style="background:#FEF2F2;color:#991B1B;padding:10px 14px;font-size:0.82rem;margin-bottom:12px;">
            {{ $page.props.flash.error }}
        </div>

        <!-- Info cards -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Bien</div>
                <div style="font-size:0.95rem;font-weight:700;">{{ c.rental_asset?.name || '—' }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Client</div>
                <div style="font-size:0.95rem;font-weight:700;">{{ c.customer?.name || '—' }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Montant total</div>
                <div style="font-size:1.1rem;font-weight:800;color:#10B981;">{{ fmt(c.total_amount) }} F</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Statut</div>
                <span :style="{ padding:'4px 12px', fontSize:'0.82rem', fontWeight:600, background: statusColors[c.status]+'20', color: statusColors[c.status] }">
                    {{ statusLabels[c.status] }}
                </span>
            </div>
        </div>

        <!-- Details -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:0.82rem;">
                    <div><span style="color:#6B7280;">Début:</span> {{ fmtDt(c.start_date) }}</div>
                    <div><span style="color:#6B7280;">Fin:</span> {{ fmtDt(c.end_date) }}</div>
                    <div><span style="color:#6B7280;">Fréquence:</span> {{ freqLabels[c.payment_frequency] }}</div>
                    <div><span style="color:#6B7280;">Créé par:</span> {{ c.user?.name || '—' }}</div>
                </div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.82rem;">
                    <div style="margin-bottom:4px;"><span style="color:#6B7280;">Caution:</span> <strong>{{ fmt(c.deposit_amount) }} F</strong></div>
                    <div v-if="c.deposit_returned" style="color:#10B981;font-weight:600;">
                        ✓ Caution restituée: {{ fmt(c.deposit_returned_amount) }} F
                    </div>
                    <div v-else-if="c.deposit_amount > 0">
                        <button @click="showDeposit = true" style="color:#6366F1;background:none;border:none;cursor:pointer;font-size:0.82rem;text-decoration:underline;">
                            Restituer la caution
                        </button>
                    </div>
                    <div style="margin-top:8px;"><span style="color:#6B7280;">Payé:</span> <strong :style="{ color: totalPaid >= totalDue ? '#10B981' : '#F59E0B' }">{{ fmt(totalPaid) }} / {{ fmt(totalDue) }} F</strong></div>
                </div>
            </div>
        </div>

        <!-- Renewed from -->
        <div v-if="c.renewed_from" style="border:1px solid #E5E7EB;padding:12px;margin-bottom:20px;font-size:0.82rem;">
            <span style="color:#6B7280;">Renouvelé depuis:</span>
            <Link :href="route('app.location.contracts.show', c.renewed_from.id)" style="color:#6366F1;text-decoration:none;font-weight:600;margin-left:4px;">
                {{ c.renewed_from.reference }}
            </Link>
        </div>

        <!-- Conditions / Notes -->
        <div v-if="c.conditions" style="border:1px solid #E5E7EB;padding:16px;margin-bottom:12px;">
            <div style="font-size:0.78rem;font-weight:600;color:#374151;margin-bottom:4px;">Conditions</div>
            <p style="font-size:0.82rem;color:#6B7280;white-space:pre-wrap;">{{ c.conditions }}</p>
        </div>
        <div v-if="c.notes" style="border:1px solid #E5E7EB;padding:16px;margin-bottom:20px;">
            <div style="font-size:0.78rem;font-weight:600;color:#374151;margin-bottom:4px;">Notes</div>
            <p style="font-size:0.82rem;color:#6B7280;white-space:pre-wrap;">{{ c.notes }}</p>
        </div>

        <!-- Actions -->
        <div v-if="c.status === 'active'" style="display:flex;gap:8px;margin-bottom:20px;">
            <button @click="changeStatus('completed')" style="padding:8px 16px;background:#10B981;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">
                Terminer
            </button>
            <button @click="changeStatus('overdue')" style="padding:8px 16px;background:#EF4444;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">
                Marquer en retard
            </button>
            <button @click="changeStatus('cancelled')" style="padding:8px 16px;background:#6B7280;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">
                Annuler
            </button>
            <button @click="showRenew = true" style="padding:8px 16px;background:#8B5CF6;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">
                Renouveler
            </button>
        </div>

        <!-- Payments table -->
        <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Échéancier de paiements</h2>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:#F9FAFB;border-bottom:2px solid #E5E7EB;">
                        <th style="text-align:left;padding:10px;">Échéance</th>
                        <th style="text-align:right;padding:10px;">Montant</th>
                        <th style="text-align:right;padding:10px;">Payé</th>
                        <th style="text-align:center;padding:10px;">Statut</th>
                        <th style="text-align:left;padding:10px;">Méthode</th>
                        <th style="text-align:left;padding:10px;">Date paiement</th>
                        <th style="text-align:right;padding:10px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in c.payments" :key="p.id" style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px;">{{ fmtDt(p.due_date) }}</td>
                        <td style="padding:10px;text-align:right;">{{ fmt(p.amount) }} F</td>
                        <td style="padding:10px;text-align:right;">{{ fmt(p.amount_paid) }} F</td>
                        <td style="padding:10px;text-align:center;">
                            <span :style="{ padding:'2px 8px', fontSize:'0.72rem', fontWeight:600, background: payStatusColors[p.status]+'20', color: payStatusColors[p.status] }">
                                {{ payStatusLabels[p.status] }}
                            </span>
                        </td>
                        <td style="padding:10px;">{{ p.method ? methodLabels[p.method] : '—' }}</td>
                        <td style="padding:10px;">{{ fmtDt(p.payment_date) }}</td>
                        <td style="padding:10px;text-align:right;">
                            <button v-if="p.status !== 'paid'" @click="openPayment(p)"
                                    style="color:#10B981;background:none;border:none;cursor:pointer;font-size:0.82rem;text-decoration:underline;">
                                Enregistrer
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!c.payments?.length">
                        <td colspan="7" style="padding:30px;text-align:center;color:#9CA3AF;">Aucune échéance</td>
                    </tr>
                </tbody>
                <tfoot v-if="c.payments?.length">
                    <tr style="border-top:2px solid #E5E7EB;">
                        <td style="padding:10px;font-weight:700;">Total</td>
                        <td style="padding:10px;text-align:right;font-weight:700;">{{ fmt(totalDue) }} F</td>
                        <td style="padding:10px;text-align:right;font-weight:700;">{{ fmt(totalPaid) }} F</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment modal -->
        <div v-if="showPayment" style="position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:50;">
            <div style="background:white;padding:24px;width:400px;max-width:90vw;">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">Enregistrer un paiement</h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Montant *</label>
                        <input v-model.number="payForm.amount_paid" type="number" step="0.01" min="0.01"
                               style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date de paiement *</label>
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
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Notes</label>
                        <textarea v-model="payForm.notes" rows="2" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;resize:vertical;"></textarea>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button @click="submitPayment" style="padding:8px 20px;background:#111827;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">Valider</button>
                    <button @click="showPayment = false" style="padding:8px 20px;border:1px solid #D1D5DB;background:white;cursor:pointer;font-size:0.82rem;">Annuler</button>
                </div>
            </div>
        </div>

        <!-- Renew modal -->
        <div v-if="showRenew" style="position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:50;">
            <div style="background:white;padding:24px;width:480px;max-width:90vw;max-height:90vh;overflow-y:auto;">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">Renouveler le contrat</h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date début *</label>
                            <input v-model="renewForm.start_date" type="date" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:0.78rem;font-weight:600;color:#374151;">Date fin *</label>
                            <input v-model="renewForm.end_date" type="date" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label style="font-size:0.78rem;font-weight:600;color:#374151;">Montant *</label>
                            <input v-model.number="renewForm.total_amount" type="number" step="0.01" min="0"
                                   style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:0.78rem;font-weight:600;color:#374151;">Caution</label>
                            <input v-model.number="renewForm.deposit_amount" type="number" step="0.01" min="0"
                                   style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                        </div>
                    </div>
                    <div>
                        <label style="font-size:0.78rem;font-weight:600;color:#374151;">Fréquence *</label>
                        <select v-model="renewForm.payment_frequency" style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;">
                            <option value="daily">Journalier</option>
                            <option value="monthly">Mensuel</option>
                            <option value="quarterly">Trimestriel</option>
                            <option value="yearly">Annuel</option>
                            <option value="one_time">Unique</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button @click="submitRenew" style="padding:8px 20px;background:#8B5CF6;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">Renouveler</button>
                    <button @click="showRenew = false" style="padding:8px 20px;border:1px solid #D1D5DB;background:white;cursor:pointer;font-size:0.82rem;">Annuler</button>
                </div>
            </div>
        </div>

        <!-- Deposit return modal -->
        <div v-if="showDeposit" style="position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:50;">
            <div style="background:white;padding:24px;width:360px;max-width:90vw;">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">Restituer la caution</h3>
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;">Montant à restituer (max: {{ fmt(c.deposit_amount) }} F)</label>
                    <input v-model.number="depositAmount" type="number" step="0.01" min="0" :max="c.deposit_amount"
                           style="width:100%;padding:8px;border:1px solid #D1D5DB;margin-top:4px;" />
                </div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button @click="submitDeposit" style="padding:8px 20px;background:#111827;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">Valider</button>
                    <button @click="showDeposit = false" style="padding:8px 20px;border:1px solid #D1D5DB;background:white;cursor:pointer;font-size:0.82rem;">Annuler</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #10B981; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #F3F4F6; color: #374151; font-size: 0.78rem; font-weight: 600; text-decoration: none; border: 1px solid #E5E7EB; transition: all 0.15s; }
.btn-back:hover { background: #E5E7EB; }
</style>
