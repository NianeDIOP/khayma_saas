<template>
  <AppLayout title="Tableau de bord">
    <div class="dashboard">
      <div class="dashboard-welcome">
        <h2>Bonjour, <strong>{{ auth.user?.name }}</strong> 👋</h2>
        <p>Bienvenue sur votre espace Khayma.</p>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-icon" style="background:#ECFDF5;color:#10B981;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
          </div>
          <div>
            <div class="kpi-value">—</div>
            <div class="kpi-label">CA du jour</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:#FFF7ED;color:#F59E0B;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
          </div>
          <div>
            <div class="kpi-value">—</div>
            <div class="kpi-label">Ventes aujourd'hui</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:#EFF6FF;color:#3B82F6;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
          </div>
          <div>
            <div class="kpi-value">—</div>
            <div class="kpi-label">Clients actifs</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:#FEF2F2;color:#EF4444;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
          </div>
          <div>
            <div class="kpi-value">—</div>
            <div class="kpi-label">Alertes stock</div>
          </div>
        </div>
      </div>

      <div class="dashboard-note">
        <p>Les données seront disponibles après la configuration du module métier (Phase 2A).</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'

const { props } = usePage()
const auth = props.auth
</script>

<style scoped>
.dashboard-welcome { margin-bottom: 24px; }
.dashboard-welcome h2 { font-size: 1.1rem; font-weight: 600; color: #111827; }
.dashboard-welcome p { font-size: 0.875rem; color: #6B7280; margin-top: 4px; }
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.kpi-card {
  background: #fff;
  border: 1px solid #E5E7EB;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.kpi-icon {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.kpi-icon svg { width: 22px; height: 22px; }
.kpi-value { font-size: 1.5rem; font-weight: 700; color: #111827; }
.kpi-label { font-size: 0.78rem; color: #6B7280; margin-top: 2px; }
.dashboard-note {
  background: #F0FDF4;
  border: 1px solid #D1FAE5;
  border-left: 3px solid #10B981;
  padding: 12px 16px;
  font-size: 0.82rem;
  color: #065F46;
}
</style>
