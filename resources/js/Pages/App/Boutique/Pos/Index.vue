<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
    customers: Array,
    depots: Array,
    categories: Array,
    loyaltyConfig: Object,
});

const flash = computed(() => usePage().props.flash);

// Filters
const search = ref('');
const selectedCategory = ref(null);
const selectedDepot = ref(props.depots.find(d => d.is_default)?.id || props.depots[0]?.id);

// Cart
const cart = ref([]);
const selectedCustomer = ref(null);
const discountAmount = ref(0);
const paymentMethod = ref('cash');
const useLoyaltyPoints = ref(0);
const notes = ref('');
const processing = ref(false);

const t = () => route().params._tenant

const filteredProducts = computed(() => {
    let result = props.products;
    if (search.value) {
        const s = search.value.toLowerCase();
        result = result.filter(p =>
            p.name.toLowerCase().includes(s) ||
            (p.barcode && p.barcode.toLowerCase().includes(s))
        );
    }
    if (selectedCategory.value) {
        result = result.filter(p => p.category_id === selectedCategory.value);
    }
    return result;
});

function addToCart(product, variant = null) {
    const key = variant ? `v-${variant.id}` : `p-${product.id}`;
    const existing = cart.value.find(i => i.key === key);

    const promoPrice = product.promo_price;
    const price = variant
        ? (variant.price_override || product.selling_price)
        : (promoPrice || product.selling_price);

    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            key,
            product_id: product.id,
            variant_id: variant?.id || null,
            name: variant ? `${product.name} — ${variant.name}` : product.name,
            unit_price: parseFloat(price),
            quantity: 1,
            discount: 0,
            promo: promoPrice && !variant ? product.promo_label : null,
        });
    }
}

function removeFromCart(index) {
    cart.value.splice(index, 1);
}

const subtotal = computed(() => cart.value.reduce((s, i) => s + (i.unit_price * i.quantity - i.discount), 0));

const customerObj = computed(() => props.customers.find(c => c.id === selectedCustomer.value));

const maxLoyaltyPoints = computed(() => {
    if (!customerObj.value || !props.loyaltyConfig) return 0;
    return customerObj.value.loyalty_points;
});

const loyaltyDiscount = computed(() => {
    if (!props.loyaltyConfig || useLoyaltyPoints.value <= 0) return 0;
    return props.loyaltyConfig.redemption_value *
        Math.floor(useLoyaltyPoints.value / props.loyaltyConfig.redemption_threshold);
});

const total = computed(() => Math.max(0, subtotal.value - discountAmount.value - loyaltyDiscount.value));

function submit() {
    if (cart.value.length === 0 || processing.value) return;
    processing.value = true;

    router.post(route('app.boutique.pos.store', { _tenant: t() }), {
        customer_id: selectedCustomer.value,
        depot_id: selectedDepot.value,
        discount_amount: discountAmount.value,
        tax_amount: 0,
        notes: notes.value,
        items: cart.value.map(i => ({
            product_id: i.product_id,
            variant_id: i.variant_id,
            quantity: i.quantity,
            unit_price: i.unit_price,
            discount: i.discount,
        })),
        payment_method: paymentMethod.value,
        use_loyalty_points: useLoyaltyPoints.value > 0 ? useLoyaltyPoints.value : null,
    }, {
        onSuccess: () => {
            cart.value = [];
            discountAmount.value = 0;
            useLoyaltyPoints.value = 0;
            notes.value = '';
            processing.value = false;
        },
        onError: () => { processing.value = false; },
    });
}

function formatPrice(v) {
    return new Intl.NumberFormat('fr-FR').format(v) + ' F';
}
</script>

