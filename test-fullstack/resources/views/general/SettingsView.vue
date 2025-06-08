<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center gap-3">
          <span class="text-2xl">⚙️</span>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Impostazioni</h1>
            <p class="text-lg text-gray-600 mt-1">
              Personalizza la tua esperienza di gioco e gestisci le preferenze dell'account
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Game Settings -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">🎮</span>
            <h2 class="text-xl font-bold text-gray-900">Impostazioni di Gioco</h2>
          </div>

          <div class="space-y-6">
            <!-- Auto-save Settings -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="auto-save">Salvataggio Automatico</label>
                <p class="text-sm text-gray-600">
                  Salva automaticamente i progressi ogni 30 secondi
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="auto-save"
                    v-model="gameSettings.autoSave"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateGameSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Notifications -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="notifications">Notifiche di Gioco</label>
                <p class="text-sm text-gray-600">
                  Ricevi notifiche quando progetti e generazioni sono completati
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="notifications"
                    v-model="gameSettings.notifications"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateGameSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Sound Effects -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="sound-effects">Effetti Sonori</label>
                <p class="text-sm text-gray-600">
                  Abilita suoni per azioni e completamenti
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="sound-effects"
                    v-model="gameSettings.soundEffects"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateGameSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Animation Speed -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="animation-speed">Velocità Animazioni</label>
                <p class="text-sm text-gray-600">
                  Controlla la velocità delle animazioni nell'interfaccia
                </p>
              </div>
              <div class="ml-4">
                <select
                  id="animation-speed"
                  v-model="gameSettings.animationSpeed"
                  class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  @change="updateGameSettings"
                >
                  <option value="slow">Lenta</option>
                  <option value="normal">Normale</option>
                  <option value="fast">Veloce</option>
                  <option value="disabled">Disabilitate</option>
                </select>
              </div>
            </div>

            <!-- Difficulty Level -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="difficulty">Livello Difficoltà</label>
                <p class="text-sm text-gray-600">
                  Modifica la difficoltà del gioco (richiede riavvio partita)
                </p>
              </div>
              <div class="ml-4">
                <select
                  id="difficulty"
                  v-model="gameSettings.difficulty"
                  class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  @change="updateGameSettings"
                >
                  <option value="easy">Facile</option>
                  <option value="normal">Normale</option>
                  <option value="hard">Difficile</option>
                  <option value="expert">Esperto</option>
                </select>
              </div>
            </div>

            <!-- Timer Display -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="timer-display">Mostra Timer Dettagliati</label>
                <p class="text-sm text-gray-600">
                  Visualizza timer precisi per progetti e generazioni
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="timer-display"
                    v-model="gameSettings.detailedTimers"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateGameSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Interface Settings -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">🎨</span>
            <h2 class="text-xl font-bold text-gray-900">Interfaccia e Visualizzazione</h2>
          </div>

          <div class="space-y-6">
            <!-- Theme -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="theme">Tema</label>
                <p class="text-sm text-gray-600">
                  Scegli il tema dell'interfaccia
                </p>
              </div>
              <div class="ml-4">
                <select
                  id="theme"
                  v-model="interfaceSettings.theme"
                  class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  @change="updateInterfaceSettings"
                >
                  <option value="light">Chiaro</option>
                  <option value="dark">Scuro</option>
                  <option value="auto">Automatico</option>
                </select>
              </div>
            </div>

            <!-- Language -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="language">Lingua</label>
                <p class="text-sm text-gray-600">
                  Cambia la lingua dell'interfaccia
                </p>
              </div>
              <div class="ml-4">
                <select
                  id="language"
                  v-model="interfaceSettings.language"
                  class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  @change="updateInterfaceSettings"
                >
                  <option value="it">Italiano</option>
                  <option value="en">English</option>
                  <option value="es">Español</option>
                  <option value="fr">Français</option>
                </select>
              </div>
            </div>

            <!-- Currency Format -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="currency">Formato Valuta</label>
                <p class="text-sm text-gray-600">
                  Come visualizzare i valori monetari
                </p>
              </div>
              <div class="ml-4">
                <select
                  id="currency"
                  v-model="interfaceSettings.currencyFormat"
                  class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  @change="updateInterfaceSettings"
                >
                  <option value="eur">Euro (€)</option>
                  <option value="usd">Dollaro ($)</option>
                  <option value="gbp">Sterlina (£)</option>
                </select>
              </div>
            </div>

            <!-- Compact Mode -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="compact-mode">Modalità Compatta</label>
                <p class="text-sm text-gray-600">
                  Riduci gli spazi nell'interfaccia per visualizzare più contenuti
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="compact-mode"
                    v-model="interfaceSettings.compactMode"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateInterfaceSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Font Size -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="font-size">Dimensione Testo</label>
                <p class="text-sm text-gray-600">
                  Regola la dimensione del testo nell'interfaccia
                </p>
              </div>
              <div class="ml-4 flex items-center gap-3">
                <input
                  id="font-size"
                  v-model="interfaceSettings.fontSize"
                  type="range"
                  min="80"
                  max="120"
                  step="10"
                  class="w-24 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                  @input="updateInterfaceSettings"
                />
                <span class="text-sm font-medium text-gray-700 min-w-[3rem]">{{ interfaceSettings.fontSize }}%</span>
              </div>
            </div>

            <!-- Reduce Motion -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="reduce-motion">Riduci Movimento</label>
                <p class="text-sm text-gray-600">
                  Limita animazioni e transizioni per accessibilità
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="reduce-motion"
                    v-model="interfaceSettings.reduceMotion"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateInterfaceSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Account Settings -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">👤</span>
            <h2 class="text-xl font-bold text-gray-900">Account e Privacy</h2>
          </div>

          <div class="space-y-6">
            <!-- Email Notifications -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="email-notifications">Notifiche Email</label>
                <p class="text-sm text-gray-600">
                  Ricevi email per aggiornamenti importanti e promemoria
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="email-notifications"
                    v-model="accountSettings.emailNotifications"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateAccountSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Analytics -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="analytics">Condivisione Dati Analitici</label>
                <p class="text-sm text-gray-600">
                  Aiutaci a migliorare il gioco condividendo dati di utilizzo anonimi
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="analytics"
                    v-model="accountSettings.analytics"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateAccountSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Session Timeout -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="session-timeout">Timeout Sessione</label>
                <p class="text-sm text-gray-600">
                  Tempo di inattività prima del logout automatico
                </p>
              </div>
              <div class="ml-4">
                <select
                  id="session-timeout"
                  v-model="accountSettings.sessionTimeout"
                  class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  @change="updateAccountSettings"
                >
                  <option value="30">30 minuti</option>
                  <option value="60">1 ora</option>
                  <option value="120">2 ore</option>
                  <option value="240">4 ore</option>
                  <option value="0">Mai</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Account Actions -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Azioni Account</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <button
                @click="exportData"
                :disabled="exporting"
                class="flex flex-col items-center gap-2 p-4 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed rounded-lg transition-colors duration-200"
              >
                <span class="text-lg">📄</span>
                <span class="text-sm font-medium text-gray-700">
                  {{ exporting ? 'Esportando...' : 'Esporta Dati' }}
                </span>
              </button>

              <button
                @click="clearCache"
                :disabled="clearingCache"
                class="flex flex-col items-center gap-2 p-4 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed rounded-lg transition-colors duration-200"
              >
                <span class="text-lg">🔄</span>
                <span class="text-sm font-medium text-gray-700">
                  {{ clearingCache ? 'Pulendo...' : 'Pulisci Cache' }}
                </span>
              </button>

              <button
                @click="logoutAllDevices"
                :disabled="loggingOut"
                class="flex flex-col items-center gap-2 p-4 border border-yellow-300 hover:border-yellow-400 hover:bg-yellow-50 disabled:bg-gray-100 disabled:cursor-not-allowed text-yellow-700 rounded-lg transition-colors duration-200"
              >
                <span class="text-lg">🚪</span>
                <span class="text-sm font-medium">
                  {{ loggingOut ? 'Uscendo...' : 'Logout Tutto' }}
                </span>
              </button>

              <button
                @click="showDeleteAccountModal = true"
                class="flex flex-col items-center gap-2 p-4 border border-red-300 hover:border-red-400 hover:bg-red-50 text-red-700 rounded-lg transition-colors duration-200"
              >
                <span class="text-lg">🗑️</span>
                <span class="text-sm font-medium">Elimina Account</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Advanced Settings -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">🔧</span>
            <h2 class="text-xl font-bold text-gray-900">Impostazioni Avanzate</h2>
          </div>

          <div class="space-y-6">
            <!-- Debug Mode -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="debug-mode">Modalità Debug</label>
                <p class="text-sm text-gray-600">
                  Mostra informazioni tecniche per sviluppatori e debug
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="debug-mode"
                    v-model="advancedSettings.debugMode"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateAdvancedSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Performance Mode -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="performance-mode">Modalità Performance</label>
                <p class="text-sm text-gray-600">
                  Ottimizza per prestazioni su dispositivi più lenti
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="performance-mode"
                    v-model="advancedSettings.performanceMode"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateAdvancedSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>

            <!-- Beta Features -->
            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <label class="block text-base font-semibold text-gray-900 mb-1" for="beta-features">Funzionalità Beta</label>
                <p class="text-sm text-gray-600">
                  Abilita funzionalità sperimentali in fase di test
                </p>
              </div>
              <div class="ml-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    id="beta-features"
                    v-model="advancedSettings.betaFeatures"
                    type="checkbox"
                    class="sr-only peer"
                    @change="updateAdvancedSettings"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>
          </div>

          <!-- Reset Settings -->
          <div class="mt-8 pt-6 border-t border-gray-200 bg-gray-50 rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Ripristina Impostazioni</h4>
            <p class="text-sm text-gray-600 mb-4">
              Riporta tutte le impostazioni ai valori predefiniti
            </p>
            <button
              @click="resetAllSettings"
              :disabled="resetting"
              class="px-4 py-2 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed text-gray-700 font-medium rounded-lg transition-colors duration-200 flex items-center gap-2"
            >
              <span class="text-lg">🔄</span>
              {{ resetting ? 'Ripristinando...' : 'Ripristina Tutto' }}
            </button>
          </div>
        </div>
      </section>
    </div>

    <!-- Delete Account Modal -->
    <div
      v-if="showDeleteAccountModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click="showDeleteAccountModal = false"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="text-2xl">⚠️</span>
              <h3 class="text-xl font-bold text-red-900">Elimina Account</h3>
            </div>
            <button
              @click="showDeleteAccountModal = false"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
            >
              <span class="text-lg">✕</span>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <div class="bg-red-50 rounded-lg p-4 border border-red-200 mb-6">
            <div class="flex items-start gap-3">
              <span class="text-2xl text-red-600">⚠️</span>
              <div class="flex-1">
                <p class="font-bold text-red-900 mb-2">Azione Irreversibile</p>
                <p class="text-red-800 mb-3">
                  L'eliminazione dell'account cancellerà permanentemente:
                </p>
                <ul class="space-y-1 text-red-700 text-sm">
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>Tutte le tue partite salvate</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>Statistiche e progressi</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>Impostazioni personalizzate</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>Tutti i dati associati all'account</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2" for="delete-confirmation">
              Scrivi "ELIMINA" per confermare:
            </label>
            <input
              id="delete-confirmation"
              v-model="deleteConfirmation"
              type="text"
              placeholder="ELIMINA"
              class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
              :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': deleteConfirmationError }"
            />
            <p v-if="deleteConfirmationError" class="text-red-600 text-sm font-medium mt-1">
              {{ deleteConfirmationError }}
            </p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 flex items-center gap-3">
          <button
            @click="cancelDeleteAccount"
            class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200"
          >
            Annulla
          </button>
          <button
            @click="confirmDeleteAccount"
            :disabled="!canDeleteAccount || deletingAccount"
            class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
          >
            <svg v-if="deletingAccount" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="deletingAccount">Eliminando...</span>
            <span v-else>Elimina Definitivamente</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'

