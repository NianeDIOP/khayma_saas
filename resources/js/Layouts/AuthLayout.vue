<template>
  <div class="auth-layout">
    <div class="auth-card">
      <div class="auth-logo">
        <span class="logo-text">Khayma</span>
        <p class="logo-sub">Là où votre business prend vie.</p>
      </div>
      <slot />
    </div>
  </div>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  background: #F9FAFB;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.auth-card {
  background: #fff;
  border: 1px solid #E5E7EB;
  width: 100%;
  max-width: 420px;
  padding: 40px;
}
.auth-logo { text-align: center; margin-bottom: 32px; }
.logo-text { font-size: 2rem; font-weight: 800; color: #10B981; letter-spacing: -1px; }
.logo-sub { font-size: 0.82rem; color: #6B7280; margin-top: 4px; }
</style>
