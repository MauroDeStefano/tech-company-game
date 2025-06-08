// stores/game.js - CORREZIONE: Aggiunti computed getters per simulare Laravel Collections

import { defineStore } from 'pinia'
import api from '@/js/services/api'

export const useGameStore = defineStore('game', {
  state: () => ({
    currentGame: null,
    games: [],
    gamesMeta: null,
    isLoading: false,
    error: null
  }),

  // 🎯 CORREZIONE: Aggiunti computed getters che simulano i metodi Laravel
  getters: {
    // Basic getters
    hasCurrentGame: (state) => !!state.currentGame,
    currentGameId: (state) => state.currentGame?.id,
    currentGameMoney: (state) => state.currentGame?.money?.amount || 0,
    currentGameStatus: (state) => state.currentGame?.status,
    isGameActive: (state) => state.currentGame?.status === 'active',
    isGamePaused: (state) => state.currentGame?.status === 'paused',
    isGameOver: (state) => state.currentGame?.status === 'game_over',
    
    // 🎯 SOLUZIONE: Computed che simula projects() come Collection Laravel
    projects: (state) => {
      if (!state.currentGame?.recent_projects) {
        return {
          filter: () => [],
          where: () => [],
          length: 0,
          data: []
        }
      }

      const projectsArray = state.currentGame.recent_projects || []
      
      // Ritorna un oggetto che simula Laravel Collection
      return {
        // Metodo filter (compatibile con la tua chiamata)
        filter: (callback) => projectsArray.filter(callback),
        
        // Metodo where per compatibilità Laravel
        where: (key, operator, value) => {
          if (arguments.length === 2) {
            // where(key, value) - operator è omesso, assume '='
            value = operator
            operator = '='
          }
          
          return projectsArray.filter(project => {
            const projectValue = getNestedValue(project, key)
            
            switch (operator) {
              case '=':
              case '==':
                return projectValue === value
              case '!=':
                return projectValue !== value
              case '>':
                return projectValue > value
              case '<':
                return projectValue < value
              case '>=':
                return projectValue >= value
              case '<=':
                return projectValue <= value
              case 'in':
                return Array.isArray(value) && value.includes(projectValue)
              default:
                return projectValue === value
            }
          })
        },
        
        // Metodi di convenienza
        count: () => projectsArray.length,
        length: projectsArray.length,
        data: projectsArray,
        
        // Accesso diretto come array
        ...projectsArray
      }
    },

    // 🎯 SOLUZIONE: Computed che simula developers() come Collection Laravel  
    developers: (state) => {
      if (!state.currentGame?.developers) {
        return {
          filter: () => [],
          where: () => [],
          length: 0,
          data: []
        }
      }

      const developersArray = state.currentGame.developers || []
      
      return {
        filter: (callback) => developersArray.filter(callback),
        where: (key, operator, value) => {
          if (arguments.length === 2) {
            value = operator
            operator = '='
          }
          
          return developersArray.filter(developer => {
            const devValue = getNestedValue(developer, key)
            return devValue === value // Semplificato per ora
          })
        },
        available: () => developersArray.filter(dev => dev.status?.is_available),
        busy: () => developersArray.filter(dev => dev.status?.is_busy),
        count: () => developersArray.length,
        length: developersArray.length,
        data: developersArray,
        ...developersArray
      }
    },

    // 🎯 SOLUZIONE: Computed che simula salesPeople() come Collection Laravel
    salesPeople: (state) => {
      if (!state.currentGame?.sales_people) {
        return {
          filter: () => [],
          where: () => [],
          length: 0,
          data: []
        }
      }

      const salesArray = state.currentGame.sales_people || []
      
      return {
        filter: (callback) => salesArray.filter(callback),
        where: (key, operator, value) => {
          if (arguments.length === 2) {
            value = operator
            operator = '='
          }
          
          return salesArray.filter(person => {
            const personValue = getNestedValue(person, key)
            return personValue === value
          })
        },
        available: () => salesArray.filter(person => person.status?.is_available),
        busy: () => salesArray.filter(person => person.status?.is_busy),
        count: () => salesArray.length,
        length: salesArray.length,
        data: salesArray,
        ...salesArray
      }
    },

    // Lista games con status info
    gamesCount: (state) => state.games?.length || 0,
    activeGamesCount: (state) => state.gamesMeta?.active_games || 0,
    pausedGamesCount: (state) => state.gamesMeta?.paused_games || 0,
    completedGamesCount: (state) => state.gamesMeta?.completed_games || 0,
    
    // Current game team info
    currentGameTeamSize: (state) => {
      if (!state.currentGame?.team) return 0
      return (state.currentGame.team.developers_count || 0) + (state.currentGame.team.sales_people_count || 0)
    },
    
    // Current game projects info - USANDO I DATI CORRETTI DAL GAMERESOURCE
    currentGameActiveProjects: (state) => state.currentGame?.projects?.active_count || 0,
    currentGamePendingProjects: (state) => state.currentGame?.projects?.pending_count || 0,
    currentGameCompletedProjects: (state) => state.currentGame?.projects?.completed_count || 0
  },

  actions: {
    // Le tue actions esistenti rimangono uguali...
    async fetchGamesList() {
      this.isLoading = true
      this.error = null
      
      try {
        console.log('🔄 Fetching games list...')
        const response = await api.get('/games')
        
        console.log('📦 API Response:', response.data)
        
        const gamesData = response.data.data || []
        const metaData = response.data.meta || null
        
        this.games = gamesData
        this.gamesMeta = metaData
        
        console.log('✅ Games loaded:', gamesData.length)
        console.log('📊 Meta data:', metaData)
        
        return {
          data: gamesData,
          meta: metaData
        }
        
      } catch (error) {
        console.error('❌ Error fetching games:', error)
        this.error = error.response?.data?.message || 'Errore nel caricamento delle partite'
        this.games = []
        this.gamesMeta = null
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async createGame(gameData) {
      this.isLoading = true
      this.error = null
      
      try {
        console.log('🎮 Creating new game...', gameData)
        const response = await api.post('/games', gameData)
        
        const createdGame = response.data.data
        
        console.log('✅ Game created:', createdGame)
        
        this.currentGame = createdGame
        
        if (Array.isArray(this.games)) {
          const gameListItem = this.convertToGameListFormat(createdGame)
          this.games.unshift(gameListItem)
        }
        
        return createdGame
        
      } catch (error) {
        console.error('❌ Error creating game:', error)
        this.error = error.response?.data?.message || 'Errore nella creazione della partita'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async loadGame(gameId) {
      if (!gameId) {
        throw new Error('Game ID is required')
      }

      this.isLoading = true
      this.error = null
      
      try {
        console.log('🔄 Loading game:', gameId)
        const response = await api.get(`/games/${gameId}`)
        
        const gameData = response.data.data
        
        console.log('✅ Game loaded:', gameData)
        console.log('📊 Projects data:', gameData.recent_projects) // Per debug
        
        this.currentGame = gameData
        
        return gameData
        
      } catch (error) {
        console.error('❌ Error loading game:', error)
        this.error = error.response?.data?.message || 'Errore nel caricamento della partita'
        this.currentGame = null
        throw error
      } finally {
        this.isLoading = false
      }
    },

    // Altri metodi helper...
    convertToGameListFormat(gameResource) {
      return {
        id: gameResource.id,
        name: gameResource.name,
        money: gameResource.money,
        status: gameResource.status,
        status_label: this.getStatusLabel(gameResource.status),
        status_badge_class: this.getStatusBadgeClass(gameResource.status),
        team_size: (gameResource.team?.developers_count || 0) + (gameResource.team?.sales_people_count || 0),
        projects_completed: gameResource.projects?.completed_count || 0,
        projects_active: gameResource.projects?.active_count || 0,
        projects_pending: gameResource.projects?.pending_count || 0,
        play_time_hours: gameResource.timing?.play_time_hours || 0,
        play_time_formatted: this.formatPlayTime(gameResource.timing?.play_time_hours || 0),
        quick_stats: {
          is_profitable: (gameResource.money?.amount || 0) > 0,
          has_active_projects: (gameResource.projects?.active_count || 0) > 0,
          team_needs_expansion: ((gameResource.team?.developers_count || 0) + (gameResource.team?.sales_people_count || 0)) < 3
        },
        created_at: gameResource.timestamps?.created_at,
        updated_at: gameResource.timestamps?.updated_at,
        last_played: gameResource.timestamps?.last_played,
        last_played_date: new Date(gameResource.timestamps?.updated_at).toLocaleString('it-IT')
      }
    },

    getStatusLabel(status) {
      const labels = {
        'active': 'Attiva',
        'paused': 'In Pausa',
        'game_over': 'Terminata'
      }
      return labels[status] || 'Sconosciuto'
    },

    getStatusBadgeClass(status) {
      const classes = {
        'active': 'bg-green-100 text-green-800',
        'paused': 'bg-yellow-100 text-yellow-800', 
        'game_over': 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-500'
    },

    formatPlayTime(hours) {
      if (!hours || hours < 1) {
        return `${Math.round((hours || 0) * 60)}m`
      }
      return `${Math.round(hours * 10) / 10}h`
    },

    clearCurrentGame() {
      this.currentGame = null
    },

    clearError() {
      this.error = null
    }
  }
})

// 🎯 HELPER: Funzione per accedere a proprietà annidate
function getNestedValue(obj, path) {
  return path.split('.').reduce((current, key) => {
    return current && current[key] !== undefined ? current[key] : undefined
  }, obj)
}