// Stores
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const exporting = ref(false)
const clearingCache = ref(false)
const loggingOut = ref(false)
const resetting = ref(false)
const deletingAccount = ref(false)
const showDeleteAccountModal = ref(false)
const deleteConfirmation = ref('')
const deleteConfirmationError = ref('')

// Settings objects
const gameSettings = ref({
  autoSave: true,
  notifications: true,
  soundEffects: true,
  animationSpeed: 'normal',
  difficulty: 'normal',
  detailedTimers: false
})

const interfaceSettings = ref({
  theme: 'light',
  language: 'it',
  currencyFormat: 'eur',
  compactMode: false,
  fontSize: 100,
  reduceMotion: false
})

const accountSettings = ref({
  emailNotifications: true,
  analytics: true,
  sessionTimeout: 60
})

const advancedSettings = ref({
  debugMode: false,
  performanceMode: false,
  betaFeatures: false
})

// Computed properties
const canDeleteAccount = computed(() => {
  return deleteConfirmation.value === 'ELIMINA'
})

// Methods
const loadSettings = () => {
  try {
    // Load from localStorage or API
    const savedGameSettings = localStorage.getItem('gameSettings')
    if (savedGameSettings) {
      gameSettings.value = { ...gameSettings.value, ...JSON.parse(savedGameSettings) }
    }

    const savedInterfaceSettings = localStorage.getItem('interfaceSettings')
    if (savedInterfaceSettings) {
      interfaceSettings.value = { ...interfaceSettings.value, ...JSON.parse(savedInterfaceSettings) }
    }

    const savedAccountSettings = localStorage.getItem('accountSettings')
    if (savedAccountSettings) {
      accountSettings.value = { ...accountSettings.value, ...JSON.parse(savedAccountSettings) }
    }

    const savedAdvancedSettings = localStorage.getItem('advancedSettings')
    if (savedAdvancedSettings) {
      advancedSettings.value = { ...advancedSettings.value, ...JSON.parse(savedAdvancedSettings) }
    }
  } catch (error) {
    console.error('Error loading settings:', error)
  }
}

