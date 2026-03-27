<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ sale: Object });

function formatPrice(v) {
    return new Intl.NumberFormat('fr-FR').format(v) + ' F';
}

function printReceipt() {
    window.print();
}
</script>

<template>
    <AppLayout title="Ticket de caisse">
        <div style="max-width:380px;margin:0 auto;background:white;border:1px solid #E5E7EB;padding:24px;">
            <div style="text-align:center;margin-bottom:16px;">
                <h2 style="font-size:1.1rem;font-weight:800;margin:0;">TICKET DE CAISSE</h2>
                <p style="font-size:0.78rem;color:#6B7280;margin:4px 0;">{{ sale.reference }}</p>
                <p style="font-size:0.75rem;color:#9CA3AF;">{{ new Date(sale.created_at).toLocaleString('fr-FR') }}</p>
            </div>

            <div v-if="sale.customer" style="font-size:0.82rem;margin-bottom:12px;padding-bottom:8px;border-bottom:1px dashed #D1D5DB;">
                Client: <strong>{{ sale.customer.name }}</strong>
            </div>

            <table style="width:100%;font-size:0.8rem;margin-bottom:12px;">
                <thead>
                    <tr style="border-bottom:1px solid #E5E7EB;">
                        <th style="text-align:left;padding:4px 0;">Article</th>
                        <th style="text-align:center;">Qté</th>
                        <th style="text-align:right;">PU</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in sale.items" :key="item.id" style="border-bottom:1px dotted #F3F4F6;">
                        <td style="padding:4px 0;">{{ item.product?.name }}</td>
                        <td style="text-align:center;">{{ item.quantity }}</td>
                        <td style="text-align:right;">{{ formatPrice(item.unit_price) }}</td>
                        <td style="text-align:right;">{{ formatPrice(item.total) }}</td>
                    </tr>
                </tbody>
            </table>

            <div style="border-top:1px solid #E5E7EB;padding-top:8px;font-size:0.82rem;">
                <div style="display:flex;justify-content:space-between;"><span>Sous-total</span><span>{{ formatPrice(sale.subtotal) }}</span></div>
                <div v-if="parseFloat(sale.discount_amount) > 0" style="display:flex;justify-content:space-between;color:#EF4444;">
                    <span>Remise</span><span>-{{ formatPrice(sale.discount_amount) }}</span>
                </div>
                <div v-if="parseFloat(sale.loyalty_discount) > 0" style="display:flex;justify-content:space-between;color:#8B5CF6;">
                    <span>Fidélité</span><span>-{{ formatPrice(sale.loyalty_discount) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1rem;margin-top:6px;padding-top:6px;border-top:2px solid #111827;">
                    <span>TOTAL</span><span>{{ formatPrice(sale.total) }}</span>
                </div>
            </div>

            <div style="margin-top:12px;font-size:0.78rem;color:#6B7280;">
                Paiement: <strong>{{ sale.payments?.[0]?.method || sale.payment_status }}</strong><br />
                Vendeur: {{ sale.user?.name }}
            </div>

            <div v-if="sale.loyalty_points_earned > 0" style="margin-top:8px;font-size:0.78rem;color:#8B5CF6;">
                +{{ sale.loyalty_points_earned }} points fidélité gagnés
            </div>

            <div style="text-align:center;margin-top:20px;padding-top:12px;border-top:1px dashed #D1D5DB;">
                <p style="font-size:0.75rem;color:#9CA3AF;">Merci pour votre achat !</p>
            </div>

            <button @click="printReceipt" style="width:100%;margin-top:16px;padding:10px;background:#111827;color:white;border:none;cursor:pointer;font-weight:600;">
                🖨 Imprimer
            </button>
        </div>
    </AppLayout>
</template>
