<!-- src/views/game/NewGameView.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-white">
    <!-- Page Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 tracking-tight mb-4">
          <span class="text-3xl mr-3">🎮</span>
          Nuova Partita
        </h1>
        <p class="text-xl text-gray-600 max-w-md mx-auto">
          Crea la tua software house e inizia l'avventura!
        </p>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
        
        <!-- Game Preview Card -->
        <div class="order-2 lg:order-1">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <!-- Card Header -->
            <div class="mb-6">
              <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="text-lg">👁️</span>
                Anteprima Partita
              </h3>
            </div>
            
            <!-- Preview Content -->
            <div class="space-y-4">
              <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-gray-500">Nome Azienda:</span>
                <span class="text-sm font-semibold text-gray-900 text-right">
                  {{ form.name || 'La tua Software House' }}
                </span>
              </div>
              
              <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-gray-500">Patrimonio Iniziale:</span>
                <span class="text-sm font-semibold text-green-600 text-right">
                  {{ formatCurrency(form.initialMoney || 5000) }}
                </span>
              </div>
              
              <div class="flex justify-between items-start">
                <span class="text-sm font-medium text-gray-500">Team Iniziale:</span>
                <span class="text-sm font-semibold text-gray-900 text-right">
                  1 Sviluppatore + 1 Commerciale
                </span>
              </div>
              
              <div v-if="form.notes" class="flex justify-between items-start">
                <span class="text-sm font-medium text-gray-500">Note:</span>
                <span class="text-sm text-gray-700 text-right max-w-xs">
                  {{ form.notes }}
                </span>
              </div>
            </div>

            <!-- Difficulty Indicator -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <h4 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <span>💪</span>
                Livello Difficoltà
              </h4>
              
              <div class="flex items-center gap-3 mb-2" :class="difficultyClass">
                <div class="flex gap-1">
                  <div
                    v-for="i in 5"
                    :key="i"
                    class="w-3 h-3 rounded-full transition-colors duration-200"
                    :class="i <= difficultyLevel ? 'bg-current' : 'bg-gray-200'"
                  ></div>
                </div>
                <span class="text-sm font-semibold">{{ difficultyText }}</span>
              </div>
              
              <p class="text-xs text-gray-600">{{ difficultyDescription }}</p>
            </div>
          </div>
        </div>

        <!-- Form Section -->
        <div class="order-1 lg:order-2">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <!-- Card Header -->
            <div class="mb-6">
              <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="text-lg">⚙️</span>
                Configurazione Partita
              </h3>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Game Name -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">
                  🏢 Nome della tua Software House
                  <span class="text-red-500 ml-1">*</span>
                </label>
                
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-400 text-lg">🏢</span>
                  </div>
                  <input
                    v-model="form.name"
                    type="text"
                    placeholder="es. TechVision Solutions, CodeCraft Studio..."
                    class="block w-full pl-10 pr-4 py-3 border rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                    :class="{
                      'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name,
                      'border-gray-300': !errors.name
                    }"
                    @blur="validateField('name')"
                    @input="clearFieldError('name')"
                    required
                  />
                </div>
                
                <p v-if="errors.name" class="text-red-600 text-sm font-medium">
                  {{ errors.name }}
                </p>
                <p v-else class="text-gray-500 text-xs">
                  Scegli un nome accattivante per la tua azienda tech (3-50 caratteri)
                </p>
              </div>

              <!-- Initial Money -->
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">
                  💰 Patrimonio Iniziale
                  <span class="text-red-500 ml-1">*</span>
                </label>
                
                <!-- Preset Buttons -->
                <div class="grid grid-cols-2 gap-3">
                  <button
                    v-for="preset in moneyPresets"
                    :key="preset.value"
                    type="button"
                    class="p-4 border rounded-xl text-left transition-all duration-200 hover:shadow-md"
                    :class="form.initialMoney === preset.value 
                      ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-500 ring-opacity-20' 
                      : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'"
                    @click="setInitialMoney(preset.value)"
                  >
                    <div class="font-semibold text-sm text-gray-900">{{ preset.label }}</div>
                    <div class="text-lg font-bold text-purple-600">{{ formatCurrency(preset.value) }}</div>
                    <div class="text-xs text-gray-500">{{ preset.difficulty }}</div>
                  </button>
                </div>

                <!-- Custom Amount Input -->
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">
                    Importo Personalizzato
                  </label>
                  
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-400 text-lg">💵</span>
                    </div>
                    <input
                      v-model.number="form.initialMoney"
                      type="number"
                      placeholder="5000"
                      min="1000"
                      max="50000"
                      step="100"
                      class="block w-full pl-10 pr-4 py-3 border rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                      :class="{
                        'border-red-300 focus:ring-red-500 focus:border-red-500': errors.initialMoney,
                        'border-gray-300': !errors.initialMoney
                      }"
                      @blur="validateField('initialMoney')"
                      @input="clearFieldError('initialMoney')"
                    />
                  </div>
                  
                  <p v-if="errors.initialMoney" class="text-red-600 text-sm font-medium">
                    {{ errors.initialMoney }}
                  </p>
                  <p v-else class="text-gray-500 text-xs">
                    Da 1.000€ a 50.000€ (influenza la difficoltà del gioco)
                  </p>
                </div>
              </div>

              <!-- Game Notes -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">
                  📝 Note della Partita
                </label>
                
                <div class="relative">
                  <div class="absolute top-3 left-0 pl-3 pointer-events-none">
                    <span class="text-gray-400 text-lg">📝</span>
                  </div>
                  <textarea
                    v-model="form.notes"
                    rows="4"
                    placeholder="Descrivi la tua strategia, obiettivi o appunti per questa partita..."
                    maxlength="500"
                    class="block w-full pl-10 pr-4 py-3 border rounded-xl bg-white text-gray-900 placeholder-gray-500 resize-none focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                    :class="{
                      'border-red-300 focus:ring-red-500 focus:border-red-500': errors.notes,
                      'border-gray-300': !errors.notes
                    }"
                    @blur="validateField('notes')"
                    @input="clearFieldError('notes')"
                  ></textarea>
                </div>
                
                <div class="flex justify-between items-center">
                  <p v-if="errors.notes" class="text-red-600 text-sm font-medium">
                    {{ errors.notes }}
                  </p>
                  <p v-else class="text-gray-500 text-xs">
                    Opzionale: aggiungi note per ricordare la strategia di questa partita
                  </p>
                  <span class="text-xs text-gray-400">
                    {{ (form.notes || '').length }}/500
                  </span>
                </div>
              </div>

              <!-- Advanced Options -->
              <details class="group">
                <summary class="flex items-center justify-between cursor-pointer p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                  <div class="flex items-center gap-3">
                    <span class="text-lg">⚙️</span>
                    <span class="font-semibold text-gray-700">Opzioni Avanzate</span>
                  </div>
                  <span class="text-gray-400 group-open:rotate-90 transition-transform duration-200">›</span>
                </summary>
                
                <div class="mt-4 p-4 border border-gray-100 rounded-xl bg-gray-50 space-y-4">
                  <!-- Game Mode Selection -->
                  <div class="space-y-3">
                    <label class="block text-sm font-semibold text-gray-700">
                      🎯 Modalità di Gioco
                    </label>
                    
                    <div class="space-y-2">
                      <label
                        v-for="mode in gameModes"
                        :key="mode.id"
                        class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition-all duration-200 hover:bg-white"
                        :class="form.gameMode === mode.id 
                          ? 'border-purple-500 bg-purple-50' 
                          : 'border-gray-200 bg-white'"
                      >
                        <input
                          v-model="form.gameMode"
                          type="radio"
                          :value="mode.id"
                          class="mt-1 w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                        />
                        <div class="flex-1">
                          <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">{{ mode.icon }}</span>
                            <span class="font-semibold text-gray-900">{{ mode.name }}</span>
                          </div>
                          <p class="text-sm text-gray-600">{{ mode.description }}</p>
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Auto-save Settings -->
                  <label class="flex items-center gap-3 cursor-pointer">
                    <input
                      v-model="form.autoSave"
                      type="checkbox"
                      class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                    />
                    <span class="text-sm font-medium text-gray-700">
                      💾 Salvataggio automatico ogni 30 secondi
                    </span>
                  </label>

                  <!-- Tutorial Setting -->
                  <label class="flex items-center gap-3 cursor-pointer">
                    <input
                      v-model="form.showTutorial"
                      type="checkbox"
                      class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                    />
                    <span class="text-sm font-medium text-gray-700">
                      🎓 Mostra tutorial per nuovi giocatori
                    </span>
                  </label>
                </div>
              </details>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row gap-3 pt-6">
                <button
                  type="button"
                  @click="goBack"
                  :disabled="isCreating"
                  class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Annulla
                </button>

                <button
                  type="submit"
                  :disabled="!isFormValid || isCreating"
                  class="flex-1 px-4 py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
                >
                  <span v-if="isCreating" class="flex items-center gap-2">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creazione in corso...
                  </span>
                  <span v-else>
                    🚀 Crea Partita
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click="closeSuccessModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">🎉 Partita Creata!</h3>
            <button
              @click="closeSuccessModal"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
            >
              <span class="text-lg">✕</span>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <p class="text-gray-700 mb-6">
            La tua software house <strong>{{ createdGame?.name }}</strong> è stata creata con successo!
          </p>
          
          <div class="space-y-3 mb-6">
            <div class="flex items-center gap-3">
              <span class="text-lg">💰</span>
              <span class="text-sm text-gray-700">
                Patrimonio: {{ formatCurrency(createdGame?.money || 0) }}
              </span>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-lg">👥</span>
              <span class="text-sm text-gray-700">Team: 2 persone</span>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-lg">🎯</span>
              <span class="text-sm text-gray-700">Obiettivo: Non fallire!</span>
            </div>
          </div>

          <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-800">
              <span class="font-semibold">💡 Tip:</span> 
              Inizia assegnando progetti ai tuoi sviluppatori per generare entrate e far crescere la tua azienda!
            </p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200">
          <button
            @click="startGame"
            class="w-full px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-colors duration-200"
          >
            🎮 Inizia a Giocare!
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/js/stores/game'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isCreating = ref(false)
const showSuccessModal = ref(false)
const createdGame = ref(null)

