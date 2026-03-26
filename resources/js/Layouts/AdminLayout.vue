<script setup>
import { Link, usePage } from '@inertiajs/vue3'

defineProps({ title: { type: String, default: 'Admin' } })

const page = usePage()
</script>

<template>
  <div class="admin-shell">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-logo">
        <img src="/khayma_logo_transparent.png" alt="Khayma" style="height:36px;" />
        <span class="admin-tag">Admin</span>
      </div>
      <nav class="sidebar-nav">
        <Link :href="route('admin.dashboard')"
          :class="['nav-item', { active: $page.url.startsWith('/admin') && $page.url === '/admin' }]">
          <i class="fa-solid fa-gauge-high"></i> Dashboard
        </Link>
        <Link :href="route('admin.companies.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/companies') }]">
          <i class="fa-solid fa-building"></i> Entreprises
        </Link>
        <Link :href="route('admin.users.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/users') }]">
          <i class="fa-solid fa-users"></i> Utilisateurs
        </Link>
      </nav>
      <div class="sidebar-footer">
        <Link href="/" class="nav-item">
          <i class="fa-solid fa-arrow-left"></i> Retour au site
        </Link>
        <Link href="/logout" method="post" as="button" class="nav-item logout">
          <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </Link>
      </div>
    </aside>

    <!-- Main -->
    <div class="admin-main">
      <header class="admin-topbar">
        <div class="topbar-title">{{ title }}</div>
        <div class="topbar-user">
          <i class="fa-solid fa-user-shield" style="color:#6366F1;"></i>
          {{ $page.props.auth?.user?.name || 'Admin' }}
        </div>
      </header>
      <main class="admin-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.admin-shell { display: flex; min-height: 100vh; background: #F9FAFB; font-family: 'Inter', sans-serif; }
.admin-sidebar {
  width: 220px; flex-shrink: 0; background: #111827; display: flex;
  flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 10;
}
.sidebar-logo { padding: 18px 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #1F2937; }
.admin-tag { background: #6366F1; color: #fff; font-size: 0.62rem; font-weight: 700; padding: 1px 6px; letter-spacing: 0.05em; }
.sidebar-nav { flex: 1; padding: 12px 0; }
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 10px 16px;
  color: #9CA3AF; font-size: 0.82rem; font-weight: 500; text-decoration: none;
  transition: background 0.15s; cursor: pointer; border: none; background: none; width: 100%; text-align: left;
}
.nav-item:hover, .nav-item.active { background: #1F2937; color: #F9FAFB; }
.nav-item.active { color: #A5B4FC; }
.nav-item i { width: 16px; text-align: center; }
.sidebar-footer { padding: 12px 0; border-top: 1px solid #1F2937; }
.nav-item.logout:hover { color: #FCA5A5; }
.admin-main { margin-left: 220px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
.admin-topbar {
  background: #fff; border-bottom: 1px solid #E5E7EB; padding: 0 24px;
  height: 52px; display: flex; align-items: center; justify-content: space-between;
}
.topbar-title { font-size: 0.9rem; font-weight: 700; color: #111827; }
.topbar-user { font-size: 0.8rem; color: #6B7280; display: flex; align-items: center; gap: 6px; }
.admin-content { padding: 24px; flex: 1; }
</style>
