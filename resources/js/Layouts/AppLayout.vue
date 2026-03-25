<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <span class="logo-text">Khayma</span>
      </div>
      <nav class="sidebar-nav">
        <Link :href="route('dashboard')" class="nav-item" :class="{ active: $page.url === '/dashboard' }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
          <span>Tableau de bord</span>
        </Link>
      </nav>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
      <!-- Header -->
      <header class="app-header">
        <div class="header-left">
          <button class="menu-toggle" @click="sidebarOpen = !sidebarOpen">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
          </button>
          <h1 class="page-title">{{ title }}</h1>
        </div>
        <div class="header-right">
          <span class="user-name">{{ $page.props.auth.user?.name }}</span>
        </div>
      </header>

      <!-- Content -->
      <main class="app-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  title: {
    type: String,
    default: 'Khayma',
  },
})

const sidebarOpen = ref(true)
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background: #F9FAFB;
}
.sidebar {
  width: 240px;
  background: #0F172A;
  color: #E2E8F0;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}
.sidebar-logo {
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.logo-text {
  font-size: 1.25rem;
  font-weight: 800;
  color: #10B981;
  letter-spacing: -0.5px;
}
.sidebar-nav {
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 0;
  color: #94A3B8;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.15s, color 0.15s;
}
.nav-item:hover, .nav-item.active {
  background: rgba(16, 185, 129, 0.12);
  color: #10B981;
}
.nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.app-header {
  height: 56px;
  background: #fff;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
}
.header-left { display: flex; align-items: center; gap: 16px; }
.menu-toggle { background: none; border: none; cursor: pointer; color: #6B7280; }
.menu-toggle svg { width: 20px; height: 20px; }
.page-title { font-size: 0.95rem; font-weight: 700; color: #111827; }
.user-name { font-size: 0.875rem; color: #374151; font-weight: 500; }
.app-content { padding: 24px; flex: 1; }
</style>