// Form data
const form = reactive({
  name: '',
  initialMoney: 5000,
  notes: '',
  gameMode: 'normal',
  autoSave: true,
  showTutorial: true
})

// Form validation
const errors = reactive({})
const touched = reactive({})

// Money presets
const moneyPresets = [
  {
    label: 'Difficile',
    value: 2000,
    difficulty: '🔥 Hard',
    description: 'Per veri esperti'
  },
  {
    label: 'Normale',
    value: 5000,
    difficulty: '⚖️ Normal',
    description: 'Esperienza bilanciata'
  },
  {
    label: 'Facile',
    value: 10000,
    difficulty: '🟢 Easy',
    description: 'Per principianti'
  },
  {
    label: 'Creativo',
    value: 25000,
    difficulty: '🎨 Creative',
    description: 'Focus sulla strategia'
  }
]

// Game modes
const gameModes = [
  {
    id: 'normal',
    name: 'Normale',
    icon: '⚖️',
    description: 'Esperienza di gioco bilanciata'
  },
  {
    id: 'challenge',
    name: 'Sfida',
    icon: '🔥',
    description: 'Obiettivi extra e sfide settimanali'
  },
  {
    id: 'sandbox',
    name: 'Sandbox',
    icon: '🏖️',
    description: 'Gioco libero senza limitazioni'
  }
]

