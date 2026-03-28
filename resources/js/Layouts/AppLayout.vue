<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

defineProps({ title: { type: String, default: 'Espace entreprise' } })

const page = usePage()
const company = computed(() => page.props.currentCompany)
const user    = computed(() => page.props.auth?.user)
const sidebarOpen = ref(true)
const sidebarNavRef = ref(null)

function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }

// ── Preserve sidebar scroll position across Inertia navigations ──
function saveSidebarScroll() {
    if (sidebarNavRef.value) {
        sessionStorage.setItem('sidebar-scroll', sidebarNavRef.value.scrollTop)
    }
}
function restoreSidebarScroll() {
    nextTick(() => {
        const saved = sessionStorage.getItem('sidebar-scroll')
        if (sidebarNavRef.value && saved) {
            sidebarNavRef.value.scrollTop = parseInt(saved, 10)
        }
    })
}
onMounted(restoreSidebarScroll)
router.on('before', saveSidebarScroll)
router.on('navigate', restoreSidebarScroll)

// ── Smart back navigation for all .btn-back buttons ──
// Intercepts clicks to use browser history instead of hardcoded routes,
// avoiding redirects to pages the user may not have permission for.
function handleBackClick(e) {
    const backBtn = e.target.closest('.btn-back')
    if (backBtn) {
        e.preventDefault()
        e.stopPropagation()
        if (window.history.length > 1) {
            window.history.back()
        } else {
            router.visit(route('app.dashboard', { _tenant: company.value?.slug }))
        }
    }
}
onMounted(() => document.addEventListener('click', handleBackClick, true))
onUnmounted(() => document.removeEventListener('click', handleBackClick, true))

// ── Collapsible sections ──
function loadSections() {
    try { return JSON.parse(sessionStorage.getItem('sidebar-sections') || '{}') } catch { return {} }
}
function saveSections() {
    sessionStorage.setItem('sidebar-sections', JSON.stringify(openSections.value))
}
const openSections = ref(loadSections())
function toggleSection(key) {
    openSections.value[key] = !openSections.value[key]
    saveSections()
}
function isOpen(key) {
    return openSections.value[key] !== false // open by default
}

// ── Detect user role in current company ──
const userRole = computed(() => {
    const u = user.value
    if (!u) return null
    if (u.is_super_admin) return 'super_admin'
    return u.company_role || null
})

// ── Detect active modules ──
const activeModules = computed(() => page.props.activeModules || [])
function hasModule(code) {
    return activeModules.value.some(m => m.code === code)
}

// ── Permission checks ──
// Owner & super_admin see everything.
// Other roles: check explicit permissions array from pivot.
const userPermissions = computed(() => user.value?.company_permissions || null)

function can(section) {
    const r = userRole.value
    if (!r) return false
    if (r === 'super_admin' || r === 'owner') return true
    // Use explicit permissions assigned by owner
    if (Array.isArray(userPermissions.value)) return userPermissions.value.includes(section)
    // No permissions set yet → no access
    return false
}
</script>

