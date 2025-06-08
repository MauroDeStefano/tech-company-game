<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
              <span class="text-2xl mr-2">👤</span>
              Il Tuo Profilo
            </h1>
            <p class="text-lg text-gray-600 mt-1">
              Gestisci le informazioni del tuo account e le preferenze
            </p>
          </div>

          <div>
            <button
              @click="goToSettings"
              class="px-4 py-2 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-medium rounded-xl transition-colors duration-200 flex items-center gap-2"
            >
              <span class="text-lg">⚙️</span>
              Impostazioni
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- User Profile Card -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">👤</span>
            <h2 class="text-xl font-bold text-gray-900">Informazioni Profilo</h2>
          </div>

          <div class="flex flex-col lg:flex-row gap-8">
            <!-- Avatar Section -->
            <div class="flex flex-col items-center">
              <div class="relative">
                <div class="relative w-32 h-32 rounded-full overflow-hidden bg-gray-100 border-4 border-white shadow-lg">
                  <img
                    v-if="user?.avatar_url"
                    :src="user.avatar_url"
                    :alt="user.name"
                    class="w-full h-full object-cover"
                    @error="handleAvatarError"
                  />
                  <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                    {{ userInitials }}
                  </div>

                  <!-- Loading overlay -->
                  <div v-if="uploadingAvatar" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </div>
                </div>

                <!-- Avatar Actions -->
                <div class="flex gap-2 mt-4">
                  <button
                    @click="triggerFileInput"
                    :disabled="uploadingAvatar"
                    class="px-3 py-2 text-sm border border-gray-300 hover:border-gray-400 hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed text-gray-700 font-medium rounded-lg transition-colors duration-200 flex items-center gap-1"
                  >
                    <span class="text-sm">📷</span>
                    Cambia
                  </button>

                  <button
                    v-if="user?.avatar_url"
                    @click="removeAvatar"
                    :disabled="uploadingAvatar"
                    class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed font-medium rounded-lg transition-colors duration-200 flex items-center gap-1"
                  >
                    <span class="text-sm">🗑️</span>
                    Rimuovi
                  </button>
                </div>
              </div>

              <!-- Hidden file input -->
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleFileChange"
                class="hidden"
              />
            </div>

            <!-- User Details -->
            <div class="flex-1">
              <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ user?.name || 'Nome Utente' }}</h2>
                <p class="text-lg text-gray-600 mb-3">{{ user?.email }}</p>

                <!-- Verification Status -->
                <div class="mb-4">
                  <span
                    v-if="user?.email_verified_at"
                    class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-lg"
                  >
                    ✅ Email Verificata
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-lg"
                  >
                    ⚠️ Email Non Verificata
                  </span>
                </div>
              </div>

              <!-- User Stats -->
              <div class="grid grid-cols-3 gap-4">
                <div class="text-center bg-blue-50 rounded-lg p-4 border border-blue-200">
                  <div class="text-2xl font-bold text-blue-900">{{ userStats.accountAge }}</div>
                  <div class="text-sm text-blue-700">Giorni come membro</div>
                </div>

                <div class="text-center bg-green-50 rounded-lg p-4 border border-green-200">
                  <div class="text-2xl font-bold text-green-900">{{ userStats.totalGames }}</div>
                  <div class="text-sm text-green-700">Partite create</div>
                </div>

                <div class="text-center bg-purple-50 rounded-lg p-4 border border-purple-200">
                  <div class="text-2xl font-bold text-purple-900">{{ userStats.hoursPlayed }}h</div>
                  <div class="text-sm text-purple-700">Ore di gioco</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Edit Profile Form -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">✏️</span>
            <h2 class="text-xl font-bold text-gray-900">Modifica Profilo</h2>
          </div>

          <!-- Success Message -->
          <div v-if="successMessage" class="mb-6 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
            <span class="text-green-600">✅</span>
            <span class="text-green-800 flex-1">{{ successMessage }}</span>
            <button @click="successMessage = ''" class="text-green-600 hover:text-green-800 transition-colors duration-200">✕</button>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
            <span class="text-red-600">⚠️</span>
            <span class="text-red-800 flex-1">{{ errorMessage }}</span>
            <button @click="errorMessage = ''" class="text-red-600 hover:text-red-800 transition-colors duration-200">✕</button>
          </div>

          <form @submit.prevent="handleUpdateProfile" class="space-y-6">
            <!-- Name Field -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Completo</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">👤</span>
                </div>
                <input
                  v-model="profileForm.name"
                  type="text"
                  placeholder="Il tuo nome completo"
                  :disabled="isUpdating"
                  required
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.name }"
                />
              </div>
              <p v-if="validationErrors.name" class="text-red-600 text-sm font-medium mt-1">
                {{ validationErrors.name }}
              </p>
            </div>

            <!-- Email Field -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">✉️</span>
                </div>
                <input
                  v-model="profileForm.email"
                  type="email"
                  placeholder="tua@email.com"
                  :disabled="isUpdating"
                  required
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.email }"
                />
              </div>
              <p v-if="validationErrors.email" class="text-red-600 text-sm font-medium mt-1">
                {{ validationErrors.email }}
              </p>
            </div>

            <!-- Bio Field -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Bio (Opzionale)</label>
              <textarea
                v-model="profileForm.bio"
                placeholder="Raccontaci qualcosa di te..."
                :disabled="isUpdating"
                rows="3"
                maxlength="500"
                class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200 resize-none"
                :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': validationErrors.bio }"
              ></textarea>
              <div class="flex justify-between items-center mt-1">
                <p v-if="validationErrors.bio" class="text-red-600 text-sm font-medium">
                  {{ validationErrors.bio }}
                </p>
                <span class="text-gray-500 text-sm">{{ profileForm.bio.length }}/500</span>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-4">
              <button
                type="button"
                @click="resetForm"
                :disabled="isUpdating"
                class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed text-gray-700 font-semibold rounded-xl transition-colors duration-200"
              >
                Annulla
              </button>

              <button
                type="submit"
                :disabled="isUpdating || !isFormChanged"
                class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
              >
                <svg v-if="isUpdating" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="isUpdating">Salvando...</span>
                <span v-else>Salva Modifiche</span>
              </button>
            </div>
          </form>
        </div>
      </section>

      <!-- Change Password Section -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">🔒</span>
            <h2 class="text-xl font-bold text-gray-900">Cambia Password</h2>
          </div>

          <form @submit.prevent="handleChangePassword" class="space-y-6">
            <!-- Current Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Password Attuale</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">🔒</span>
                </div>
                <input
                  v-model="passwordForm.currentPassword"
                  type="password"
                  placeholder="Inserisci la password attuale"
                  :disabled="isChangingPassword"
                  required
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': passwordErrors.currentPassword }"
                />
              </div>
              <p v-if="passwordErrors.currentPassword" class="text-red-600 text-sm font-medium mt-1">
                {{ passwordErrors.currentPassword }}
              </p>
            </div>

            <!-- New Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nuova Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">🆕</span>
                </div>
                <input
                  v-model="passwordForm.newPassword"
                  type="password"
                  placeholder="Inserisci la nuova password"
                  :disabled="isChangingPassword"
                  required
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': passwordErrors.newPassword }"
                />
              </div>
              <p v-if="passwordErrors.newPassword" class="text-red-600 text-sm font-medium mt-1">
                {{ passwordErrors.newPassword }}
              </p>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Conferma Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-400 text-lg">✅</span>
                </div>
                <input
                  v-model="passwordForm.confirmPassword"
                  type="password"
                  placeholder="Ripeti la nuova password"
                  :disabled="isChangingPassword"
                  required
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': passwordErrors.confirmPassword }"
                />
              </div>
              <p v-if="passwordErrors.confirmPassword" class="text-red-600 text-sm font-medium mt-1">
                {{ passwordErrors.confirmPassword }}
              </p>
            </div>

            <!-- Password Strength Indicator -->
            <div v-if="passwordForm.newPassword" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <div class="text-sm text-gray-600 mb-2">Forza Password:</div>
              <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                <div
                  class="h-2 rounded-full transition-all duration-300"
                  :class="{
                    'bg-red-500': passwordStrength.level === 'weak',
                    'bg-yellow-500': passwordStrength.level === 'medium',
                    'bg-green-500': passwordStrength.level === 'strong'
                  }"
                  :style="{ width: `${passwordStrength.percentage}%` }"
                ></div>
              </div>
              <div class="text-sm font-medium"
                   :class="{
                     'text-red-600': passwordStrength.level === 'weak',
                     'text-yellow-600': passwordStrength.level === 'medium',
                     'text-green-600': passwordStrength.level === 'strong'
                   }">
                {{ passwordStrength.text }}
              </div>
            </div>

            <!-- Password Form Actions -->
            <div class="flex gap-4 pt-4">
              <button
                type="button"
                @click="resetPasswordForm"
                :disabled="isChangingPassword"
                class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed text-gray-700 font-semibold rounded-xl transition-colors duration-200"
              >
                Annulla
              </button>

              <button
                type="submit"
                :disabled="isChangingPassword || !isPasswordFormValid"
                class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
              >
                <svg v-if="isChangingPassword" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="isChangingPassword">Cambiando...</span>
                <span v-else>Cambia Password</span>
              </button>
            </div>
          </form>
        </div>
      </section>

      <!-- Account Stats -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">📊</span>
            <h2 class="text-xl font-bold text-gray-900">Statistiche Account</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
              <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">🎮</span>
                <span class="text-lg font-semibold text-blue-900">Gaming</span>
              </div>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-blue-700">Partite Totali</span>
                  <span class="font-bold text-blue-900">{{ userStats.totalGames }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-blue-700">Partite Completate</span>
                  <span class="font-bold text-blue-900">{{ userStats.completedGames }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-blue-700">Miglior Patrimonio</span>
                  <span class="font-bold text-blue-900">{{ formatCurrency(userStats.bestMoney) }}</span>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
              <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">📈</span>
                <span class="text-lg font-semibold text-green-900">Progressi</span>
              </div>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-green-700">Livello Utente</span>
                  <span class="font-bold text-green-900">{{ userStats.userLevel }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-green-700">Punti Esperienza</span>
                  <span class="font-bold text-green-900">{{ userStats.experiencePoints }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-green-700">Achievement</span>
                  <span class="font-bold text-green-900">{{ userStats.achievements }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Danger Zone -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-red-200 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">⚠️</span>
            <h2 class="text-xl font-bold text-red-900">Zona Pericolosa</h2>
          </div>

          <div class="bg-red-50 rounded-lg p-4 border border-red-200 mb-6">
            <p class="text-red-800">
              Le azioni in questa sezione sono irreversibili. Procedi con cautela.
            </p>
          </div>

          <div>
            <button
              @click="showDeleteModal = true"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200 flex items-center gap-2"
            >
              <span class="text-lg">🗑️</span>
              Elimina Account
            </button>
          </div>
        </div>
      </section>
    </div>

    <!-- Delete Account Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click="showDeleteModal = false"
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
              @click="showDeleteModal = false"
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
                <h4 class="font-bold text-red-900 mb-2">Attenzione: Azione Irreversibile</h4>
                <p class="text-red-800 mb-3">Eliminando il tuo account:</p>
                <ul class="space-y-1 text-red-700 text-sm">
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>Tutte le tue partite verranno eliminate definitivamente</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>I tuoi progressi e statistiche andranno persi</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>Non potrai più accedere a questo account</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-red-500 mt-1">•</span>
                    <span>Questa azione non può essere annullata</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Per confermare, scrivi 'ELIMINA' qui sotto:
            </label>
            <input
              v-model="deleteConfirmation"
              type="text"
              placeholder="ELIMINA"
              class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
              :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': deleteError }"
            />
            <p v-if="deleteError" class="text-red-600 text-sm font-medium mt-1">
              {{ deleteError }}
            </p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 flex items-center gap-3">
          <button
            @click="showDeleteModal = false"
            class="flex-1 px-4 py-3 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors duration-200"
          >
            Annulla
          </button>
          <button
            @click="handleDeleteAccount"
            :disabled="isDeletingAccount"
            class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
          >
            <svg v-if="isDeletingAccount" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="isDeletingAccount">Eliminando...</span>
            <span v-else>Elimina Account</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useNotificationStore } from '@/js/stores/notifications'
import { formatCurrency } from '@/js/utils/helpers'

// Stores
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()

// Reactive state
const isUpdating = ref(false)
const isChangingPassword = ref(false)
const isDeletingAccount = ref(false)
const uploadingAvatar = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const showDeleteModal = ref(false)
const deleteConfirmation = ref('')
const deleteError = ref('')
const fileInput = ref(null)

// Forms
const profileForm = reactive({
  name: '',
  email: '',
  bio: ''
})

const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const validationErrors = reactive({
  name: '',
  email: '',
  bio: ''
})

const passwordErrors = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Mock user stats (in realtà verrebbero dall'API)
const userStats = reactive({
  accountAge: 45,
  totalGames: 12,
  completedGames: 8,
  hoursPlayed: 87,
  bestMoney: 45000,
  userLevel: 5,
  experiencePoints: 2340,
  achievements: 15
})

// Computed
const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map(n => n.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

const isFormChanged = computed(() => {
  if (!user.value) return false
  return profileForm.name !== user.value.name ||
         profileForm.email !== user.value.email ||
         profileForm.bio !== (user.value.bio || '')
})

const isPasswordFormValid = computed(() => {
  return passwordForm.currentPassword &&
         passwordForm.newPassword &&
         passwordForm.confirmPassword &&
         passwordForm.newPassword === passwordForm.confirmPassword &&
         passwordForm.newPassword.length >= 8
})

const passwordStrength = computed(() => {
  const password = passwordForm.newPassword
  if (!password) return { level: 'none', percentage: 0, text: '' }

  let score = 0
  const checks = {
    length: password.length >= 8,
    lowercase: /[a-z]/.test(password),
    uppercase: /[A-Z]/.test(password),
    numbers: /\d/.test(password),
    special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
  }

  score = Object.values(checks).filter(Boolean).length

  const levels = {
    0: { level: 'none', percentage: 0, text: '' },
    1: { level: 'weak', percentage: 20, text: 'Molto Debole' },
    2: { level: 'weak', percentage: 40, text: 'Debole' },
    3: { level: 'medium', percentage: 60, text: 'Media' },
    4: { level: 'strong', percentage: 80, text: 'Forte' },
    5: { level: 'strong', percentage: 100, text: 'Molto Forte' }
  }

  return levels[score]
})

// Methods
const populateForm = () => {
  if (user.value) {
    profileForm.name = user.value.name || ''
    profileForm.email = user.value.email || ''
    profileForm.bio = user.value.bio || ''
  }
}

const resetForm = () => {
  populateForm()
  clearValidationErrors()
  clearMessages()
}

const resetPasswordForm = () => {
  passwordForm.currentPassword = ''
  passwordForm.newPassword = ''
  passwordForm.confirmPassword = ''
  clearPasswordErrors()
}

const clearValidationErrors = () => {
  Object.keys(validationErrors).forEach(key => {
    validationErrors[key] = ''
  })
}

const clearPasswordErrors = () => {
  Object.keys(passwordErrors).forEach(key => {
    passwordErrors[key] = ''
  })
}

const clearMessages = () => {
  successMessage.value = ''
  errorMessage.value = ''
}

const handleUpdateProfile = async () => {
  clearValidationErrors()
  clearMessages()

  isUpdating.value = true
  try {
    await authStore.updateProfile({
      name: profileForm.name,
      email: profileForm.email,
      bio: profileForm.bio
    })

    successMessage.value = 'Profilo aggiornato con successo!'
    setTimeout(() => {
      successMessage.value = ''
    }, 5000)

  } catch (error) {
    if (error.validationErrors) {
      Object.assign(validationErrors, error.validationErrors)
    } else {
      errorMessage.value = error.message || 'Errore nell\'aggiornamento del profilo'
    }
  } finally {
    isUpdating.value = false
  }
}

const handleChangePassword = async () => {
  clearPasswordErrors()
  clearMessages()

  // Client-side validation
  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    passwordErrors.confirmPassword = 'Le password non corrispondono'
    return
  }

  if (passwordForm.newPassword.length < 8) {
    passwordErrors.newPassword = 'La password deve essere di almeno 8 caratteri'
    return
  }

  isChangingPassword.value = true
  try {
    await authStore.changePassword({
      currentPassword: passwordForm.currentPassword,
      newPassword: passwordForm.newPassword,
      passwordConfirmation: passwordForm.confirmPassword
    })

    successMessage.value = 'Password cambiata con successo!'
    resetPasswordForm()

    setTimeout(() => {
      successMessage.value = ''
    }, 5000)

  } catch (error) {
    if (error.validationErrors) {
      Object.assign(passwordErrors, error.validationErrors)
    } else {
      errorMessage.value = error.message || 'Errore nel cambio password'
    }
  } finally {
    isChangingPassword.value = false
  }
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileChange = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file
  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'Seleziona un file immagine valido'
    return
  }

  if (file.size > 5 * 1024 * 1024) { // 5MB
    errorMessage.value = 'L\'immagine deve essere più piccola di 5MB'
    return
  }

  uploadingAvatar.value = true
  try {
    // Simula upload (in realtà useresti authService.uploadAvatar)
    await new Promise(resolve => setTimeout(resolve, 2000))
    successMessage.value = 'Avatar aggiornato con successo!'

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)

  } catch (error) {
    errorMessage.value = 'Errore nell\'upload dell\'avatar'
  } finally {
    uploadingAvatar.value = false
    event.target.value = '' // Reset input
  }
}

const removeAvatar = async () => {
  uploadingAvatar.value = true
  try {
    // Simula rimozione avatar
    await new Promise(resolve => setTimeout(resolve, 1000))
    successMessage.value = 'Avatar rimosso con successo!'

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)

  } catch (error) {
    errorMessage.value = 'Errore nella rimozione dell\'avatar'
  } finally {
    uploadingAvatar.value = false
  }
}

const handleAvatarError = () => {
  console.warn('Errore caricamento avatar')
}

const handleDeleteAccount = async () => {
  deleteError.value = ''

  if (deleteConfirmation.value !== 'ELIMINA') {
    deleteError.value = 'Devi scrivere "ELIMINA" per confermare'
    return
  }

  isDeletingAccount.value = true
  try {
    await authStore.deleteAccount()
    notificationStore.success('Account eliminato. Ci dispiace vederti andare!')
    router.push('/welcome')

  } catch (error) {
    deleteError.value = error.message || 'Errore nell\'eliminazione dell\'account'
  } finally {
    isDeletingAccount.value = false
  }
}

const goToSettings = () => {
  router.push({ name: 'Settings' })
}

// Lifecycle
onMounted(() => {
  populateForm()
})
</script>