// Computed properties
const difficultyLevel = computed(() => {
  const money = form.initialMoney
  if (money <= 2000) return 5      // Very Hard
  if (money <= 4000) return 4      // Hard
  if (money <= 6000) return 3      // Normal
  if (money <= 10000) return 2     // Easy
  return 1                         // Very Easy
})

const difficultyClass = computed(() => {
  const level = difficultyLevel.value
  if (level >= 5) return 'text-red-600'
  if (level >= 4) return 'text-orange-600'
  if (level >= 3) return 'text-yellow-600'
  if (level >= 2) return 'text-green-600'
  return 'text-blue-600'
})

const difficultyText = computed(() => {
  const level = difficultyLevel.value
  const texts = ['Molto Facile', 'Facile', 'Normale', 'Difficile', 'Molto Difficile']
  return texts[level - 1] || 'Normale'
})

const difficultyDescription = computed(() => {
  const level = difficultyLevel.value
  const descriptions = [
    'Perfetto per imparare le meccaniche di gioco senza pressione',
    'Buon equilibrio tra sfida e accessibilità',
    'Esperienza bilanciata, né troppo facile né troppo difficile',
    'Richiede strategia e pianificazione attenta',
    'Solo per veri esperti! Ogni decisione conta'
  ]
  return descriptions[level - 1] || descriptions[2]
})

const isFormValid = computed(() => {
  return !Object.values(errors).some(error => error) &&
         form.name.trim() !== '' &&
         form.initialMoney >= 1000 &&
         form.initialMoney <= 50000
})