<template>
  <div class="app-shell" :class="{ 'sidebar-collapsed': !sidebarOpen }">
    <!-- TOPBAR -->
    <header class="app-topbar">
      <div class="topbar-left">
        <button class="hamburger" @click="toggleSidebar" title="Menu">
          <i :class="sidebarOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'"></i>
        </button>
        <a :href="route('app.dashboard', { _tenant: company?.slug })" class="topbar-logo-link">
          <img src="/khayma_logo_transparent.png" alt="Khayma" class="topbar-logo" />
          <span class="topbar-tagline">Tenter<br>&amp; Estimer</span>
        </a>
      </div>
      <div class="topbar-center" v-if="company">
        <span class="company-name">{{ company.name }}</span>
        <span class="company-badge" :class="'badge-' + company.subscription_status">
          {{ company.subscription_status }}
        </span>
      </div>
      <div class="topbar-right">
        <span class="topbar-user">
          <i class="fa-solid fa-user-circle" style="color:#6366F1;"></i>
          {{ user?.name || 'Utilisateur' }}
          <small class="role-tag">{{ userRole }}</small>
        </span>
        <a href="/" class="topbar-home" title="Retour au site">
          <i class="fa-solid fa-house"></i>
        </a>
      </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="app-sidebar" v-show="sidebarOpen">
      <nav ref="sidebarNavRef" class="sidebar-nav">
        <!-- Top-level links -->
        <Link v-if="can('dashboard')" :href="route('app.dashboard', { _tenant: company?.slug })"
          :class="['nav-item', { active: $page.url.endsWith('/app') || $page.url.endsWith('/app/') || $page.url.match(/\/app\?/) }]">
          <i class="fa-solid fa-gauge-high" style="color:#10B981"></i> <span>Dashboard</span>
        </Link>
        <Link v-if="can('onboarding')" :href="route('app.onboarding', { _tenant: company?.slug })"
          :class="['nav-item', { active: $page.url.includes('/onboarding') }]">
          <i class="fa-solid fa-clipboard-check" style="color:#F59E0B"></i> <span>Onboarding</span>
        </Link>
        <Link v-if="can('settings')" :href="route('app.settings', { _tenant: company?.slug })"
          :class="['nav-item', { active: $page.url.includes('/settings') }]">
          <i class="fa-solid fa-gear" style="color:#8B5CF6"></i> <span>Paramètres</span>
        </Link>
        <Link v-if="can('team')" :href="route('app.team.index', { _tenant: company?.slug })"
          :class="['nav-item', { active: $page.url.includes('/team') }]">
          <i class="fa-solid fa-users" style="color:#6366F1"></i> <span>Équipe</span>
        </Link>

        <!-- ── Gestion ── -->
        <template v-if="can('customers') || can('suppliers')">
          <button class="section-toggle" @click="toggleSection('gestion')">
            <span class="section-label">Gestion</span>
            <i :class="isOpen('gestion') ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="section-arrow"></i>
          </button>
          <div v-show="isOpen('gestion')" class="section-links">
            <Link v-if="can('customers')" :href="route('app.customers.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/customers') }]">
              <i class="fa-solid fa-user-group" style="color:#2563EB"></i> <span>Clients</span>
            </Link>
            <Link v-if="can('suppliers')" :href="route('app.suppliers.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/suppliers') }]">
              <i class="fa-solid fa-truck-field" style="color:#F59E0B"></i> <span>Fournisseurs</span>
            </Link>
          </div>
        </template>

        <!-- ── Catalogue ── -->
        <template v-if="can('products') || can('categories') || can('units') || can('depots')">
          <button class="section-toggle" @click="toggleSection('catalogue')">
            <span class="section-label">Catalogue</span>
            <i :class="isOpen('catalogue') ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="section-arrow"></i>
          </button>
          <div v-show="isOpen('catalogue')" class="section-links">
            <Link v-if="can('products')" :href="route('app.products.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/products') }]">
              <i class="fa-solid fa-box" style="color:#10B981"></i> <span>Produits</span>
            </Link>
            <Link v-if="can('categories')" :href="route('app.categories.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/categories') }]">
              <i class="fa-solid fa-folder-tree" style="color:#8B5CF6"></i> <span>Catégories</span>
            </Link>
            <Link v-if="can('units')" :href="route('app.units.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/units') }]">
              <i class="fa-solid fa-ruler" style="color:#0891B2"></i> <span>Unités</span>
            </Link>
            <Link v-if="can('depots')" :href="route('app.depots.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/depots') }]">
              <i class="fa-solid fa-warehouse" style="color:#6366F1"></i> <span>Dépôts</span>
            </Link>
          </div>
        </template>

        <!-- ── Commerce ── -->
        <template v-if="can('stock') || can('sales') || can('expenses')">
          <button class="section-toggle" @click="toggleSection('commerce')">
            <span class="section-label">Commerce</span>
            <i :class="isOpen('commerce') ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="section-arrow"></i>
          </button>
          <div v-show="isOpen('commerce')" class="section-links">
            <Link v-if="can('stock')" :href="route('app.stock.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/stock') }]">
              <i class="fa-solid fa-boxes-stacked" style="color:#EF4444"></i> <span>Stock</span>
            </Link>
            <Link v-if="can('sales')" :href="route('app.sales.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/sales') }]">
              <i class="fa-solid fa-cash-register" style="color:#F59E0B"></i> <span>Ventes</span>
            </Link>
            <Link v-if="can('expenses')" :href="route('app.expenses.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/expenses') && !$page.url.includes('/expense-categories') }]">
              <i class="fa-solid fa-money-bill-trend-up" style="color:#DC2626"></i> <span>Dépenses</span>
            </Link>
          </div>
        </template>

        <!-- ── Restaurant ── -->
        <template v-if="hasModule('restaurant') && (can('restaurant.pos') || can('restaurant.orders'))">
          <button class="section-toggle" @click="toggleSection('restaurant')">
            <span class="section-label">Restaurant</span>
            <i :class="isOpen('restaurant') ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="section-arrow"></i>
          </button>
          <div v-show="isOpen('restaurant')" class="section-links">
            <Link v-if="can('restaurant.pos')" :href="route('app.restaurant.orders.create', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/restaurant/orders/create') }]">
              <i class="fa-solid fa-utensils" style="color:#EF4444"></i> <span>POS Commandes</span>
            </Link>
            <Link v-if="can('restaurant.orders')" :href="route('app.restaurant.orders.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/restaurant/orders') && !$page.url.includes('/orders/create') }]">
              <i class="fa-solid fa-clipboard-list" style="color:#F97316"></i> <span>Commandes</span>
            </Link>
            <Link v-if="can('restaurant.dishes')" :href="route('app.restaurant.dishes.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/restaurant/dishes') }]">
              <i class="fa-solid fa-bowl-food" style="color:#F59E0B"></i> <span>Plats</span>
            </Link>
            <Link v-if="can('restaurant.categories')" :href="route('app.restaurant.categories.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/restaurant/categories') }]">
              <i class="fa-solid fa-layer-group" style="color:#10B981"></i> <span>Catégories Menu</span>
            </Link>
            <Link v-if="can('restaurant.services')" :href="route('app.restaurant.services.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/restaurant/services') }]">
              <i class="fa-solid fa-clock" style="color:#3B82F6"></i> <span>Services</span>
            </Link>
            <Link v-if="can('restaurant.cash')" :href="route('app.restaurant.cash-sessions.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/restaurant/cash-sessions') }]">
              <i class="fa-solid fa-cash-register" style="color:#8B5CF6"></i> <span>Caisse</span>
            </Link>
            <Link v-if="can('restaurant.reports')" :href="route('app.restaurant.reports.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/restaurant/reports') }]">
              <i class="fa-solid fa-chart-bar" style="color:#EC4899"></i> <span>Rapports</span>
            </Link>
          </div>
        </template>

        <!-- ── Quincaillerie ── -->
        <template v-if="hasModule('quincaillerie') && (can('quinc.pos') || can('quinc.quotes') || can('quinc.inventories'))">
          <button class="section-toggle" @click="toggleSection('quincaillerie')">
            <span class="section-label">Quincaillerie</span>
            <i :class="isOpen('quincaillerie') ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="section-arrow"></i>
          </button>
          <div v-show="isOpen('quincaillerie')" class="section-links">
            <Link v-if="can('quinc.pos')" :href="route('app.quincaillerie.pos.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/pos') }]">
              <i class="fa-solid fa-cash-register" style="color:#10B981"></i> <span>Caisse POS</span>
            </Link>
            <Link v-if="can('quinc.quotes')" :href="route('app.quincaillerie.quotes.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/quotes') }]">
              <i class="fa-solid fa-file-invoice" style="color:#6366F1"></i> <span>Devis</span>
            </Link>
            <Link v-if="can('quinc.purchase-orders')" :href="route('app.quincaillerie.purchase-orders.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/purchase-orders') }]">
              <i class="fa-solid fa-truck" style="color:#0EA5E9"></i> <span>Bons de commande</span>
            </Link>
            <Link v-if="can('quinc.supplier-payments')" :href="route('app.quincaillerie.supplier-payments.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/supplier-payments') }]">
              <i class="fa-solid fa-money-check-dollar" style="color:#10B981"></i> <span>Paiements fourn.</span>
            </Link>
            <Link v-if="can('quinc.supplier-returns')" :href="route('app.quincaillerie.supplier-returns.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/supplier-returns') }]">
              <i class="fa-solid fa-rotate-left" style="color:#EF4444"></i> <span>Retours fourn.</span>
            </Link>
            <Link v-if="can('quinc.credits')" :href="route('app.quincaillerie.credits.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/credits') }]">
              <i class="fa-solid fa-hand-holding-dollar" style="color:#F59E0B"></i> <span>Crédits clients</span>
            </Link>
            <Link v-if="can('quinc.inventories')" :href="route('app.quincaillerie.inventories.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/inventories') }]">
              <i class="fa-solid fa-clipboard-check" style="color:#8B5CF6"></i> <span>Inventaires</span>
            </Link>
            <Link v-if="can('quinc.reports')" :href="route('app.quincaillerie.reports.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/quincaillerie/reports') }]">
              <i class="fa-solid fa-chart-bar" style="color:#EC4899"></i> <span>Rapports Quinc.</span>
            </Link>
          </div>
        </template>

        <!-- ── Boutique ── -->
        <template v-if="hasModule('boutique') && (can('boutique.pos') || can('boutique.transfers'))">
          <button class="section-toggle" @click="toggleSection('boutique')">
            <span class="section-label">Boutique</span>
            <i :class="isOpen('boutique') ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="section-arrow"></i>
          </button>
          <div v-show="isOpen('boutique')" class="section-links">
            <Link v-if="can('boutique.pos')" :href="route('app.boutique.pos.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/boutique/pos') }]">
              <i class="fa-solid fa-cash-register" style="color:#10B981"></i> <span>Caisse POS</span>
            </Link>
            <Link v-if="can('boutique.variants')" :href="route('app.boutique.variants.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/boutique/variants') }]">
              <i class="fa-solid fa-swatchbook" style="color:#6366F1"></i> <span>Variantes</span>
            </Link>
            <Link v-if="can('boutique.promotions')" :href="route('app.boutique.promotions.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/boutique/promotions') }]">
              <i class="fa-solid fa-tags" style="color:#F59E0B"></i> <span>Promotions</span>
            </Link>
            <Link v-if="can('boutique.loyalty')" :href="route('app.boutique.loyalty.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/boutique/loyalty') }]">
              <i class="fa-solid fa-heart" style="color:#EC4899"></i> <span>Fidélité</span>
            </Link>
            <Link v-if="can('boutique.transfers')" :href="route('app.boutique.transfers.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/boutique/transfers') }]">
              <i class="fa-solid fa-right-left" style="color:#0EA5E9"></i> <span>Transferts</span>
            </Link>
            <Link v-if="can('boutique.reports')" :href="route('app.boutique.reports.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/boutique/reports') }]">
              <i class="fa-solid fa-chart-pie" style="color:#8B5CF6"></i> <span>Rapports Boutique</span>
            </Link>
          </div>
        </template>

        <!-- ── Location ── -->
        <template v-if="hasModule('location') && (can('location.assets') || can('location.payments'))">
          <button class="section-toggle" @click="toggleSection('location')">
            <span class="section-label">Location</span>
            <i :class="isOpen('location') ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'" class="section-arrow"></i>
          </button>
          <div v-show="isOpen('location')" class="section-links">
            <Link v-if="can('location.assets')" :href="route('app.location.assets.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/location/assets') }]">
              <i class="fa-solid fa-building" style="color:#0EA5E9"></i> <span>Biens</span>
            </Link>
            <Link v-if="can('location.contracts')" :href="route('app.location.contracts.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/location/contracts') }]">
              <i class="fa-solid fa-file-contract" style="color:#10B981"></i> <span>Contrats</span>
            </Link>
            <Link v-if="can('location.payments')" :href="route('app.location.payments.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/location/payments') }]">
              <i class="fa-solid fa-money-bill-wave" style="color:#F59E0B"></i> <span>Paiements</span>
            </Link>
            <Link v-if="can('location.calendar')" :href="route('app.location.calendar.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/location/calendar') }]">
              <i class="fa-solid fa-calendar-days" style="color:#8B5CF6"></i> <span>Calendrier</span>
            </Link>
            <Link v-if="can('location.reports')" :href="route('app.location.reports.index', { _tenant: company?.slug })"
              :class="['nav-item', { active: $page.url.includes('/location/reports') }]">
              <i class="fa-solid fa-chart-line" style="color:#EC4899"></i> <span>Rapports Location</span>
            </Link>
          </div>
        </template>
      </nav>

      <div class="sidebar-footer">
        <Link :href="route('logout')" method="post" as="button" class="nav-item logout">
          <i class="fa-solid fa-right-from-bracket" style="color:#EF4444"></i> <span>Déconnexion</span>
        </Link>
      </div>
    </aside>

    <!-- CONTENT -->
    <main class="app-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.app-shell { min-height: 100vh; background: #F9FAFB; font-family: 'Inter', sans-serif; }

