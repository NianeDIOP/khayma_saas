<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

defineProps({ title: { type: String, default: 'Admin' } })

const page = usePage()
const sidebarOpen = ref(true)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }
const adminName = page.props.auth?.user?.name || 'Admin'
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
        <Link href="/" class="topbar-action">
          <i class="fa-solid fa-arrow-left"></i> Retour au site
        </Link>
        <div class="topbar-sep"></div>
        <div class="topbar-user">
          <i class="fa-solid fa-user-shield"></i>
          {{ adminName }}
        </div>
        <Link :href="route('logout')" method="post" as="button" class="topbar-logout">
          <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </Link>
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
          <i class="fa-solid fa-clipboard-list" style="color:#EF4444"></i> <span>Logs d'audit</span>
        </Link>
        <Link :href="route('admin.settings.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/settings') }]">
          <i class="fa-solid fa-cog" style="color:#6B7280"></i> <span>Paramètres</span>
        </Link>
        <Link :href="route('admin.legal-pages.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/legal-pages') }]">
          <i class="fa-solid fa-file-contract" style="color:#8B5CF6"></i> <span>Pages légales</span>
        </Link>
        <Link :href="route('admin.faqs.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/faqs') }]">
          <i class="fa-solid fa-circle-question" style="color:#F59E0B"></i> <span>FAQ</span>
        </Link>
        <Link :href="route('admin.blog-posts.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/blog-posts') }]">
          <i class="fa-solid fa-newspaper" style="color:#3B82F6"></i> <span>Blog</span>
        </Link>
        <Link :href="route('admin.contact-messages.index')"
          :class="['nav-item', { active: $page.url.startsWith('/admin/contact-messages') }]">
          <i class="fa-solid fa-envelope" style="color:#EF4444"></i> <span>Messages</span>
        </Link>
      </nav>

      <!-- Sidebar footer with admin name -->
      <div class="sidebar-user-footer">
        <div class="sidebar-user-avatar">
          <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name">{{ adminName }}</div>
          <div class="sidebar-user-role">Super Admin</div>
        </div>
      </div>
    </aside>

    <!-- CONTENT -->
    <main class="admin-content">
      <slot />

      <!-- Content footer -->
      <footer class="admin-footer">
        <span>&copy; {{ new Date().getFullYear() }} Khayma — Plateforme SaaS</span>
        <span class="footer-sep">·</span>
        <span>Backoffice Administration</span>
      </footer>
    </main>
  </div>
</template>

<style scoped>
.admin-shell { min-height: 100vh; background: #F9FAFB; font-family: 'Inter', sans-serif; }

/* ── Topbar ─── */
.admin-topbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 20;
  height: 56px; background: #FFFFFF; padding: 0 20px;
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.topbar-left { display: flex; align-items: center; gap: 14px; }
.hamburger {
  background: none; border: none; color: #9CA3AF; font-size: 1.1rem;
  cursor: pointer; padding: 6px 8px; transition: color 0.15s;
}
.hamburger:hover { color: #4F46E5; }
.topbar-logo-link {
  display: flex; align-items: center; gap: 0.6rem; text-decoration: none;
}
.topbar-logo {
  height: 38px; width: auto; display: block; object-fit: contain;
  filter: drop-shadow(0 0 4px rgba(16,185,129,0.2));
}
.topbar-tagline {
  font-size: 0.6rem; color: #6B7280; font-weight: 600;
  letter-spacing: 0.08em; text-transform: uppercase;
  border-left: 2px solid #10B981; padding-left: 0.5rem;
  line-height: 1.3; white-space: nowrap;
}
.topbar-right {
  display: flex; align-items: center; gap: 6px;
}
.topbar-action {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.78rem; color: #6B7280; text-decoration: none;
  padding: 6px 12px; border-radius: 6px; transition: all 0.15s;
  font-weight: 500;
}
.topbar-action:hover { background: #F3F4F6; color: #4F46E5; }
.topbar-sep { width: 1px; height: 24px; background: #E5E7EB; margin: 0 4px; }
.topbar-user {
  display: flex; align-items: center; gap: 6px;
  font-size: 0.8rem; color: #374151; font-weight: 600;
}
.topbar-user i { color: #6366F1; font-size: 0.9rem; }
.topbar-logout {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.78rem; color: #9CA3AF; text-decoration: none;
  padding: 6px 12px; border-radius: 6px; transition: all 0.15s;
  background: none; border: none; cursor: pointer; font-weight: 500;
}
.topbar-logout:hover { background: #FEF2F2; color: #EF4444; }

/* ── Sidebar ─── */
.admin-sidebar {
  width: 220px; position: fixed; top: 56px; left: 0; bottom: 0; z-index: 10;
  background: transparent; border-right: 1px solid #E5E7EB;
  display: flex; flex-direction: column; transition: transform 0.2s ease;
}
.sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 12px 16px;
  color: #374151; font-size: 0.88rem; font-weight: 600; text-decoration: none;
  transition: background 0.15s, color 0.15s; cursor: pointer; border: none; background: none;
  width: 100%; text-align: left; letter-spacing: -0.01em;
}
.nav-item:hover { background: #F3F4F6; color: #111827; }
.nav-item.active { background: #EEF2FF; color: #4F46E5; border-right: 3px solid #4F46E5; }
.nav-item i { width: 20px; text-align: center; font-size: 1.05rem; flex-shrink: 0; }

/* Sidebar user footer */
.sidebar-user-footer {
  padding: 14px 18px; border-top: 1px solid #F3F4F6;
  display: flex; align-items: center; gap: 10px;
  background: #F9FAFB;
}
.sidebar-user-avatar {
  width: 34px; height: 34px; border-radius: 8px;
  background: linear-gradient(135deg, #6366F1, #818CF8);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 0.85rem; flex-shrink: 0;
}
.sidebar-user-info { min-width: 0; }
.sidebar-user-name {
  font-size: 0.78rem; font-weight: 600; color: #374151;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sidebar-user-role {
  font-size: 0.65rem; color: #9CA3AF; font-weight: 500;
}

/* ── Content ─── */
.admin-content {
  margin-left: 220px; margin-top: 56px; padding: 24px;
  min-height: calc(100vh - 56px); transition: margin-left 0.2s ease;
  display: flex; flex-direction: column;
}
.sidebar-collapsed .admin-content { margin-left: 0; }
.sidebar-collapsed .admin-sidebar { transform: translateX(-100%); }

/* Content footer */
.admin-footer {
  margin-top: auto; padding-top: 32px;
  border-top: 1px solid #F3F4F6;
  text-align: center; font-size: 0.72rem; color: #9CA3AF;
  padding-bottom: 16px;
}
.footer-sep { margin: 0 6px; }
</style>