// Validation rules
const validationRules = {
  name: (value) => {
    if (!value?.trim()) return 'Il nome della software house è richiesto'
    if (value.length < 3) return 'Il nome deve essere di almeno 3 caratteri'
    if (value.length > 50) return 'Il nome non può superare i 50 caratteri'
    if (!/^[a-zA-ZÀ-ÿ0-9\s\-\.&]+$/.test(value)) {
      return 'Il nome può contenere solo lettere, numeri, spazi e i caratteri: - . &'
    }
    return ''
  },
  
  initialMoney: (value) => {
    const num = Number(value)
    if (isNaN(num)) return 'Inserisci un importo valido'
    if (num < 1000) return 'Il patrimonio minimo è 1.000€'
    if (num > 50000) return 'Il patrimonio massimo è 50.000€'
    if (num % 100 !== 0) return 'L\'importo deve essere un multiplo di 100€'
    return ''
  },
  
  notes: (value) => {
    if (!value) return '' // Field is optional
    if (value.length > 500) return 'Le note non possono superare i 500 caratteri'
    return ''
  }
}

// Methods
const validateField = (fieldName) => {
  touched[fieldName] = true
  const rule = validationRules[fieldName]
  if (rule) {
    errors[fieldName] = rule(form[fieldName])
  }
}

const clearFieldError = (fieldName) => {
  if (touched[fieldName]) {
    errors[fieldName] = ''
  }
}

const validateAllFields = () => {
  Object.keys(validationRules).forEach(fieldName => {
    validateField(fieldName)
  })
}

const setInitialMoney = (amount) => {
  form.initialMoney = amount
  clearFieldError('initialMoney')
}

const handleSubmit = async () => {
  validateAllFields()
  
  if (!isFormValid.value) {
    notificationStore.error('Controlla i campi evidenziati in rosso')
    return
  }

  isCreating.value = true

  try {
    // Prepare game data
    const gameData = {
      name: form.name.trim(),
      initial_money: form.initialMoney,
      notes: form.notes?.trim() || null
    }

    // Create the game via store
    const result = await gameStore.createGame(gameData)
    
    createdGame.value = result
    
    // 🎯 CORREZIONE 1: Carica immediatamente il game nello store
    await gameStore.loadGame(result.id)
    
    notificationStore.success('Partita creata con successo! 🎉')
    
    // 🎯 CORREZIONE 2: Opzioni di navigazione
    if (form.showTutorial) {
      // Se tutorial attivo, mostra modal poi naviga
      showSuccessModal.value = true
    } else {
      // Se no tutorial, naviga direttamente
      router.push('/game/dashboard')
    }
    
  } catch (error) {
    console.error('Error creating game:', error)
    
    // Handle validation errors from server
    if (error.response?.status === 422) {
      const serverErrors = error.response.data.errors
      Object.keys(serverErrors).forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = Array.isArray(serverErrors[field]) 
            ? serverErrors[field][0] 
            : serverErrors[field]
        }
      })
      notificationStore.error('Controlla i dati inseriti')
    } else {
      notificationStore.error('Errore nella creazione della partita. Riprova.')
    }
  } finally {
    isCreating.value = false
  }
}

const startGame = async () => {
  closeSuccessModal()
  
  // Assicurati che il game sia caricato nello store
  if (createdGame.value?.id && !gameStore.currentGame?.id) {
    try {
      await gameStore.loadGame(createdGame.value.id)
    } catch (error) {
      console.error('Error loading game:', error)
      notificationStore.error('Errore nel caricamento della partita')
      return
    }
  }
  
  router.push('/game/dashboard')
}
const closeSuccessModal = () => {
  showSuccessModal.value = false
  createdGame.value = null
}

const goBack = () => {
  router.push({ name: 'GameList' })
}

// Lifecycle
onMounted(() => {
  // Pre-populate with some random inspiring names
  const inspiringNames = [
    'TechVision Solutions',
    'CodeCraft Studio', 
    'InnovateLab',
    'DigitalForge',
    'ByteWorks',
    'NextGen Software',
    'CloudSprint',
    'AgileMinds',
    'DevStorm',
    'PixelForge'
  ]
  
  // Set a random placeholder name
  if (!form.name) {
    const randomName = inspiringNames[Math.floor(Math.random() * inspiringNames.length)]
    form.name = randomName
  }
})
</script>