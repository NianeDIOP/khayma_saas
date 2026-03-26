<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

defineProps({ title: { type: String, default: 'Admin' } })

const page = usePage()
const sidebarOpen = ref(true)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }
</script>

<template>
  <div class="admin-shell" :class="{ 'sidebar-collapsed': !sidebarOpen }">
    <!-- TOPBAR pleine largeur -->
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="hamburger" @click="toggleSidebar" title="Menu">
          <i :class="sidebarOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'"></i>
        </button>
        <a href="/" class="topbar-logo-link">
          <img src="/khayma_logo_transparent.png" alt="Khayma" class="topbar-logo" />
          <span class="topbar-tagline">Tenter<br>&amp; Estimer</span>
        </a>
      </div>
      <div class="topbar-right">
        <i class="fa-solid fa-user-shield" style="color:#6366F1;"></i>
        {{ $page.props.auth?.user?.name || 'Admin' }}
      </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" v-show="sidebarOpen">
      <nav class="sidebar-nav">
        <Link :href="route('admin.dashboard')"
          :class="['nav-item', { active: $page.url === '/admin' }]">
          <i class="fa-solid fa-gauge-high" style="color:#10B981"></i> <span>Dashboard</span>
        </Link>
        <Link :href="route('admin.companies.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/companies') }]">
          <i class="fa-solid fa-building" style="color:#8B5CF6"></i> <span>Entreprises</span>
        </Link>
        <Link :href="route('admin.plans.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/plans') }]">
          <i class="fa-solid fa-tags" style="color:#F59E0B"></i> <span>Plans & Tarifs</span>
        </Link>
        <Link :href="route('admin.modules.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/modules') }]">
          <i class="fa-solid fa-puzzle-piece" style="color:#EC4899"></i> <span>Modules</span>
        </Link>
        <Link :href="route('admin.subscriptions.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/subscriptions') }]">
          <i class="fa-solid fa-credit-card" style="color:#10B981"></i> <span>Abonnements</span>
        </Link>
        <Link :href="route('admin.users.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/users') }]">
          <i class="fa-solid fa-users" style="color:#3B82F6"></i> <span>Utilisateurs</span>
        </Link>
        <Link :href="route('admin.audit-logs.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/audit-logs') }]">
          <i class="fa-solid fa-clipboard-list" style="color:#EF4444"></i> <span>Logs</span>
        </Link>
        <Link :href="route('admin.settings.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/settings') }]">
          <i class="fa-solid fa-cog" style="color:#6B7280"></i> <span>Paramètres</span>
        </Link>
      </nav>
      <div class="sidebar-footer">
        <Link href="/" class="nav-item">
          <i class="fa-solid fa-arrow-left" style="color:#6B7280"></i> <span>Retour au site</span>
        </Link>
        <Link :href="route('logout')" method="post" as="button" class="nav-item logout">
          <i class="fa-solid fa-right-from-bracket" style="color:#EF4444"></i> <span>Déconnexion</span>
        </Link>
      </div>
    </aside>

    <!-- CONTENT -->
    <main class="admin-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.admin-shell { min-height: 100vh; background: #F9FAFB; font-family: 'Inter', sans-serif; }

/* ── Topbar pleine largeur ─── */
.admin-topbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 20;
  height: 60px; background: transparent; padding: 0 24px;
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid #E5E7EB;
}
.topbar-left { display: flex; align-items: center; gap: 14px; }

/* Hamburger */
.hamburger {
  background: none; border: none; color: #6B7280; font-size: 1.2rem;
  cursor: pointer; padding: 6px 8px; transition: color 0.15s;
}
.hamburger:hover { color: #111827; }

/* Logo comme la landing */
.topbar-logo-link {
  display: flex; align-items: center; gap: 0.6rem; text-decoration: none;
}
.topbar-logo {
  height: 44px; width: auto; display: block; object-fit: contain;
  filter: drop-shadow(0 0 6px rgba(16,185,129,0.25));
}
.topbar-tagline {
  font-size: 0.65rem; color: #374151; font-weight: 600;
  letter-spacing: 0.08em; text-transform: uppercase;
  border-left: 2px solid #10B981; padding-left: 0.6rem;
  line-height: 1.3; white-space: nowrap;
}

.topbar-right { font-size: 0.82rem; color: #374151; display: flex; align-items: center; gap: 8px; }

/* ── Sidebar ─── */
.admin-sidebar {
  width: 220px; position: fixed; top: 60px; left: 0; bottom: 0; z-index: 10;
  background: transparent; border-right: 1px solid #E5E7EB;
  display: flex; flex-direction: column; transition: transform 0.2s ease;
}
.sidebar-nav { flex: 1; padding: 12px 0; }
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 12px 16px;
  color: #374151; font-size: 0.88rem; font-weight: 600; text-decoration: none;
  transition: background 0.15s, color 0.15s; cursor: pointer; border: none; background: none; width: 100%; text-align: left;
  letter-spacing: -0.01em;
}
.nav-item:hover { background: #F3F4F6; color: #111827; }
.nav-item.active { background: #EEF2FF; color: #4F46E5; border-right: 3px solid #4F46E5; }
.nav-item i { width: 20px; text-align: center; font-size: 1.05rem; flex-shrink: 0; }
.sidebar-footer { padding: 12px 0; border-top: 1px solid #E5E7EB; }
.nav-item.logout:hover { color: #EF4444; }

/* ── Content ─── */
.admin-content {
  margin-left: 220px; margin-top: 60px; padding: 24px;
  min-height: calc(100vh - 60px); transition: margin-left 0.2s ease;
}
.sidebar-collapsed .admin-content { margin-left: 0; }
</style>
