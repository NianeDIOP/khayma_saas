<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
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

// ── Detect user role in current company ──
const userRole = computed(() => {
    const u = user.value
    if (!u) return null
    if (u.is_super_admin) return 'super_admin'
    // Role comes from the pivot — shared via HandleInertiaRequests
    return u.company_role || 'owner'
})

// ── Detect active modules ──
const activeModules = computed(() => page.props.activeModules || [])
function hasModule(code) {
    return activeModules.value.some(m => m.code === code)
}

// ── Permission checks per role ──
function can(section) {
    const r = userRole.value
    if (!r) return false
    if (r === 'super_admin' || r === 'owner') return true

    const map = {
        dashboard:    ['manager', 'caissier', 'magasinier'],
        onboarding:   [],
        settings:     [],
        customers:    ['manager', 'caissier'],
        suppliers:    ['manager', 'magasinier'],
        products:     ['manager', 'magasinier'],
        categories:   ['manager', 'magasinier'],
        units:        ['manager', 'magasinier'],
        depots:       ['manager', 'magasinier'],
        stock:        ['manager', 'magasinier'],
        sales:        ['manager', 'caissier'],
        expenses:     ['manager'],
        // Restaurant
        'restaurant.pos':       ['manager', 'caissier'],
        'restaurant.orders':    ['manager', 'caissier'],
        'restaurant.dishes':    ['manager'],
        'restaurant.categories':['manager'],
        'restaurant.services':  ['manager'],
        'restaurant.cash':      ['manager', 'caissier'],
        'restaurant.reports':   ['manager'],
        // Quincaillerie
        'quinc.quotes':           ['manager'],
        'quinc.purchase-orders':  ['manager'],
        'quinc.supplier-payments':['manager'],
        'quinc.supplier-returns': ['manager'],
        'quinc.credits':          ['manager', 'caissier'],
        'quinc.inventories':      ['manager', 'magasinier'],
        'quinc.reports':          ['manager'],
        // Boutique
        'boutique.pos':        ['manager', 'caissier'],
        'boutique.variants':   ['manager'],
        'boutique.promotions': ['manager'],
        'boutique.loyalty':    ['manager'],
        'boutique.transfers':  ['manager', 'magasinier'],
        'boutique.reports':    ['manager'],
        // Location
        'location.assets':    ['manager'],
        'location.contracts': ['manager'],
        'location.payments':  ['manager', 'caissier'],
        'location.calendar':  ['manager'],
        'location.reports':   ['manager'],
        team:                 [],
    }
    return (map[section] || []).includes(r)
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
        <i class="fa-solid fa-user-circle" style="color:#6366F1;"></i>
        {{ $page.props.auth?.user?.name || 'Utilisateur' }}
      </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="app-sidebar" v-show="sidebarOpen">
      <nav ref="sidebarNavRef" class="sidebar-nav">
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

        <!-- Gestion -->
        <template v-if="can('customers') || can('suppliers')">
          <div class="nav-divider"></div>
          <div class="nav-section-label">Gestion</div>
          <Link v-if="can('customers')" :href="route('app.customers.index', { _tenant: company?.slug })"
            :class="['nav-item', { active: $page.url.includes('/customers') }]">
            <i class="fa-solid fa-user-group" style="color:#2563EB"></i> <span>Clients</span>
          </Link>
          <Link v-if="can('suppliers')" :href="route('app.suppliers.index', { _tenant: company?.slug })"
            :class="['nav-item', { active: $page.url.includes('/suppliers') }]">
            <i class="fa-solid fa-truck-field" style="color:#F59E0B"></i> <span>Fournisseurs</span>
          </Link>
        </template>

        <!-- Catalogue -->
        <template v-if="can('products') || can('categories') || can('units') || can('depots')">
          <div class="nav-divider"></div>
          <div class="nav-section-label">Catalogue</div>
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
        </template>

        <!-- Commerce -->
        <template v-if="can('stock') || can('sales') || can('expenses')">
          <div class="nav-divider"></div>
          <div class="nav-section-label">Commerce</div>
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
        </template>

        <!-- Restaurant -->
        <template v-if="hasModule('restaurant') && (can('restaurant.pos') || can('restaurant.orders'))">
          <div class="nav-divider"></div>
          <div class="nav-section-label">Restaurant</div>
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
        </template>

        <!-- Quincaillerie -->
        <template v-if="hasModule('quincaillerie') && (can('quinc.quotes') || can('quinc.inventories'))">
          <div class="nav-divider"></div>
          <div class="nav-section-label">Quincaillerie</div>
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
        </template>

        <!-- Boutique -->
        <template v-if="hasModule('boutique') && (can('boutique.pos') || can('boutique.transfers'))">
          <div class="nav-divider"></div>
          <div class="nav-section-label">Boutique</div>
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
        </template>

        <!-- Location -->
        <template v-if="hasModule('location') && (can('location.assets') || can('location.payments'))">
          <div class="nav-divider"></div>
          <div class="nav-section-label">Location</div>
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
        </template>
      </nav>

      <div class="sidebar-footer">
        <Link :href="route('app.dashboard', { _tenant: company?.slug })" class="nav-item user-info">
          <i class="fa-solid fa-user-circle" style="color:#6366F1"></i>
          <span>{{ user?.name || 'Profil' }}</span>
          <small class="role-badge">{{ userRole }}</small>
        </Link>
        <a href="/" class="nav-item">
          <i class="fa-solid fa-arrow-left" style="color:#6B7280"></i> <span>Retour au site</span>
        </a>
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

.topbar-right { font-size: 0.82rem; color: #374151; display: flex; align-items: center; gap: 8px; }

/* ── Sidebar ─── */
.app-sidebar {
  width: 220px; position: fixed; top: 60px; left: 0; bottom: 0; z-index: 10;
  background: #FFFFFF; border-right: 1px solid #E5E7EB;
  display: flex; flex-direction: column; transition: transform 0.2s ease;
  overflow: hidden;
}
.sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; min-height: 0; }
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 12px 16px;
  color: #374151; font-size: 0.88rem; font-weight: 600; text-decoration: none;
  transition: background 0.15s, color 0.15s; cursor: pointer;
  border: none; background: none; width: 100%; text-align: left;
  letter-spacing: -0.01em;
}
.nav-item:hover { background: #F3F4F6; color: #111827; }
.nav-item.active { background: #EEF2FF; color: #4F46E5; border-right: 3px solid #4F46E5; }
.nav-item i { width: 20px; text-align: center; font-size: 1.05rem; flex-shrink: 0; }
.sidebar-footer { padding: 12px 0; border-top: 1px solid #E5E7EB; }
.nav-divider { height: 1px; background: #E5E7EB; margin: 8px 16px; }
.nav-section-label { font-size: 0.68rem; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.06em; padding: 4px 16px 6px; }
.nav-item.logout:hover { color: #EF4444; }
.user-info { flex-wrap: wrap; }
.role-badge {
  display: inline-block; font-size: 0.6rem; font-weight: 700; padding: 1px 6px;
  border-radius: 3px; background: #EEF2FF; color: #4F46E5; text-transform: uppercase;
  letter-spacing: 0.04em; margin-left: auto;
}

/* ── Content ─── */
.app-content {
  margin-left: 220px; margin-top: 60px; padding: 24px;
  min-height: calc(100vh - 60px); transition: margin-left 0.2s ease;
}
.sidebar-collapsed .app-content { margin-left: 0; }
</style>
