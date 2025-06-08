// @/js/stores/game.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/js/services/api' // ✅ Usando il tuo import corretto

/**
 * 🎮 Game Store - Gestione stato dei giochi
 * 
 * Store Pinia per gestire:
 * - Lista dei giochi salvati
 * - Gioco corrente attivo
 * - Stato di caricamento
 * - Operazioni CRUD sui giochi
 */
export const useGameStore = defineStore('game', () => {
  // ✅ STATE - Usando ref() per dati reattivi (come nel tuo codice originale)
  const currentGame = ref(null)
  const games = ref([]) // ✅ Aggiunto per la lista giochi
  const loading = ref(false) // ✅ Mantengo il nome originale 'loading'

  // ✅ GETTERS - Usando computed() per valori derivati (mantengo i tuoi nomi originali)
  const isGameActive = computed(() => currentGame.value?.status === 'active')
  const isGamePaused = computed(() => currentGame.value?.status === 'paused')
  const isGameOver = computed(() => currentGame.value?.status === 'game_over')

  // ✅ Getters aggiuntivi utili
  const totalGames = computed(() => games.value.length)
  const activeGames = computed(() => {
    return games.value.filter(game => game.status === 'active')
  })

  // ✅ ACTIONS - Metodi per interagire con l'API Laravel

  /**
   * 📋 Fetch lista di tutti i giochi dell'utente
   * ⚠️ Questo è il metodo che GameListView.vue cercava di chiamare
   */
  async function fetchGamesList() {
    loading.value = true
    
    try {
      // 🔗 Chiamata API Laravel - usando la tua struttura URL
      const response = await api.get('/games') // ✅ Senza /api prefix come nel tuo codice
      
      // ✅ Laravel restituisce formato: { data: [...] }
      games.value = response.data.data || response.data || []
      
      console.log('✅ Games loaded:', games.value.length)
      
      return {
        data: games.value,
        success: true,
        message: 'Giochi caricati con successo'
      }
      
    } catch (error) {
      console.error('❌ Error fetching games:', error)
      
      // ⚠️ Struttura di errore consistente
      return {
        data: [],
        success: false,
        message: error.response?.data?.message || 'Errore nel caricamento dei giochi'
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * 🎮 Carica un gioco specifico e lo imposta come corrente (TUO METODO ORIGINALE)
   */
  async function loadGame(gameId) {
    loading.value = true
    try {
      const response = await api.get(`/games/${gameId}`)
      currentGame.value = response.data.data
      return response.data.data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * ➕ Crea un nuovo gioco (TUO METODO ORIGINALE)
   */
  async function createGame(gameData) {
    loading.value = true
    try {
      const response = await api.post('/games', gameData)
      currentGame.value = response.data.data
      
      // ✅ Aggiungi alla lista locale se esiste
      if (response.data.data) {
        games.value.unshift(response.data.data)
      }
      
      return response.data.data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * 💾 Salva partita corrente (TUO METODO ORIGINALE)
   */
  async function saveGame() {
    if (!currentGame.value) return

    try {
      const response = await api.put(`/games/${currentGame.value.id}`, currentGame.value)
      currentGame.value = response.data.data
      
      // ✅ Aggiorna nella lista locale se presente
      const gameIndex = games.value.findIndex(game => game.id === currentGame.value.id)
      if (gameIndex !== -1) {
        games.value[gameIndex] = response.data.data
      }
      
      return response.data.data
    } catch (error) {
      throw error
    }
  }

  /**
   * ⏸️ Pausa partita (TUO METODO ORIGINALE)
   */
  async function pauseGame() {
    if (!currentGame.value) return

    try {
      const response = await api.post(`/games/${currentGame.value.id}/pause`)
      currentGame.value = response.data.data
      return response.data.data
    } catch (error) {
      throw error
    }
  }

  /**
   * ▶️ Riprendi partita (TUO METODO ORIGINALE)
   */
  async function resumeGame() {
    if (!currentGame.value) return

    try {
      const response = await api.post(`/games/${currentGame.value.id}/resume`)
      currentGame.value = response.data.data
      return response.data.data
    } catch (error) {
      throw error
    }
  }

  /**
   * 🔄 Aggiorna stato gioco (TUO METODO ORIGINALE)
   */
  async function refreshGameState() {
    if (!currentGame.value) return

    try {
      const response = await api.get(`/games/${currentGame.value.id}/status`)
      currentGame.value = response.data.data
      return response.data.data
    } catch (error) {
      throw error
    }
  }

  /**
   * ✅ Completa progetto (TUO METODO ORIGINALE)
   */
  async function completeProject(projectId) {
    try {
      const response = await api.post(`/projects/${projectId}/complete`)
      await refreshGameState() // Aggiorna lo stato del gioco
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * 🚫 Annulla assegnazione progetto (TUO METODO ORIGINALE)
   */
  async function unassignProject(projectId) {
    try {
      const response = await api.post(`/games/${currentGame.value.id}/projects/${projectId}/unassign`)
      await refreshGameState()
      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * 🧹 Clear gioco corrente (TUO METODO ORIGINALE)
   */
  function clearCurrentGame() {
    currentGame.value = null
  }

  // ✅ METODI AGGIUNTIVI per GameListView.vue

  /**
   * 📝 Aggiorna un gioco esistente (per rinominare, etc.)
   */
  async function updateGame(gameId, updates) {
    loading.value = true
    
    try {
      const response = await api.put(`/games/${gameId}`, updates)
      const updatedGame = response.data.data || response.data
      
      // ✅ Aggiorna nella lista locale
      const gameIndex = games.value.findIndex(game => game.id === gameId)
      if (gameIndex !== -1) {
        games.value[gameIndex] = updatedGame
      }
      
      // ✅ Aggiorna gioco corrente se necessario
      if (currentGame.value && currentGame.value.id === gameId) {
        currentGame.value = updatedGame
      }
      
      return {
        data: updatedGame,
        success: true,
        message: 'Gioco aggiornato con successo'
      }
      
    } catch (error) {
      return {
        data: null,
        success: false,
        message: error.response?.data?.message || 'Errore nell\'aggiornamento del gioco'
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * 🗑️ Elimina un gioco
   */
  async function deleteGame(gameId) {
    loading.value = true
    
    try {
      await api.delete(`/games/${gameId}`)
      
      // ✅ Rimuovi dalla lista locale
      games.value = games.value.filter(game => game.id !== gameId)
      
      // ✅ Clear gioco corrente se è quello eliminato
      if (currentGame.value && currentGame.value.id === gameId) {
        currentGame.value = null
      }
      
      return {
        success: true,
        message: 'Gioco eliminato con successo'
      }
      
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Errore nell\'eliminazione del gioco'
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * 📄 Duplica un gioco esistente
   */
  async function duplicateGame(gameId) {
    loading.value = true
    
    try {
      const response = await api.post(`/games/${gameId}/duplicate`)
      const duplicatedGame = response.data.data || response.data
      
      // ✅ Aggiungi alla lista locale
      games.value.unshift(duplicatedGame)
      
      return {
        data: duplicatedGame,
        success: true,
        message: 'Gioco duplicato con successo'
      }
      
    } catch (error) {
      return {
        data: null,
        success: false,
        message: error.response?.data?.message || 'Errore nella duplicazione del gioco'
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * 📤 Esporta dati del gioco
   */
  async function exportGame(gameId) {
    loading.value = true
    
    try {
      // ⚠️ responseType blob per file download
      const response = await api.get(`/games/${gameId}/export`, {
        responseType: 'blob'
      })
      
      // ✅ Crea e scarica il file
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `game-${gameId}-export.json`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      
      return {
        success: true,
        message: 'Gioco esportato con successo'
      }
      
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Errore nell\'esportazione del gioco'
      }
    } finally {
      loading.value = false
    }
  }

  // ✅ RETURN - Esponi state, getters e actions
  return {
    // State (i tuoi nomi originali)
    currentGame,
    loading,
    games,
    
    // Getters (i tuoi nomi originali + nuovi)
    isGameActive,
    isGamePaused,
    isGameOver,
    totalGames,
    activeGames,
    
    // Actions (i tuoi metodi originali + quello mancante!)
    fetchGamesList, // ⚠️ QUESTO ERA IL METODO MANCANTE!
    loadGame,
    createGame,
    saveGame,
    pauseGame,
    resumeGame,
    refreshGameState,
    completeProject,
    unassignProject,
    clearCurrentGame,
    
    // Actions aggiuntive per GameListView
    updateGame,
    deleteGame,
    duplicateGame,
    exportGame
  }
})