import { createRouter, createWebHistory } from 'vue-router'
import { authGuard, gameGuard } from './guards'

// Import route definitions
import authRoutes from './routes/auth'
import gameRoutes from './routes/game'
import generalRoutes from './routes/general'

const routes = [
    // Redirect root to welcome or dashboard based on auth
    {
        path: '/',
        redirect: to => {
            const token = localStorage.getItem('auth-token')
            return token ? '/game' : '/welcome'
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
            title: 'Pagina non trovata'
        }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        // Scroll to top on route change, unless it's a saved position (back button)
        if (savedPosition) {
            return savedPosition
        } else {
            return { top: 0 }
        }
    }
})

// Apply global navigation guards
router.beforeEach(authGuard)
router.beforeEach(gameGuard)

// Add page title and meta tags
router.beforeEach((to, from, next) => {
    // Set page title
    if (to.meta.title) {
        document.title = `${to.meta.title} - Tech Company Game`
    } else {
        document.title = 'Tech Company Game'
    }

    next()
})

// Global after hooks for analytics, etc.
router.afterEach((to, from) => {
    // Here you could add analytics tracking
    console.log(`Navigated from ${from.name} to ${to.name}`)
})

export default router