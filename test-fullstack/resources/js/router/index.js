import { createRouter, createWebHashHistory } from 'vue-router'

// Import route definitions
import authRoutes from './auth'
import gameRoutes from './game'
import generalRoutes from './general'

// ✅ Import guards (li attiviamo!)
import { authGuard, gameGuard, redirectGuard } from './guards'

const routes = [
    // ✅ Root redirect INTELLIGENTE
    {
        path: '/',
        name: 'Root',
        // ⚠️ NON redirect statico! Il guard deciderà dove andare
        component: () => import('@/views/general/LoadingView.vue'),
        meta: {
            title: 'Tech Company Game',
            smartRedirect: true // Flag personalizzato per il guard
        }
    },

    // Authentication routes
    ...authRoutes,

    // Game routes  
    ...gameRoutes,

    // General routes
    ...generalRoutes,

    // Catch-all 404 route (must be last!)
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/views/general/NotFoundView.vue'),
        meta: {
            title: 'Pagina non trovata',
            layout: 'LayoutDefault'
        }
    }
]

const router = createRouter({
    // ✅ Hash mode - nessun problema con il server!
    history: createWebHashHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        } else {
            return { top: 0 }
        }
    }
})

// ✅ GUARDS ATTIVATI nell'ordine corretto
// 1. Redirect guard (previene loop infiniti)
router.beforeEach(redirectGuard)

// 2. Auth guard (gestisce autenticazione)
router.beforeEach(authGuard)

// 3. Game guard (gestisce stato gioco)
router.beforeEach(gameGuard)

// 4. Logging e title setup
router.beforeEach((to, from) => {
    console.log(`🚀 Navigation: ${from.name || 'initial'} → ${to.name}`)
    
    // Set page title
    if (to.meta.title) {
        document.title = `${to.meta.title} - Tech Company Game`
    } else {
        document.title = 'Tech Company Game'
    }
    
    return true
})

router.afterEach((to, from, failure) => {
    if (failure) {
        console.error('❌ Navigation failed:', failure)
    } else {
        console.log(`✅ Navigation completed: ${to.name}`)
    }
})

export default router