const updateGameSettings = () => {
  try {
    localStorage.setItem('gameSettings', JSON.stringify(gameSettings.value))
    notificationStore.success('Impostazioni di gioco aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const updateInterfaceSettings = () => {
  try {
    localStorage.setItem('interfaceSettings', JSON.stringify(interfaceSettings.value))

    // Apply theme immediately
    applyTheme()

    // Apply font size
    applyFontSize()

    notificationStore.success('Impostazioni interfaccia aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const updateAccountSettings = () => {
  try {
    localStorage.setItem('accountSettings', JSON.stringify(accountSettings.value))
    notificationStore.success('Impostazioni account aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const updateAdvancedSettings = () => {
  try {
    localStorage.setItem('advancedSettings', JSON.stringify(advancedSettings.value))
    notificationStore.success('Impostazioni avanzate aggiornate')
  } catch (error) {
    notificationStore.error('Errore nel salvataggio delle impostazioni')
  }
}

const applyTheme = () => {
  const theme = interfaceSettings.value.theme
  if (theme === 'dark') {
    document.documentElement.classList.add('dark')
  } else if (theme === 'light') {
    document.documentElement.classList.remove('dark')
  } else {
    // Auto theme
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    if (prefersDark) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }
}

const applyFontSize = () => {
  const fontSize = interfaceSettings.value.fontSize
  document.documentElement.style.fontSize = `${fontSize}%`
}

const exportData = async () => {
  exporting.value = true
  try {
    // Simulate data export
    await new Promise(resolve => setTimeout(resolve, 2000))

    const data = {
      settings: {
        game: gameSettings.value,
        interface: interfaceSettings.value,
        account: accountSettings.value,
        advanced: advancedSettings.value
      },
      user: authStore.user,
      timestamp: new Date().toISOString()
    }

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `tech-company-game-data-${Date.now()}.json`
    link.click()

    URL.revokeObjectURL(url)
    notificationStore.success('Dati esportati con successo')
  } catch (error) {
    notificationStore.error('Errore nell\'esportazione dei dati')
  } finally {
    exporting.value = false
  }
}

const clearCache = async () => {
  clearingCache.value = true
  try {
    // Clear various caches
    if ('caches' in window) {
      const cacheNames = await caches.keys()
      await Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
      )
    }

    // Clear localStorage (except auth and settings)
    const keysToKeep = ['auth-token', 'gameSettings', 'interfaceSettings', 'accountSettings', 'advancedSettings']
    Object.keys(localStorage).forEach(key => {
      if (!keysToKeep.includes(key)) {
        localStorage.removeItem(key)
      }
    })

    notificationStore.success('Cache pulita con successo')
  } catch (error) {
    notificationStore.error('Errore nella pulizia della cache')
  } finally {
    clearingCache.value = false
  }
}

const logoutAllDevices = async () => {
  loggingOut.value = true
  try {
    await authStore.logoutAll()
    notificationStore.success('Logout effettuato da tutti i dispositivi')
    router.push({ name: 'Login' })
  } catch (error) {
    notificationStore.error('Errore nel logout')
  } finally {
    loggingOut.value = false
  }
}

const resetAllSettings = async () => {
  resetting.value = true
  try {
    // Reset to defaults
    gameSettings.value = {
      autoSave: true,
      notifications: true,
      soundEffects: true,
      animationSpeed: 'normal',
      difficulty: 'normal',
      detailedTimers: false
    }

    interfaceSettings.value = {
      theme: 'light',
      language: 'it',
      currencyFormat: 'eur',
      compactMode: false,
      fontSize: 100,
      reduceMotion: false
    }

    accountSettings.value = {
      emailNotifications: true,
      analytics: true,
      sessionTimeout: 60
    }

    advancedSettings.value = {
      debugMode: false,
      performanceMode: false,
      betaFeatures: false
    }

    // Save all settings
    updateGameSettings()
    updateInterfaceSettings()
    updateAccountSettings()
    updateAdvancedSettings()

    notificationStore.success('Tutte le impostazioni sono state ripristinate')
  } catch (error) {
    notificationStore.error('Errore nel ripristino delle impostazioni')
  } finally {
    resetting.value = false
  }
}

const cancelDeleteAccount = () => {
  showDeleteAccountModal.value = false
  deleteConfirmation.value = ''
  deleteConfirmationError.value = ''
}

const confirmDeleteAccount = async () => {
  if (!canDeleteAccount.value) {
    deleteConfirmationError.value = 'Scrivi "ELIMINA" per confermare'
    return
  }

  deletingAccount.value = true
  try {
    await authStore.deleteAccount()
    notificationStore.success('Account eliminato con successo')
    router.push({ name: 'Welcome' })
  } catch (error) {
    notificationStore.error('Errore nell\'eliminazione dell\'account')
  } finally {
    deletingAccount.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadSettings()
  applyTheme()
  applyFontSize()
})
</script>