<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    totalSales: [Number, String],
    totalOrders: Number,
    avgBasket: [Number, String],
    netProfit: [Number, String],
    salesByPayment: Array,
    topProducts: Array,
    salesByCategory: Array,
    stockAlerts: Array,
    customerDebts: Array,
    loyaltyEarned: [Number, String],
    loyaltyRedeemed: [Number, String],
    stockByDepot: Array,
    filters: Object,
});

const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const t = () => route().params._tenant

function applyFilters() {
    router.get(route('app.boutique.reports.index', { _tenant: t() }), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
}

function formatPrice(v) {
    return new Intl.NumberFormat('fr-FR').format(v);
}

const paymentLabels = { cash: 'Espèces', wave: 'Wave', om: 'Orange Money', card: 'Carte', other: 'Autre' };
</script>

<template>
    <AppLayout title="Rapports Boutique">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-chart-pie" style="color:#8B5CF6"></i> Rapports Boutique</h1>
        </div>

        <!-- Date filter -->
        <div style="display:flex;gap:8px;margin-bottom:20px;align-items:end;">
            <div>
                <label style="font-size:0.72rem;color:#6B7280;">Début</label>
                <input v-model="startDate" type="date" style="padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            </div>
            <div>
                <label style="font-size:0.72rem;color:#6B7280;">Fin</label>
                <input v-model="endDate" type="date" style="padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
            </div>
            <button @click="applyFilters" style="padding:6px 14px;background:#111827;color:white;border:none;cursor:pointer;font-size:0.82rem;font-weight:600;">Filtrer</button>
        </div>

        <!-- KPI cards -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Chiffre d'affaires</div>
                <div style="font-size:1.3rem;font-weight:800;color:#10B981;">{{ formatPrice(totalSales) }} F</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Nombre de ventes</div>
                <div style="font-size:1.3rem;font-weight:800;color:#2563EB;">{{ totalOrders }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Panier moyen</div>
                <div style="font-size:1.3rem;font-weight:800;color:#F59E0B;">{{ formatPrice(avgBasket) }} F</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Bénéfice net</div>
                <div style="font-size:1.3rem;font-weight:800;color:#8B5CF6;">{{ formatPrice(netProfit) }} F</div>
            </div>
        </div>

        <!-- Second row - loyalty + stock -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Points fidélité émis</div>
                <div style="font-size:1.1rem;font-weight:700;color:#8B5CF6;">{{ formatPrice(loyaltyEarned) }}</div>
            </div>
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Points fidélité utilisés</div>
                <div style="font-size:1.1rem;font-weight:700;color:#EF4444;">{{ formatPrice(loyaltyRedeemed) }}</div>
            </div>
            <div v-for="depot in stockByDepot" :key="depot.name" style="border:1px solid #E5E7EB;padding:16px;">
                <div style="font-size:0.72rem;color:#6B7280;">Stock {{ depot.name }}</div>
                <div style="font-size:1.1rem;font-weight:700;">{{ depot.items }} articles</div>
                <div style="font-size:0.78rem;color:#6B7280;">{{ formatPrice(depot.value) }} F</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <!-- Top products -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Top produits</h2>
                <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                    <thead>
                        <tr style="border-bottom:1px solid #E5E7EB;">
                            <th style="text-align:left;padding:6px;">Produit</th>
                            <th style="text-align:right;padding:6px;">Qté</th>
                            <th style="text-align:right;padding:6px;">CA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in topProducts" :key="p.name" style="border-bottom:1px solid #F3F4F6;">
                            <td style="padding:6px;font-weight:600;">{{ p.name }}</td>
                            <td style="padding:6px;text-align:right;">{{ p.qty }}</td>
                            <td style="padding:6px;text-align:right;color:#10B981;">{{ formatPrice(p.revenue) }} F</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Sales by payment -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Ventes par mode de paiement</h2>
                <div v-for="pm in salesByPayment" :key="pm.method" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F3F4F6;font-size:0.85rem;">
                    <span style="font-weight:600;">{{ paymentLabels[pm.method] || pm.method }}</span>
                    <span style="color:#10B981;font-weight:700;">{{ formatPrice(pm.total) }} F</span>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <!-- Sales by category -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Ventes par catégorie</h2>
                <div v-for="c in salesByCategory" :key="c.name" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F3F4F6;font-size:0.85rem;">
                    <span style="font-weight:600;">{{ c.name }}</span>
                    <span style="color:#2563EB;font-weight:700;">{{ formatPrice(c.revenue) }} F</span>
                </div>
                <div v-if="salesByCategory.length === 0" style="color:#9CA3AF;padding:12px;text-align:center;font-size:0.82rem;">Aucune donnée</div>
            </div>

            <!-- Customer debts -->
            <div style="border:1px solid #E5E7EB;padding:16px;">
                <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;">Créances clients</h2>
                <div v-for="c in customerDebts" :key="c.id" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F3F4F6;font-size:0.85rem;">
                    <div>
                        <span style="font-weight:600;">{{ c.name }}</span>
                        <span style="color:#9CA3AF;margin-left:8px;font-size:0.78rem;">{{ c.phone }}</span>
                    </div>
                    <span style="color:#EF4444;font-weight:700;">{{ formatPrice(c.outstanding_balance) }} F</span>
                </div>
                <div v-if="customerDebts.length === 0" style="color:#9CA3AF;padding:12px;text-align:center;font-size:0.82rem;">Aucune créance</div>
            </div>
        </div>

        <!-- Stock alerts -->
        <div v-if="stockAlerts.length > 0" style="border:1px solid #FEE2E2;padding:16px;background:#FEF2F2;">
            <h2 style="font-size:0.95rem;font-weight:700;margin-bottom:12px;color:#991B1B;">⚠ Alertes stock ({{ stockAlerts.length }})</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;">
                <div v-for="p in stockAlerts" :key="p.id" style="background:white;border:1px solid #FECACA;padding:10px;">
                    <div style="font-weight:600;font-size:0.82rem;">{{ p.name }}</div>
                    <div style="font-size:0.78rem;color:#EF4444;">Stock: {{ p.total_stock }} / Seuil: {{ p.min_stock_alert }}</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #8B5CF6}
</style>
