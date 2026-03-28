<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ products: Array, depots: Array, depot_id: Number })
const t = () => route().params._tenant

const lines = ref(props.products.map(p => ({
  product_id: p.id,
  name: p.name,
  barcode: p.barcode,
  system_quantity: p.system_quantity,
  physical_quantity: p.system_quantity,
})))

const notes = ref('')

const gapCount = computed(() => lines.value.filter(l => l.physical_quantity != l.system_quantity).length)

function submit() {
  const form = useForm({
    depot_id: props.depot_id,
    notes: notes.value,
    lines: lines.value.map(l => ({
      product_id: l.product_id,
      system_quantity: l.system_quantity,
      physical_quantity: l.physical_quantity,
    })),
  })
  form.post(route('app.quincaillerie.inventories.store', { _tenant: t() }))
}
</script>

<template>
  <AppLayout title="Saisie inventaire">
    <Head title="Saisie inventaire" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-clipboard-check" style="color:#8B5CF6"></i> Saisie inventaire</h1>
      <Link :href="route('app.quincaillerie.inventories.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <div class="info-bar">
      <span><strong>{{ lines.length }}</strong> produits</span>
      <span v-if="gapCount" style="color:#EF4444"><strong>{{ gapCount }}</strong> écarts détectés</span>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Code</th>
            <th>Qté système</th>
            <th>Qté physique</th>
            <th>Écart</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(line, idx) in lines" :key="line.product_id"
              :class="{ 'gap-row': line.physical_quantity != line.system_quantity }">
            <td>{{ line.name }}</td>
            <td class="text-muted">{{ line.barcode || '—' }}</td>
            <td>{{ line.system_quantity }}</td>
            <td>
              <input v-model.number="line.physical_quantity" type="number" min="0" step="0.01" class="form-input input-sm" />
            </td>
            <td :style="{ color: line.physical_quantity - line.system_quantity < 0 ? '#EF4444' : line.physical_quantity - line.system_quantity > 0 ? '#10B981' : '#6B7280', fontWeight: 600 }">
              {{ line.physical_quantity - line.system_quantity > 0 ? '+' : '' }}{{ (line.physical_quantity - line.system_quantity).toFixed(2) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="form-group" style="margin-top:1rem">
      <label>Notes</label>
      <textarea v-model="notes" class="form-input" rows="3" placeholder="Observations…"></textarea>
    </div>

    <div class="form-actions">
      <button @click="submit" class="btn-primary">
        <i class="fa-solid fa-save"></i> Enregistrer l'inventaire
      </button>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.15rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;padding-left:12px;border-left:3px solid #8B5CF6}
.btn-back{color:#6B7280;font-size:.875rem;display:inline-flex;align-items:center;gap:.3rem}
.info-bar{display:flex;gap:1.5rem;margin-bottom:1rem;font-size:.9rem;color:#374151}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.6rem .75rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.5rem .75rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.gap-row{background:#FEF9C3}
.text-muted{color:#9CA3AF}
.form-input{padding:.45rem .6rem;border:1px solid #D1D5DB;font-size:.875rem;width:100%}
.input-sm{width:100px}
.form-group{display:flex;flex-direction:column;gap:.3rem}
.form-group label{font-size:.8rem;font-weight:600;color:#374151}
.form-actions{display:flex;justify-content:flex-end;margin-top:1rem}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1.25rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
</style>
