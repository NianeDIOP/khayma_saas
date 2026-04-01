<script setup>
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    files:      Object,
    folders:    Array,
    usedBytes:  Number,
    limitBytes: [Number, null],
    filters:    Object,
})

const uploadForm = useForm({
    file:   null,
    folder: '',
})

function onFileChange(e) {
    uploadForm.file = e.target.files[0]
}

function upload() {
    uploadForm.post(route('app.storage.upload'), {
        forceFormData: true,
        onSuccess: () => {
            uploadForm.reset()
            document.getElementById('file-input').value = ''
        },
    })
}

function deleteFile(id) {
    if (confirm('Supprimer ce fichier ?')) {
        router.delete(route('app.storage.destroy', id))
    }
}

function humanSize(bytes) {
    if (bytes < 1024) return bytes + ' o'
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' Ko'
    if (bytes < 1073741824) return (bytes / 1048576).toFixed(1) + ' Mo'
    return (bytes / 1073741824).toFixed(2) + ' Go'
}

const usagePercent = props.limitBytes
    ? Math.min(100, Math.round((props.usedBytes / props.limitBytes) * 100))
    : null
</script>

<template>
    <Head title="Fichiers — Stockage" />
    <AppLayout>
        <div class="st-page">
            <div class="st-header">
                <h1>Fichiers & Stockage</h1>
                <div class="st-usage">
                    <span>{{ humanSize(usedBytes) }}</span>
                    <template v-if="limitBytes"> / {{ humanSize(limitBytes) }}</template>
                    <template v-if="usagePercent !== null">
                        <div class="usage-bar"><div class="usage-fill" :style="{ width: usagePercent + '%' }"></div></div>
                    </template>
                </div>
            </div>

            <!-- Upload -->
            <div class="st-upload">
                <form @submit.prevent="upload" class="upload-form">
                    <input id="file-input" type="file" @change="onFileChange" />
                    <input v-model="uploadForm.folder" type="text" placeholder="Dossier (optionnel)" class="folder-input" />
                    <button type="submit" class="btn-sm-green" :disabled="!uploadForm.file || uploadForm.processing">
                        Téléverser
                    </button>
                </form>
                <div v-if="uploadForm.errors.file" class="field-error">{{ uploadForm.errors.file }}</div>
            </div>

            <!-- Folder filter -->
            <div v-if="folders.length > 1" class="st-filters">
                <Link :href="route('app.storage.index')" :class="['filter-btn', { active: !filters.folder }]">Tous</Link>
                <Link v-for="f in folders" :key="f"
                      :href="route('app.storage.index', { folder: f })"
                      :class="['filter-btn', { active: filters.folder === f }]">
                    {{ f }}
                </Link>
            </div>

            <!-- File list -->
            <table class="st-table" v-if="files.data.length">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Dossier</th>
                        <th>Taille</th>
                        <th>Par</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="f in files.data" :key="f.id">
                        <td class="fname">{{ f.original_name }}</td>
                        <td>{{ f.folder }}</td>
                        <td>{{ humanSize(f.size) }}</td>
                        <td>{{ f.uploader?.name ?? '—' }}</td>
                        <td>{{ new Date(f.created_at).toLocaleDateString('fr-FR') }}</td>
                        <td>
                            <button class="btn-xs-red" @click="deleteFile(f.id)">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="empty">Aucun fichier téléversé.</p>

            <!-- Pagination -->
            <div v-if="files.links && files.last_page > 1" class="pagination">
                <Link v-for="link in files.links" :key="link.label"
                      :href="link.url"
                      :class="['pg-link', { active: link.active, disabled: !link.url }]"
                      v-html="link.label" />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.st-page { max-width: 960px; margin: 0 auto; padding: 32px 16px; }
.st-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.st-header h1 { font-size: 1.4rem; font-weight: 800; color: #0F172A; }
.st-usage { font-size: 0.85rem; color: #6B7280; display: flex; align-items: center; gap: 8px; }
.usage-bar { width: 120px; height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden; }
.usage-fill { height: 100%; background: #10B981; border-radius: 4px; transition: width 0.3s; }
.st-upload { margin-bottom: 24px; }
.upload-form { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
.folder-input { padding: 8px 12px; border: 1px solid #D1D5DB; font-size: 0.85rem; width: 180px; }
.btn-sm-green { padding: 8px 18px; background: #10B981; color: #fff; font-weight: 600; border: none; cursor: pointer; font-size: 0.85rem; }
.btn-sm-green:hover:not(:disabled) { background: #059669; }
.btn-sm-green:disabled { opacity: 0.5; }
.field-error { color: #EF4444; font-size: 0.8rem; margin-top: 4px; }
.st-filters { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-btn { padding: 6px 14px; border: 1px solid #D1D5DB; font-size: 0.8rem; color: #374151; text-decoration: none; border-radius: 4px; }
.filter-btn.active { background: #10B981; color: #fff; border-color: #10B981; }
.st-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.st-table th { text-align: left; padding: 10px 12px; color: #6B7280; border-bottom: 2px solid #E5E7EB; font-weight: 600; }
.st-table td { padding: 10px 12px; border-bottom: 1px solid #F3F4F6; color: #0F172A; }
.fname { font-weight: 600; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btn-xs-red { padding: 4px 10px; background: #FEE2E2; color: #B91C1C; border: none; font-size: 0.78rem; cursor: pointer; font-weight: 600; }
.btn-xs-red:hover { background: #FECACA; }
.empty { color: #9CA3AF; text-align: center; padding: 40px; font-size: 0.9rem; }
.pagination { display: flex; gap: 4px; justify-content: center; margin-top: 24px; }
.pg-link { padding: 6px 12px; border: 1px solid #D1D5DB; font-size: 0.8rem; text-decoration: none; color: #374151; }
.pg-link.active { background: #10B981; color: #fff; border-color: #10B981; }
.pg-link.disabled { opacity: 0.4; pointer-events: none; }
</style>
