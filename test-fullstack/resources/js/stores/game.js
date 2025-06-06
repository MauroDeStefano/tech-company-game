import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/js/services/api'

export const useGameStore = defineStore('game', () => {
  // ===== STATE =====
  const currentGame = ref(null)
  const loading = ref(false)
  const games = ref([])

  // ===== GETTERS =====
  const isGameActive = computed(() => currentGame.value?.status === 'active')
  const isGamePaused = computed(() => currentGame.value?.status === 'paused')
  const isGameOver = computed(() => currentGame.value?.status === 'game_over')

  // ===== ACTIONS =====
  async function createGame(gameData) {
    loading.value = true
    try {
      const response = await api.post('/games', gameData)
      currentGame.value = response.data.data
      return response.data.data
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

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

  async function saveGame() {
    if (!currentGame.value) return

    try {
      const response = await api.put(`/games/${currentGame.value.id}`, currentGame.value)
      currentGame.value = response.data.data
      return response.data.data
    } catch (error) {
      throw error
    }
  }

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

  async function completeProject(projectId) {
    try {
      const response = await api.post(`/projects/${projectId}/complete`)
      await refreshGameState() // Aggiorna lo stato del gioco
      return response.data
    } catch (error) {
      throw error
    }
  }

  async function unassignProject(projectId) {
    try {
      const response = await api.post(`/games/${currentGame.value.id}/projects/${projectId}/unassign`)
      await refreshGameState()
      return response.data
    } catch (error) {
      throw error
    }
  }

  function clearCurrentGame() {
    currentGame.value = null
  }

  return {
    // State
    currentGame,
    loading,
    games,

    // Getters
    isGameActive,
    isGamePaused,
    isGameOver,

    // Actions
    createGame,
    loadGame,
    saveGame,
    pauseGame,
    resumeGame,
    refreshGameState,
    completeProject,
    unassignProject,
    clearCurrentGame
  }
})