<template>
    <AppLayout title="Caisse POS">
        <!-- Flash -->
        <div v-if="flash?.success" style="background:#ECFDF5;border:1px solid #6EE7B7;padding:10px 16px;margin-bottom:16px;color:#065F46;font-size:0.85rem;">
            {{ flash.success }}
        </div>

        <div style="display:grid;grid-template-columns:1fr 380px;gap:16px;height:calc(100vh - 120px);">
            <!-- LEFT: Products -->
            <div style="display:flex;flex-direction:column;overflow:hidden;">
                <!-- Search bar -->
                <div style="display:flex;gap:8px;margin-bottom:12px;">
                    <input v-model="search" type="text" placeholder="Rechercher produit ou scanner code-barres..."
                        style="flex:1;padding:10px 14px;border:1px solid #D1D5DB;font-size:0.9rem;" />
                    <select v-model="selectedCategory" style="padding:10px;border:1px solid #D1D5DB;font-size:0.85rem;">
                        <option :value="null">Toutes catégories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <select v-model="selectedDepot" style="padding:10px;border:1px solid #D1D5DB;font-size:0.85rem;">
                        <option v-for="d in depots" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                </div>

                <!-- Product grid -->
                <div style="flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;align-content:start;">
                    <div v-for="product in filteredProducts" :key="product.id"
                         style="border:1px solid #E5E7EB;padding:10px;cursor:pointer;position:relative;"
                         @click="product.variants?.length ? null : addToCart(product)">
                        <!-- Promo badge -->
                        <div v-if="product.promo_price" style="position:absolute;top:4px;right:4px;background:#EF4444;color:white;font-size:0.65rem;padding:2px 6px;font-weight:700;">
                            PROMO
                        </div>
                        <div style="font-weight:700;font-size:0.82rem;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ product.name }}
                        </div>
                        <div style="font-size:0.78rem;color:#6B7280;">{{ product.category?.name }}</div>
                        <div style="margin-top:4px;">
                            <span v-if="product.promo_price" style="font-size:0.85rem;font-weight:700;color:#EF4444;">{{ formatPrice(product.promo_price) }}</span>
                            <span :style="product.promo_price ? 'text-decoration:line-through;color:#9CA3AF;font-size:0.75rem;margin-left:4px;' : 'font-size:0.85rem;font-weight:700;color:#10B981;'">
                                {{ formatPrice(product.selling_price) }}
                            </span>
                        </div>
                        <div style="font-size:0.72rem;color:#9CA3AF;margin-top:2px;">Stock: {{ product.stock_qty ?? '—' }}</div>

                        <!-- Variants -->
                        <div v-if="product.variants?.length" style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;">
                            <button v-for="v in product.variants" :key="v.id"
                                    @click.stop="addToCart(product, v)"
                                    style="font-size:0.7rem;padding:2px 6px;border:1px solid #6366F1;color:#6366F1;background:white;cursor:pointer;">
                                {{ v.name }}
                            </button>
                        </div>
                    </div>
                    <div v-if="filteredProducts.length === 0" style="grid-column:1/-1;text-align:center;padding:40px;color:#9CA3AF;">
                        Aucun produit trouvé
                    </div>
                </div>
            </div>

            <!-- RIGHT: Cart -->
            <div style="border:1px solid #E5E7EB;display:flex;flex-direction:column;background:white;">
                <div style="padding:12px 16px;background:#111827;color:white;font-weight:700;font-size:0.9rem;">
                    🛒 Panier ({{ cart.length }} article{{ cart.length > 1 ? 's' : '' }})
                </div>

                <!-- Cart items -->
                <div style="flex:1;overflow-y:auto;padding:8px;">
                    <div v-if="cart.length === 0" style="text-align:center;color:#9CA3AF;padding:40px;font-size:0.85rem;">
                        Panier vide
                    </div>
                    <div v-for="(item, idx) in cart" :key="item.key"
                         style="border-bottom:1px solid #F3F4F6;padding:8px 0;display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:600;">{{ item.name }}</div>
                            <div v-if="item.promo" style="font-size:0.68rem;color:#EF4444;">{{ item.promo }}</div>
                            <div style="font-size:0.75rem;color:#6B7280;">{{ formatPrice(item.unit_price) }} × {{ item.quantity }}</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <button @click="item.quantity > 1 ? item.quantity-- : removeFromCart(idx)"
                                    style="width:24px;height:24px;border:1px solid #D1D5DB;background:white;cursor:pointer;font-weight:700;">−</button>
                            <span style="width:28px;text-align:center;font-size:0.85rem;font-weight:600;">{{ item.quantity }}</span>
                            <button @click="item.quantity++"
                                    style="width:24px;height:24px;border:1px solid #D1D5DB;background:white;cursor:pointer;font-weight:700;">+</button>
                        </div>
                        <div style="font-weight:700;font-size:0.85rem;width:80px;text-align:right;">
                            {{ formatPrice(item.unit_price * item.quantity - item.discount) }}
                        </div>
                        <button @click="removeFromCart(idx)" style="color:#EF4444;border:none;background:none;cursor:pointer;font-size:1rem;">✕</button>
                    </div>
                </div>

                <!-- Totals & Options -->
                <div style="border-top:1px solid #E5E7EB;padding:12px;">
                    <!-- Customer select -->
                    <div style="margin-bottom:8px;">
                        <select v-model="selectedCustomer" style="width:100%;padding:8px;border:1px solid #D1D5DB;font-size:0.82rem;">
                            <option :value="null">Client de passage</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} {{ c.loyalty_points ? '(' + c.loyalty_points + ' pts)' : '' }}</option>
                        </select>
                    </div>

                    <!-- Discount -->
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <div style="flex:1;">
                            <label style="font-size:0.72rem;color:#6B7280;">Remise (F)</label>
                            <input v-model.number="discountAmount" type="number" min="0" style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        </div>
                        <div v-if="loyaltyConfig && selectedCustomer && maxLoyaltyPoints > 0" style="flex:1;">
                            <label style="font-size:0.72rem;color:#6B7280;">Points fidélité</label>
                            <input v-model.number="useLoyaltyPoints" type="number" :min="0" :max="maxLoyaltyPoints"
                                   style="width:100%;padding:6px;border:1px solid #D1D5DB;font-size:0.82rem;" />
                        </div>
                    </div>

                    <!-- Payment method -->
                    <div style="display:flex;gap:4px;margin-bottom:10px;flex-wrap:wrap;">
                        <button v-for="m in ['cash','wave','om','card','credit']" :key="m"
                                @click="paymentMethod = m"
                                :style="{
                                    padding: '6px 12px', border: '1px solid', cursor: 'pointer', fontSize: '0.78rem', fontWeight: 600,
                                    background: paymentMethod === m ? '#111827' : 'white',
                                    color: paymentMethod === m ? 'white' : '#374151',
                                    borderColor: paymentMethod === m ? '#111827' : '#D1D5DB',
                                }">
                            {{ m === 'cash' ? 'Espèces' : m === 'wave' ? 'Wave' : m === 'om' ? 'Orange M.' : m === 'card' ? 'Carte' : 'Crédit' }}
                        </button>
                    </div>

                    <!-- Subtotal & Total -->
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;color:#6B7280;margin-bottom:2px;">
                        <span>Sous-total</span>
                        <span>{{ formatPrice(subtotal) }}</span>
                    </div>
                    <div v-if="discountAmount > 0" style="display:flex;justify-content:space-between;font-size:0.82rem;color:#EF4444;margin-bottom:2px;">
                        <span>Remise</span>
                        <span>-{{ formatPrice(discountAmount) }}</span>
                    </div>
                    <div v-if="loyaltyDiscount > 0" style="display:flex;justify-content:space-between;font-size:0.82rem;color:#8B5CF6;margin-bottom:2px;">
                        <span>Fidélité ({{ useLoyaltyPoints }} pts)</span>
                        <span>-{{ formatPrice(loyaltyDiscount) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:800;color:#111827;margin-top:6px;padding-top:8px;border-top:2px solid #111827;">
                        <span>TOTAL</span>
                        <span>{{ formatPrice(total) }}</span>
                    </div>

                    <!-- Submit -->
                    <button @click="submit" :disabled="cart.length === 0 || processing"
                            style="width:100%;margin-top:12px;padding:14px;background:#10B981;color:white;border:none;font-size:1rem;font-weight:700;cursor:pointer;"
                            :style="{ opacity: cart.length === 0 || processing ? 0.5 : 1 }">
                        {{ processing ? 'Traitement...' : 'VALIDER LA VENTE' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
