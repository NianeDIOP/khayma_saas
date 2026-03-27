<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ inventory: Object })
const t = () => route().params._tenant

function fmtDt(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

function validateInventory() {
  if (!confirm('Valider l\'inventaire ? Le stock sera ajusté selon les quantités physiques saisies.')) return
  router.post(route('app.quincaillerie.inventories.validate', { _tenant: t(), inventory: props.inventory.id }))
}

const STATUS_LABELS = { in_progress: 'En cours', validated: 'Validé' }
const STATUS_COLORS = { in_progress: '#F59E0B', validated: '#10B981' }
</script>

<template>
  <AppLayout title="Détail inventaire">
    <Head title="Détail inventaire" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-clipboard-check" style="color:#8B5CF6"></i> {{ inventory.reference }}</h1>
      <Link :href="route('app.quincaillerie.inventories.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-circle-check"></i> {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="flash-error">
      <i class="fa-solid fa-circle-xmark"></i> {{ $page.props.flash.error }}
    </div>

    <div class="detail-card">
      <div class="detail-grid">
        <div><span class="detail-label">Dépôt</span><span class="detail-value">{{ inventory.depot?.name || '—' }}</span></div>
        <div><span class="detail-label">Statut</span><span class="badge" :style="{ background: STATUS_COLORS[inventory.status] }">{{ STATUS_LABELS[inventory.status] }}</span></div>
        <div><span class="detail-label">Créé par</span><span class="detail-value">{{ inventory.user?.name || '—' }}</span></div>
        <div><span class="detail-label">Date</span><span class="detail-value">{{ fmtDt(inventory.created_at) }}</span></div>
        <div><span class="detail-label">Validé le</span><span class="detail-value">{{ fmtDt(inventory.validated_at) }}</span></div>
      </div>
      <div v-if="inventory.notes" class="notes-block"><strong>Notes :</strong> {{ inventory.notes }}</div>
    </div>

    <h3 class="section-title">Lignes d'inventaire</h3>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Produit</th><th>Qté système</th><th>Qté physique</th><th>Écart</th><th>Notes</th></tr>
        </thead>
        <tbody>
          <tr v-for="line in inventory.lines" :key="line.id"
              :class="{ 'gap-row': line.gap != 0 }">
            <td>{{ line.product?.name }}</td>
            <td>{{ line.system_quantity }}</td>
            <td class="font-medium">{{ line.physical_quantity }}</td>
            <td :style="{ color: line.gap < 0 ? '#EF4444' : line.gap > 0 ? '#10B981' : '#6B7280', fontWeight: 600 }">
              {{ line.gap > 0 ? '+' : '' }}{{ line.gap }}
            </td>
            <td>{{ line.notes || '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="actions-bar" v-if="inventory.status === 'in_progress'">
      <button @click="validateInventory" class="btn-primary" style="background:#10B981">
        <i class="fa-solid fa-check"></i> Valider l'inventaire
      </button>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.page-title{font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.5rem}
.btn-back{color:#6B7280;font-size:.875rem;display:inline-flex;align-items:center;gap:.3rem}
.flash-success{background:#D1FAE5;color:#065F46;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.flash-error{background:#FEE2E2;color:#991B1B;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.detail-card{background:#fff;border:1px solid #E5E7EB;padding:1.25rem;margin-bottom:1.5rem}
.detail-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.detail-label{display:block;font-size:.75rem;color:#6B7280;text-transform:uppercase;font-weight:600;margin-bottom:.2rem}
.detail-value{font-size:.95rem;color:#111}
.badge{color:#fff;padding:.15rem .5rem;font-size:.75rem;font-weight:600;display:inline-block}
.notes-block{margin-top:1rem;padding:.75rem;background:#F9FAFB;font-size:.875rem;color:#374151}
.section-title{font-size:1rem;font-weight:600;margin-bottom:.75rem;color:#111}
.table-wrap{overflow-x:auto;margin-bottom:1.5rem}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:#F3F4F6;text-align:left;padding:.5rem .6rem;font-size:.75rem;text-transform:uppercase;color:#6B7280;font-weight:600}
.data-table td{padding:.5rem .6rem;border-bottom:1px solid #E5E7EB;font-size:.875rem}
.font-medium{font-weight:500}
.gap-row{background:#FEF9C3}
.actions-bar{display:flex;gap:.5rem}
.btn-primary{background:#2563EB;color:#fff;padding:.5rem 1rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;border:none;cursor:pointer}
</style>