/* ── Topbar ─── */
.app-topbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 20;
  height: 60px; background: #FFFFFF; padding: 0 24px;
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid #E5E7EB;
}
.topbar-left { display: flex; align-items: center; gap: 14px; }
.hamburger {
  background: none; border: none; color: #6B7280; font-size: 1.2rem;
  cursor: pointer; padding: 6px 8px; transition: color 0.15s;
}
.hamburger:hover { color: #111827; }
.topbar-logo-link { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; }
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

.topbar-center { display: flex; align-items: center; gap: 10px; }
.company-name { font-size: 0.88rem; font-weight: 700; color: #111827; }
.company-badge {
  font-size: 0.65rem; font-weight: 600; padding: 2px 8px; border-radius: 4px;
  text-transform: uppercase; letter-spacing: 0.04em;
}
.badge-trial { background: #FEF3C7; color: #92400E; }
.badge-active { background: #D1FAE5; color: #065F46; }
.badge-expired, .badge-suspended, .badge-cancelled { background: #FEE2E2; color: #991B1B; }
.badge-grace_period { background: #FEF3C7; color: #92400E; }

.topbar-right { display: flex; align-items: center; gap: 14px; }
.topbar-user { font-size: 0.82rem; color: #374151; display: flex; align-items: center; gap: 8px; }
.role-tag {
  font-size: 0.6rem; font-weight: 700; padding: 1px 6px; border-radius: 3px;
  background: #EEF2FF; color: #4F46E5; text-transform: uppercase; letter-spacing: 0.04em;
}
.topbar-home {
  display: flex; align-items: center; justify-content: center;
  width: 34px; height: 34px; border-radius: 50%; color: #6B7280;
  text-decoration: none; transition: all 0.15s; font-size: 0.9rem;
}
.topbar-home:hover { background: #F3F4F6; color: #111827; }

/* ── Sidebar ─── */
.app-sidebar {
  width: 220px; position: fixed; top: 60px; left: 0; bottom: 0; z-index: 10;
  background: #FFFFFF; border-right: 1px solid #E5E7EB;
  display: flex; flex-direction: column; transition: transform 0.2s ease;
  overflow: hidden;
}
.sidebar-nav { flex: 1; padding: 8px 0; overflow-y: auto; min-height: 0; }
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 10px 16px;
  color: #374151; font-size: 0.82rem; font-weight: 600; text-decoration: none;
  transition: background 0.15s, color 0.15s; cursor: pointer;
  border: none; background: none; width: 100%; text-align: left;
  letter-spacing: -0.01em;
}
.nav-item:hover { background: #F3F4F6; color: #111827; }
.nav-item.active { background: #EEF2FF; color: #4F46E5; border-right: 3px solid #4F46E5; }
.nav-item i { width: 18px; text-align: center; font-size: 0.95rem; flex-shrink: 0; }

/* ── Collapsible sections ─── */
.section-toggle {
  display: flex; align-items: center; justify-content: space-between;
  width: 100%; padding: 10px 16px; margin-top: 10px;
  background: none; border: none; border-top: 1px solid #E5E7EB;
  cursor: pointer; transition: background 0.15s;
}
.section-toggle:hover { background: #F3F4F6; }
.section-label {
  font-size: 0.75rem; font-weight: 800; color: #4B5563;
  text-transform: uppercase; letter-spacing: 0.08em;
}
.section-arrow { font-size: 0.6rem; color: #6B7280; transition: transform 0.15s; }
.section-links { /* no extra styling needed, just a wrapper */ }

.sidebar-footer { padding: 8px 0; border-top: 1px solid #E5E7EB; }
.nav-item.logout:hover { color: #EF4444; }

/* ── Content ─── */
.app-content {
  margin-left: 220px; margin-top: 60px; padding: 24px;
  min-height: calc(100vh - 60px); transition: margin-left 0.2s ease;
}
.sidebar-collapsed .app-content { margin-left: 0; }
</style>
