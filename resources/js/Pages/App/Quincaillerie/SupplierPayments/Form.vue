<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ payment: Object, suppliers: Array, purchaseOrders: Array })
const t = () => route().params._tenant

const form = useForm({
  supplier_id: '',
  purchase_order_id: '',
  amount: '',
  method: 'cash',
  reference: '',
  notes: '',
  date: new Date().toISOString().split('T')[0],
})

const filteredPOs = computed(() => {
  if (!form.supplier_id) return props.purchaseOrders
  return props.purchaseOrders.filter(po => po.supplier_id == form.supplier_id)
})

function submit() {
  form.post(route('app.quincaillerie.supplier-payments.store', { _tenant: t() }))
}
</script>

<template>
  <AppLayout title="Nouveau paiement fournisseur">
    <Head title="Nouveau paiement fournisseur" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-money-check-dollar" style="color:#10B981"></i> Nouveau paiement</h1>
      <Link :href="route('app.quincaillerie.supplier-payments.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="form-group">
          <label>Fournisseur *</label>
          <select v-model="form.supplier_id" class="form-input" required>
            <option value="">Choisir…</option>
            <option v-for="s in suppliers" :key="s.id" :value="s.id">
              {{ s.name }} <span v-if="s.outstanding_balance > 0">(dette: {{ new Intl.NumberFormat('fr-FR').format(s.outstanding_balance) }} XOF)</span>
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Bon de commande (optionnel)</label>
          <select v-model="form.purchase_order_id" class="form-input">
            <option value="">— Aucun —</option>
            <option v-for="po in filteredPOs" :key="po.id" :value="po.id">{{ po.reference }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Montant *</label>
          <input v-model.number="form.amount" type="number" min="0.01" step="0.01" class="form-input" required />
        </div>
        <div class="form-group">
          <label>Méthode *</label>
          <select v-model="form.method" class="form-input" required>
            <option value="cash">Espèces</option>
            <option value="wave">Wave</option>
            <option value="om">Orange Money</option>
            <option value="bank">Virement</option>
            <option value="other">Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label>Référence</label>
          <input v-model="form.reference" type="text" class="form-input" placeholder="N° chèque, réf. transfert…" />
        </div>
        <div class="form-group">
          <label>Date *</label>
          <input v-model="form.date" type="date" class="form-input" required />
        </div>
      </div>

      <div class="form-group">
        <label>Notes</label>
        <textarea v-model="form.notes" class="form-input" rows="3"></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="form.processing">
          <i class="fa-solid fa-save"></i> Enregistrer le paiement
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.5rem}
.btn-back{color:#6B7280;font-size:.875rem;display:inline-flex;align-items:center;gap:.3rem}
.form-card{background:#fff;border:1px solid #E5E7EB;padding:1.5rem}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
.form-group{display:flex;flex-direction:column;gap:.3rem}
.form-group label{font-size:.8rem;font-weight:600;color:#374151}
.form-input{padding:.45rem .6rem;border:1px solid #D1D5DB;font-size:.875rem;width:100%}
.form-actions{display:flex;justify-content:flex-end;margin-top:1rem}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1.25rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
.btn-primary:disabled{opacity:.5}
</style>
