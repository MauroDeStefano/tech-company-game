import { useAuthStore } from '@/stores/auth'
import { useGameStore } from '@/stores/game'

/**
 * Authentication Guard
 * Protegge le route che richiedono autenticazione
 */
export const authGuard = async (to, from) => {
    const authStore = useAuthStore()

    // Routes che richiedono autenticazione
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

    // Routes che sono specifiche per utenti non autenticati
    const isAuthPage = to.matched.some(record => record.meta.isAuthPage)

    // Se la route richiede autenticazione ma l'utente non è autenticato
    if (requiresAuth && !authStore.isAuthenticated) {
        console.log('🔒 Access denied: authentication required')
        return {
            name: 'Login',
            query: { redirect: to.fullPath }
        }
    }

    // Se l'utente è autenticato e cerca di accedere a pagine di auth (login, register)
    if (authStore.isAuthenticated && isAuthPage) {
        console.log('🔄 Already authenticated, redirecting to game')
        return { name: 'GameDashboard' }
    }

    // Permetti la navigazione
    return true
}

/**
 * Game Guard
 * Protegge le route che richiedono un gioco attivo
 */
export const gameGuard = async (to, from) => {
    const gameStore = useGameStore()

    // Routes che richiedono un gioco attivo
    const requiresGame = to.matched.some(record => record.meta.requiresGame)

    if (requiresGame && !gameStore.currentGame) {
        console.log('🎮 No active game, redirecting to game list')
        return { name: 'GameList' }
    }

    return true
}

/**
 * Game Over Guard
 * Previene azioni su giochi terminati
 */
export const gameOverGuard = async (to, from) => {
    const gameStore = useGameStore()

    const currentGame = gameStore.currentGame

    if (currentGame && currentGame.status === 'game_over') {
        // Permetti solo visualizzazione, non azioni
        const allowedOnGameOver = ['GameDashboard', 'GameList']

        if (!allowedOnGameOver.includes(to.name)) {
            console.log('💀 Game over, redirecting to dashboard')
            return { name: 'GameDashboard' }
        }
    }

    return true
}

/**
 * Permission Guard
 * Controlla permessi specifici dell'utente
 */
export const permissionGuard = (permission) => {
    return async (to, from) => {
        const authStore = useAuthStore()

        if (!authStore.hasPermission(permission)) {
            console.log(`🚫 Permission denied: ${permission}`)
            return { name: 'Forbidden' }
        }

        return true
    }
}

/**
 * Route Specific Guards
 * Guards per route specifiche
 */

// Guard per verificare se il gioco può essere modificato
export const gameEditableGuard = async (to, from) => {
    const gameStore = useGameStore()
    const currentGame = gameStore.currentGame

    if (currentGame && currentGame.status === 'paused') {
        console.log('⏸️ Game is paused, some actions may be restricted')
        // Potresti voler mostrare un avviso o limitare certe azioni
    }

    return true
}

// Guard per verificare se l'utente ha un gioco in corso prima di crearne uno nuovo
export const newGameGuard = async (to, from) => {
    const gameStore = useGameStore()

    if (to.name === 'NewGame' && gameStore.currentGame && gameStore.currentGame.status === 'active') {
        // Chiedi conferma se vuole abbandonare il gioco corrente
        const confirmed = window.confirm(
            'Hai già un gioco in corso. Vuoi davvero crearne uno nuovo? Il gioco corrente verrà salvato automaticamente.'
        )

        if (!confirmed) {
            return false // Blocca la navigazione
        }
    }

    return true
}