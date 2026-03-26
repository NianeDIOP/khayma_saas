<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ pages: Array })

const form = useForm({
  pages: props.pages.map(p => ({ key: p.key, value: p.value || '' })),
})

const labels = {}
props.pages.forEach(p => { labels[p.key] = p.label })

const activeTab = $ref ? $ref(props.pages[0]?.key) : null
import { ref } from 'vue'
const currentTab = ref(props.pages[0]?.key || '')

function submit() {
  form.put(route('admin.legal-pages.update'))
}
</script>

<template>
  <AdminLayout title="Pages légales">
    <Head title="Admin · Pages légales" />

    <div class="toolbar">
      <h1 class="page-title"><i class="fa-solid fa-file-contract"></i> Pages légales</h1>
      <p class="subtitle">Rédigez le contenu des pages juridiques de la plateforme.</p>
    </div>

    <!-- Flash -->
    <div v-if="$page.props.flash?.success" class="flash-success">
      <i class="fa-solid fa-check-circle"></i> {{ $page.props.flash.success }}
    </div>

    <div class="legal-editor">
      <!-- Tabs -->
      <div class="tabs">
        <button v-for="page in pages" :key="page.key"
          :class="['tab', { active: currentTab === page.key }]"
          @click="currentTab = page.key">
          {{ page.label }}
        </button>
      </div>

      <!-- Tab content -->
      <form @submit.prevent="submit">
        <div v-for="(pg, idx) in form.pages" :key="pg.key" v-show="currentTab === pg.key" class="tab-panel">
          <label class="panel-label">{{ labels[pg.key] }}</label>
          <textarea v-model="form.pages[idx].value" rows="18" class="kh-textarea"
                    :placeholder="'Rédigez ici le contenu de ' + labels[pg.key] + '...'"></textarea>
          <span v-if="form.errors['pages.' + idx + '.value']" class="error">
            {{ form.errors['pages.' + idx + '.value'] }}
          </span>
          <p class="hint">Vous pouvez utiliser du texte simple. Le contenu sera affiché tel quel aux utilisateurs.</p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-submit" :disabled="form.processing">
            <i class="fa-solid fa-check"></i> Enregistrer toutes les pages
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.toolbar { margin-bottom: 16px; }
.page-title {
  font-size: 1.1rem; font-weight: 700; color: #111827;
  padding-left: 12px; border-left: 3px solid #6366F1;
  display: flex; align-items: center; gap: 8px;
}
.subtitle { font-size: 0.78rem; color: #6B7280; margin-top: 4px; padding-left: 15px; }

.flash-success {
  background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46;
  padding: 10px 14px; font-size: 0.8rem; font-weight: 600; margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}

.legal-editor { background: #fff; border: 1px solid #E5E7EB; }

.tabs {
  display: flex; flex-wrap: wrap; gap: 0; border-bottom: 1px solid #E5E7EB;
  background: #F9FAFB;
}
.tab {
  padding: 10px 18px; font-size: 0.78rem; font-weight: 600; color: #6B7280;
  background: none; border: none; cursor: pointer;
  border-bottom: 2px solid transparent; transition: all 0.15s;
}
.tab:hover { color: #374151; background: #F3F4F6; }
.tab.active { color: #4F46E5; border-bottom-color: #4F46E5; background: #fff; }

.tab-panel { padding: 20px; }
.panel-label { display: block; font-size: 0.82rem; font-weight: 700; color: #374151; margin-bottom: 8px; }

.kh-textarea {
  width: 100%; padding: 12px 14px; border: 1px solid #D1D5DB; font-size: 0.82rem;
  font-family: 'Inter', sans-serif; background: #FAFAFA; resize: vertical;
  line-height: 1.6; min-height: 300px;
}
.kh-textarea:focus { border-color: #6366F1; outline: none; background: #fff; }

.hint { font-size: 0.7rem; color: #9CA3AF; margin-top: 6px; }
.error { color: #EF4444; font-size: 0.72rem; display: block; margin-top: 4px; }

.form-actions { padding: 16px 20px; border-top: 1px solid #F3F4F6; }
.btn-submit {
  padding: 8px 20px; background: #6366F1; color: #fff; border: none;
  font-size: 0.82rem; font-weight: 600; cursor: pointer;
  display: inline-flex; align-items: center; gap: 6px;
}
.btn-submit:disabled { opacity: 0.6; }
.btn-submit:hover:not(:disabled) { background: #4F46E5; }
</style>
