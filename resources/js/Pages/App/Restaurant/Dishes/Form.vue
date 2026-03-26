<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ dish: Object, categories: Array })
const isEdit = !!props.dish
const t = () => route().params._tenant

const form = useForm({
  restaurant_category_id: props.dish?.restaurant_category_id || '',
  name:              props.dish?.name || '',
  description:       props.dish?.description || '',
  price:             props.dish?.price || '',
  image_url:         props.dish?.image_url || '',
  is_available:      props.dish?.is_available ?? true,
  available_morning: props.dish?.available_morning ?? true,
  available_noon:    props.dish?.available_noon ?? true,
  available_evening: props.dish?.available_evening ?? true,
  is_additional:     props.dish?.is_additional ?? false,
  promo_price:       props.dish?.promo_price || '',
  promo_start:       props.dish?.promo_start || '',
  promo_end:         props.dish?.promo_end || '',
  sort_order:        props.dish?.sort_order ?? 0,
})

function submit() {
  if (isEdit) {
    form.put(route('app.restaurant.dishes.update', { dish: props.dish.id, _tenant: t() }))
  } else {
    form.post(route('app.restaurant.dishes.store', { _tenant: t() }))
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Modifier plat' : 'Nouveau plat'">
    <Head :title="isEdit ? 'Modifier plat' : 'Nouveau plat'" />

    <div class="page-header">
      <h1 class="page-title"><i class="fa-solid fa-utensils" style="color:#EF4444"></i> {{ isEdit ? 'Modifier' : 'Nouveau' }} plat</h1>
      <Link :href="route('app.restaurant.dishes.index', { _tenant: t() })" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
      </Link>
    </div>

    <form @submit.prevent="submit" class="form-card">
      <div class="form-grid">
        <div class="field">
          <label>Catégorie *</label>
          <select v-model="form.restaurant_category_id" :class="{ 'is-error': form.errors.restaurant_category_id }" required>
            <option value="">— Choisir —</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <span v-if="form.errors.restaurant_category_id" class="field-error">{{ form.errors.restaurant_category_id }}</span>
        </div>

        <div class="field">
          <label>Nom *</label>
          <input v-model="form.name" type="text" :class="{ 'is-error': form.errors.name }" required />
          <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
        </div>

        <div class="field span-2">
          <label>Description</label>
          <textarea v-model="form.description" rows="2"></textarea>
        </div>

        <div class="field">
          <label>Prix (XOF) *</label>
          <input v-model.number="form.price" type="number" min="0" step="50" :class="{ 'is-error': form.errors.price }" required />
          <span v-if="form.errors.price" class="field-error">{{ form.errors.price }}</span>
        </div>

        <div class="field">
          <label>URL image</label>
          <input v-model="form.image_url" type="text" placeholder="https://…" />
        </div>

        <div class="field">
          <label>Prix promo (XOF)</label>
          <input v-model.number="form.promo_price" type="number" min="0" step="50" />
        </div>

        <div class="field">
          <label>Début promo</label>
          <input v-model="form.promo_start" type="date" />
        </div>

        <div class="field">
          <label>Fin promo</label>
          <input v-model="form.promo_end" type="date" />
        </div>

        <div class="field">
          <label>Ordre d'affichage</label>
          <input v-model.number="form.sort_order" type="number" min="0" />
        </div>
      </div>

      <div class="checks-group">
        <label class="check-label"><input v-model="form.is_available" type="checkbox" /> Disponible</label>
        <label class="check-label"><input v-model="form.available_morning" type="checkbox" /> Matin</label>
        <label class="check-label"><input v-model="form.available_noon" type="checkbox" /> Midi</label>
        <label class="check-label"><input v-model="form.available_evening" type="checkbox" /> Soir</label>
        <label class="check-label"><input v-model="form.is_additional" type="checkbox" /> Extra (boisson, dessert…)</label>
      </div>

      <button type="submit" class="btn-submit" :disabled="form.processing">
        {{ form.processing ? 'Enregistrement…' : (isEdit ? 'Mettre à jour' : 'Créer') }}
      </button>
    </form>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 1.15rem; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; padding-left: 12px; border-left: 3px solid #EF4444; }
.btn-back { font-size: 0.82rem; color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 6px; }
.form-card { background: #fff; border: 1px solid #E5E7EB; padding: 24px; max-width: 700px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.span-2 { grid-column: span 2; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 0.82rem; font-weight: 600; color: #374151; }
.field input, .field select, .field textarea { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.88rem; outline: none; font-family: inherit; }
.field input:focus, .field select:focus, .field textarea:focus { border-color: #EF4444; }
.field input.is-error, .field select.is-error { border-color: #EF4444; }
.field-error { font-size: 0.78rem; color: #EF4444; }
.checks-group { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 22px; padding: 14px; border: 1px solid #F3F4F6; background: #FAFAFA; }
.check-label { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #374151; cursor: pointer; }
.check-label input[type="checkbox"] { accent-color: #EF4444; width: 16px; height: 16px; }
.btn-submit { background: #EF4444; color: #fff; padding: 10px 24px; font-size: 0.88rem; font-weight: 600; border: none; cursor: pointer; }
.btn-submit:hover:not(:disabled) { background: #DC2626; }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
