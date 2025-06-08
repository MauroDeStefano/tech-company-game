/**
 * 🔧 Fixed Router Guards - Compatibili con Vue 3 + Vite
 * 
 * Fix per il problema "require is not defined" 
 * Usa import dinamici invece di require()
 */

/**
 * ✅ Safe store access con import dinamici
 */
async function getSafeAuthStore() {
    try {
        // ✅ Import dinamico invece di require
        const { useAuthStore } = await import('@/js/stores/auth')
        return useAuthStore()
    } catch (error) {
        console.warn('Could not access auth store:', error)
        return {
            isAuthenticated: false,
            user: null,
            checkAuth: () => Promise.resolve(false)
        }
    }
}

async function getSafeGameStore() {
    try {
        // ✅ Import dinamico invece di require  
        const { useGameStore } = await import('@/js/stores/game')
        return useGameStore()
    } catch (error) {
        console.warn('Could not access game store:', error)
        return {
            currentGame: null,
            loading: false
        }
    }
}

/**
 * 🔄 Redirect Guard - Previene loop infiniti
 */
export const redirectGuard = async (to, from) => {
    // Previeni navigazione alla stessa route
    if (to.name === from.name && to.path === from.path) {
        console.log('🔄 Preventing same route navigation')
        return false
    }

    // ✅ SMART REDIRECT per root
    if (to.meta?.smartRedirect && to.name === 'Root') {
        const authStore = await getSafeAuthStore()
        
        console.log('🎯 Smart redirect from root:', {
            isAuthenticated: authStore.isAuthenticated,
            hasUser: !!authStore.user
        })

        // Se autenticato → Lista giochi
        if (authStore.isAuthenticated) {
            console.log('✅ Authenticated user → Games list')
            return { name: 'GameList' }
        } 
        // Se non autenticato → Welcome page
        else {
            console.log('🔐 Guest user → Welcome page')
            return { name: 'Welcome' }
        }
    }

    return true
}

/**
 * 🔒 Auth Guard - Gestione autenticazione
 */
export const authGuard = async (to, from) => {
    // Skip durante navigazione iniziale per evitare loop
    if (!from.name && to.name === 'Root') {
        return true
    }

    // ✅ ASPETTA che lo store sia disponibile
    const authStore = await getSafeAuthStore()

    // Route che richiedono autenticazione
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
    
    // Route specifiche per guest (login, register, etc.)
    const isAuthPage = to.matched.some(record => record.meta.isAuthPage)

    console.log(`🔒 Auth Guard: ${from.name || 'initial'} → ${to.name}`, {
        requiresAuth,
        isAuthPage,
        isAuthenticated: authStore.isAuthenticated,
        hasUser: !!authStore.user
    })

    // ⚠️ CASO SPECIALE: Route che richiede auth ma utente non autenticato
    if (requiresAuth && !authStore.isAuthenticated) {
        console.log('🚫 Access denied: authentication required')
        
        // Se già siamo nel login, non redirectare di nuovo
        if (to.name === 'Login') {
            return true
        }
        
        // ✅ Redirect al login con query parameter per tornare dopo
        return {
            name: 'Login',
            query: to.fullPath !== '/' ? { redirect: to.fullPath } : {}
        }
    }

    // ⚠️ CASO SPECIALE: Utente autenticato cerca di accedere a pagine di auth
    if (authStore.isAuthenticated && isAuthPage) {
        console.log('🔄 Already authenticated → Games list')
        
        // Evita loop se già siamo nella lista giochi
        if (to.name === 'GameList') {
            return true
        }
        
        return { name: 'GameList' }
    }

    // ✅ Default: permetti navigazione
    return true
}

/**
 * 🎮 Game Guard - Gestione stato gioco
 */
export const gameGuard = async (to, from) => {
    // Skip durante navigazione iniziale
    if (!from.name) {
        return true
    }

    // ✅ ASPETTA che lo store sia disponibile
    const gameStore = await getSafeGameStore()

    // Route che richiedono un gioco attivo
    const requiresGame = to.matched.some(record => record.meta.requiresGame)

    console.log(`🎮 Game Guard: ${from.name || 'initial'} → ${to.name}`, {
        requiresGame,
        hasCurrentGame: !!gameStore.currentGame,
        gameStatus: gameStore.currentGame?.status || 'none'
    })

    if (requiresGame) {
        // ⚠️ Nessun gioco attivo
        if (!gameStore.currentGame) {
            console.log('🎮 No active game → Game list')
            
            // Evita loop se già siamo nella lista
            if (to.name === 'GameList') {
                return true
            }
            
            return { name: 'GameList' }
        }

        // ⚠️ Gioco terminato - solo dashboard permessa
        if (gameStore.currentGame.status === 'game_over' && 
            to.name !== 'GameDashboard') {
            
            console.log('💀 Game over → Dashboard only')
            
            // Evita loop se già siamo nella dashboard
            if (from.name === 'GameDashboard') {
                return true
            }
            
            return { name: 'GameDashboard' }
        }
    }

    return true
}

// ✅ ALTERNATIVA SEMPLIFICATA se hai ancora problemi
export const simpleAuthGuard = async (to, from) => {
    console.log(`🔒 Simple Auth Guard: ${from.name || 'initial'} → ${to.name}`)

    // Route che richiedono autenticazione
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
    
    if (!requiresAuth) {
        console.log('✅ Public route, allowing access')
        return true
    }

    try {
        // Tenta di accedere al store
        const { useAuthStore } = await import('@/js/stores/auth')
        const authStore = useAuthStore()
        
        if (authStore.isAuthenticated) {
            console.log('✅ User is authenticated, allowing access')
            return true
        } else {
            console.log('🚫 User not authenticated, redirecting to login')
            return { name: 'Login', query: { redirect: to.fullPath } }
        }
    } catch (error) {
        console.error('❌ Auth guard error:', error)
        // In caso di errore, redirect al login per sicurezza
        return { name: 'Login' }
    }
}

/**
 * 🔧 Utility: Verifica se route è protetta
 */
export const isProtectedRoute = (route) => {
    return route.matched.some(record => record.meta.requiresAuth)
}

/**
 * 🔧 Utility: Verifica se route è per guest
 */
export const isGuestRoute = (route) => {
    return route.matched.some(record => record.meta.isAuthPage)
}

/**
 * 🔧 Utility: Ottieni redirect URL dopo login
 */
export const getLoginRedirectUrl = (route) => {
    const redirectQuery = route.query.redirect
    if (redirectQuery && redirectQuery !== '/') {
        return redirectQuery
    }
    return '/games' // Default per utenti autenticati
}