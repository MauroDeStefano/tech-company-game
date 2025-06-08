<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-white">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
              <span class="text-2xl mr-2">📊</span>
              Statistiche Partita
            </h1>
            <p class="text-lg text-gray-600 mt-1">
              Analisi dettagliata delle performance della tua software house
            </p>
          </div>

          <div class="flex flex-wrap gap-3">
            <button
              @click="exportStats"
              :disabled="loading"
              class="px-4 py-2 border border-gray-300 hover:border-gray-400 hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed text-gray-700 font-medium rounded-xl transition-colors duration-200 flex items-center gap-2"
            >
              <span class="text-lg">📄</span>
              Esporta Report
            </button>
            
            <button
              @click="refreshStats"
              :disabled="loading"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-colors duration-200 flex items-center gap-2"
            >
              <svg v-if="loading" class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-else class="text-lg">🔄</span>
              Aggiorna
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !gameStats" class="flex flex-col items-center justify-center py-24">
      <svg class="animate-spin h-12 w-12 text-blue-600 mb-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
      </svg>
      <p class="text-gray-600 text-lg">Caricamento statistiche...</p>
    </div>

    <!-- Main Stats Content -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Overview Cards -->
      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
          <!-- Financial Overview -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 xl:col-span-2">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">💰</span>
              <h2 class="text-xl font-bold text-gray-900">Situazione Finanziaria</h2>
            </div>

            <div class="space-y-6">
              <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                <div class="text-sm text-blue-600 mb-1">Patrimonio Attuale</div>
                <div class="text-3xl font-bold" :class="moneyColorClass">
                  {{ formatCurrency(gameStats.currentMoney) }}
                </div>
                <div class="flex items-center justify-center gap-2 mt-2" :class="moneyTrendClass">
                  <span>{{ moneyTrendIcon }}</span>
                  <span class="text-sm font-medium">{{ moneyChangeText }}</span>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                  <div class="text-sm text-gray-500 mb-1">Patrimonio Iniziale</div>
                  <div class="text-lg font-bold text-gray-900">{{ formatCurrency(gameStats.initialMoney) }}</div>
                </div>
                <div class="text-center">
                  <div class="text-sm text-gray-500 mb-1">Revenue Totale</div>
                  <div class="text-lg font-bold text-green-600">{{ formatCurrency(gameStats.totalRevenue) }}</div>
                </div>
                <div class="text-center">
                  <div class="text-sm text-gray-500 mb-1">Costi Totali</div>
                  <div class="text-lg font-bold text-red-600">{{ formatCurrency(gameStats.totalCosts) }}</div>
                </div>
                <div class="text-center">
                  <div class="text-sm text-gray-500 mb-1">Profitto Netto</div>
                  <div class="text-lg font-bold" :class="profitColorClass">{{ formatCurrency(netProfit) }}</div>
                </div>
              </div>

              <!-- Profit Margin Bar -->
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm text-gray-600">Margine di Profitto</span>
                  <span class="text-sm font-bold" :class="profitMarginColorClass">
                    {{ profitMarginPercentage }}%
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="h-3 rounded-full transition-all duration-500"
                    :class="profitMarginBarClass"
                    :style="{ width: `${Math.min(100, Math.abs(profitMarginPercentage))}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Projects Overview -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">🚀</span>
              <h2 class="text-xl font-bold text-gray-900">Progetti</h2>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                  <div class="text-2xl font-bold text-green-600">{{ gameStats.projectsCompleted }}</div>
                  <div class="text-xs text-green-700">Completati</div>
                </div>
                <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                  <div class="text-2xl font-bold text-blue-600">{{ gameStats.projectsActive }}</div>
                  <div class="text-xs text-blue-700">In Corso</div>
                </div>
                <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                  <div class="text-2xl font-bold text-yellow-600">{{ gameStats.projectsPending }}</div>
                  <div class="text-xs text-yellow-700">In Attesa</div>
                </div>
              </div>

              <!-- Project Success Rate -->
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm text-gray-600">Tasso di Successo</span>
                  <span class="text-sm font-bold text-green-600">{{ projectSuccessRate }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-green-500 h-2 rounded-full transition-all duration-500"
                    :style="{ width: `${projectSuccessRate}%` }"
                  ></div>
                </div>
              </div>

              <!-- Project Metrics -->
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Valore Medio Progetto</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(averageProjectValue) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Progetti/Settimana</span>
                  <span class="text-sm font-medium text-gray-900">{{ projectsPerWeek }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Team Overview -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">👥</span>
              <h2 class="text-xl font-bold text-gray-900">Team</h2>
            </div>

            <div class="space-y-4">
              <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                  <div class="flex items-center gap-2">
                    <span class="text-lg">👨‍💻</span>
                    <div>
                      <div class="text-lg font-bold text-blue-900">{{ gameStats.developersCount }}</div>
                      <div class="text-xs text-blue-700">Sviluppatori</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs text-blue-600">Efficienza</div>
                    <div class="text-sm font-bold text-blue-900">{{ developersEfficiency }}%</div>
                  </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-200">
                  <div class="flex items-center gap-2">
                    <span class="text-lg">💼</span>
                    <div>
                      <div class="text-lg font-bold text-purple-900">{{ gameStats.salesPeopleCount }}</div>
                      <div class="text-xs text-purple-700">Commerciali</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs text-purple-600">Efficienza</div>
                    <div class="text-sm font-bold text-purple-900">{{ salesEfficiency }}%</div>
                  </div>
                </div>
              </div>

              <!-- Team Costs -->
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-center mb-3">
                  <span class="text-sm text-gray-600">Costi Team/Mese</span>
                  <span class="text-sm font-bold text-gray-900">{{ formatCurrency(gameStats.monthlyCosts) }}</span>
                </div>
                
                <div class="space-y-2">
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Sviluppatori</span>
                    <span class="text-gray-700">{{ formatCurrency(gameStats.developersCosts) }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Commerciali</span>
                    <span class="text-gray-700">{{ formatCurrency(gameStats.salesCosts) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance Overview -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">⚡</span>
              <h2 class="text-xl font-bold text-gray-900">Performance</h2>
            </div>

            <div class="space-y-4">
              <!-- Overall Score -->
              <div class="text-center">
                <div class="relative inline-flex items-center justify-center w-24 h-24 rounded-full border-4 mb-3"
                     :class="performanceScoreClass">
                  <div class="text-center">
                    <div class="text-2xl font-bold">{{ overallPerformanceScore }}</div>
                    <div class="text-xs opacity-75">/ 100</div>
                  </div>
                </div>
                <div>
                  <div class="text-lg font-bold text-gray-900">{{ performanceRating }}</div>
                  <div class="text-sm text-gray-600">Performance Generale</div>
                </div>
              </div>

              <!-- Performance Metrics -->
              <div class="space-y-3">
                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Efficienza Operativa</span>
                    <span class="font-medium">{{ operationalEfficiency }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" :style="{ width: `${operationalEfficiency}%` }"></div>
                  </div>
                </div>

                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Crescita Revenue</span>
                    <span class="font-medium">{{ revenueGrowth }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all duration-500" :style="{ width: `${revenueGrowth}%` }"></div>
                  </div>
                </div>

                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Soddisfazione Team</span>
                    <span class="font-medium">{{ teamSatisfaction }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-500 h-2 rounded-full transition-all duration-500" :style="{ width: `${teamSatisfaction}%` }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Detailed Charts Section -->
      <section>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Revenue Chart -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">📈</span>
              <h2 class="text-xl font-bold text-gray-900">Andamento Revenue</h2>
            </div>

            <div class="h-64 mb-4 bg-gray-50 rounded-lg flex items-center justify-center">
              <canvas ref="revenueChart" class="max-w-full max-h-full"></canvas>
            </div>
            
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <div class="text-sm text-gray-500">Picco Massimo</div>
                <div class="text-lg font-bold text-gray-900">{{ formatCurrency(revenueStats.peak) }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Media Settimanale</div>
                <div class="text-lg font-bold text-gray-900">{{ formatCurrency(revenueStats.weeklyAverage) }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Trend</div>
                <div class="text-lg font-bold" :class="revenueTrendClass">
                  {{ revenueTrendText }}
                </div>
              </div>
            </div>
          </div>

          <!-- Team Productivity Chart -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">👥</span>
              <h2 class="text-xl font-bold text-gray-900">Produttività Team</h2>
            </div>

            <div class="h-64 mb-4 bg-gray-50 rounded-lg flex items-center justify-center">
              <canvas ref="productivityChart" class="max-w-full max-h-full"></canvas>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between p-3 rounded-lg"
                   :class="productivityInsights.developers.trend === 'insight-item--positive' ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                <div class="flex items-center gap-3">
                  <span class="text-xl">👨‍💻</span>
                  <div>
                    <div class="font-medium text-gray-900">Sviluppatori</div>
                    <div class="text-sm text-gray-600">{{ productivityInsights.developers.description }}</div>
                  </div>
                </div>
                <div class="text-lg font-bold text-gray-900">{{ productivityInsights.developers.score }}%</div>
              </div>

              <div class="flex items-center justify-between p-3 rounded-lg"
                   :class="productivityInsights.sales.trend === 'insight-item--positive' ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                <div class="flex items-center gap-3">
                  <span class="text-xl">💼</span>
                  <div>
                    <div class="font-medium text-gray-900">Commerciali</div>
                    <div class="text-sm text-gray-600">{{ productivityInsights.sales.description }}</div>
                  </div>
                </div>
                <div class="text-lg font-bold text-gray-900">{{ productivityInsights.sales.score }}%</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Detailed Analytics -->
      <section>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Projects Breakdown -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">📊</span>
              <h2 class="text-xl font-bold text-gray-900">Analisi Progetti</h2>
            </div>

            <div class="space-y-6">
              <!-- By Complexity -->
              <div>
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Per Complessità</h4>
                <div class="space-y-4">
                  <div
                    v-for="complexity in complexityBreakdown"
                    :key="complexity.level"
                    class="bg-gray-50 rounded-lg p-4"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <span class="font-medium text-gray-900">{{ complexity.label }}</span>
                      <span class="text-sm text-gray-600">{{ complexity.count }} progetti</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                      <div
                        class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                        :style="{ width: `${complexity.percentage}%` }"
                      ></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                      <span>Valore: {{ formatCurrency(complexity.totalValue) }}</span>
                      <span>Media: {{ formatCurrency(complexity.averageValue) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- By Status -->
              <div>
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Per Status</h4>
                <div class="flex items-center gap-6">
                  <div class="w-32 h-32 bg-gray-50 rounded-lg flex items-center justify-center">
                    <canvas ref="statusPieChart" class="max-w-full max-h-full"></canvas>
                  </div>
                  <div class="flex-1 space-y-2">
                    <div
                      v-for="status in statusBreakdown"
                      :key="status.name"
                      class="flex items-center justify-between"
                    >
                      <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: status.color }"></div>
                        <span class="text-sm text-gray-700">{{ status.label }}</span>
                      </div>
                      <span class="text-sm font-medium text-gray-900">{{ status.count }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Time Analysis -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-2xl">⏰</span>
              <h2 class="text-xl font-bold text-gray-900">Analisi Tempi</h2>
            </div>

            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                  <div class="text-sm text-blue-600 mb-1">Tempo Totale di Gioco</div>
                  <div class="text-xl font-bold text-blue-900">{{ formatPlayTime(gameStats.totalPlayTime) }}</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                  <div class="text-sm text-green-600 mb-1">Tempo Medio per Progetto</div>
                  <div class="text-xl font-bold text-green-900">{{ formatDuration(averageProjectTime) }}</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                  <div class="text-sm text-purple-600 mb-1">Sessioni di Gioco</div>
                  <div class="text-xl font-bold text-purple-900">{{ gameStats.gameSessions }}</div>
                </div>
              </div>

              <!-- Productivity by Hour -->
              <div>
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Produttività per Ora</h4>
                <div class="h-32 bg-gray-50 rounded-lg flex items-center justify-center">
                  <canvas ref="hourlyChart" class="max-w-full max-h-full"></canvas>
                </div>
              </div>

              <!-- Milestones -->
              <div>
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Traguardi Raggiunti</h4>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                  <div
                    v-for="milestone in achievedMilestones"
                    :key="milestone.id"
                    class="flex items-center justify-between p-3 rounded-lg transition-colors duration-200"
                    :class="milestone.isRecent ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50 border border-gray-200'"
                  >
                    <div class="flex items-center gap-3">
                      <span class="text-xl">{{ milestone.icon }}</span>
                      <div>
                        <div class="font-medium text-gray-900">{{ milestone.title }}</div>
                        <div class="text-sm text-gray-600">{{ formatDate(milestone.achievedAt) }}</div>
                      </div>
                    </div>
                    <div v-if="milestone.isRecent" class="px-2 py-1 bg-yellow-200 text-yellow-800 text-xs font-medium rounded-lg">
                      Nuovo!
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Recommendations -->
      <section>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">💡</span>
            <h2 class="text-xl font-bold text-gray-900">Raccomandazioni</h2>
          </div>

          <div class="space-y-4">
            <p class="text-gray-600">
              Basandoci sulle tue performance, ecco alcuni consigli per migliorare la tua software house:
            </p>
            
            <div class="space-y-4">
              <div
                v-for="recommendation in recommendations"
                :key="recommendation.id"
                class="border rounded-xl p-4 transition-all duration-200"
                :class="{
                  'border-red-200 bg-red-50': recommendation.priority === 'high',
                  'border-yellow-200 bg-yellow-50': recommendation.priority === 'medium',
                  'border-green-200 bg-green-50': recommendation.priority === 'low'
                }"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <span class="text-xl">{{ recommendation.icon }}</span>
                    <div>
                      <h3 class="font-semibold text-gray-900">{{ recommendation.title }}</h3>
                      <span class="px-2 py-1 rounded-lg text-xs font-medium"
                            :class="{
                              'bg-red-200 text-red-800': recommendation.priority === 'high',
                              'bg-yellow-200 text-yellow-800': recommendation.priority === 'medium',
                              'bg-green-200 text-green-800': recommendation.priority === 'low'
                            }">
                        {{ recommendation.priorityLabel }}
                      </span>
                    </div>
                  </div>
                </div>
                <p class="text-gray-700 mb-4">{{ recommendation.description }}</p>
                <div v-if="recommendation.action">
                  <button
                    @click="handleRecommendationAction(recommendation)"
                    class="px-4 py-2 font-semibold rounded-lg transition-colors duration-200"
                    :class="{
                      'bg-red-600 hover:bg-red-700 text-white': recommendation.actionVariant === 'danger',
                      'bg-yellow-600 hover:bg-yellow-700 text-white': recommendation.actionVariant === 'warning',
                      'bg-blue-600 hover:bg-blue-700 text-white': recommendation.actionVariant === 'primary',
                      'bg-green-600 hover:bg-green-700 text-white': recommendation.actionVariant === 'success'
                    }"
                  >
                    {{ recommendation.actionText }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useNotificationStore } from '@/stores/notifications'
import { formatCurrency, formatDate, formatDuration } from '@/utils/helpers'

// Stores
const gameStore = useGameStore()
const notificationStore = useNotificationStore()
const route = useRoute()
const router = useRouter()

// Reactive state
const loading = ref(false)
const gameStats = ref(null)
const revenueChart = ref(null)
const productivityChart = ref(null)
const statusPieChart = ref(null)
const hourlyChart = ref(null)

// Charts instances
let revenueChartInstance = null
let productivityChartInstance = null
let statusPieChartInstance = null
let hourlyChartInstance = null

// Computed properties
const moneyColorClass = computed(() => {
  if (!gameStats.value) return ''
  const money = gameStats.value.currentMoney
  if (money < 0) return 'text-red-600'
  if (money < 1000) return 'text-yellow-600'
  return 'text-green-600'
})

const netProfit = computed(() => {
  if (!gameStats.value) return 0
  return gameStats.value.totalRevenue - gameStats.value.totalCosts
})

const profitColorClass = computed(() => {
  const profit = netProfit.value
  if (profit < 0) return 'text-red-600'
  if (profit > 0) return 'text-green-600'
  return 'text-gray-600'
})

const profitMarginPercentage = computed(() => {
  if (!gameStats.value || gameStats.value.totalRevenue === 0) return 0
  return Math.round((netProfit.value / gameStats.value.totalRevenue) * 100)
})

const profitMarginColorClass = computed(() => {
  const margin = profitMarginPercentage.value
  if (margin < 0) return 'text-red-600'
  if (margin < 10) return 'text-yellow-600'
  if (margin < 25) return 'text-blue-600'
  return 'text-green-600'
})

const profitMarginBarClass = computed(() => {
  const margin = profitMarginPercentage.value
  if (margin < 0) return 'bg-red-500'
  if (margin < 10) return 'bg-yellow-500'
  if (margin < 25) return 'bg-blue-500'
  return 'bg-green-500'
})

const moneyTrendIcon = computed(() => {
  if (!gameStats.value) return '➡️'
  const change = gameStats.value.currentMoney - gameStats.value.initialMoney
  if (change > 0) return '📈'
  if (change < 0) return '📉'
  return '➡️'
})

const moneyTrendClass = computed(() => {
  if (!gameStats.value) return ''
  const change = gameStats.value.currentMoney - gameStats.value.initialMoney
  if (change > 0) return 'text-green-600'
  if (change < 0) return 'text-red-600'
  return 'text-gray-600'
})

const moneyChangeText = computed(() => {
  if (!gameStats.value) return 'Stabile'
  const change = gameStats.value.currentMoney - gameStats.value.initialMoney
  if (change === 0) return 'Stabile'
  const sign = change > 0 ? '+' : ''
  return `${sign}${formatCurrency(change)}`
})

const projectSuccessRate = computed(() => {
  if (!gameStats.value) return 0
  const total = gameStats.value.projectsCompleted + gameStats.value.projectsFailed
  if (total === 0) return 100
  return Math.round((gameStats.value.projectsCompleted / total) * 100)
})

const averageProjectValue = computed(() => {
  if (!gameStats.value || gameStats.value.projectsCompleted === 0) return 0
  return Math.round(gameStats.value.totalRevenue / gameStats.value.projectsCompleted)
})

const projectsPerWeek = computed(() => {
  if (!gameStats.value) return 0
  const weeks = Math.max(1, gameStats.value.totalPlayTime / (7 * 24 * 60 * 60 * 1000))
  return (gameStats.value.projectsCompleted / weeks).toFixed(1)
})

const developersEfficiency = computed(() => {
  if (!gameStats.value || gameStats.value.developersCount === 0) return 0
  return Math.round((gameStats.value.projectsCompleted / gameStats.value.developersCount) * 10)
})

const salesEfficiency = computed(() => {
  if (!gameStats.value || gameStats.value.salesPeopleCount === 0) return 0
  return Math.round((gameStats.value.projectsGenerated / gameStats.value.salesPeopleCount) * 10)
})

const overallPerformanceScore = computed(() => {
  if (!gameStats.value) return 0
  
  const profitScore = Math.min(100, Math.max(0, profitMarginPercentage.value + 50))
  const efficiencyScore = (developersEfficiency.value + salesEfficiency.value) / 2
  const growthScore = Math.min(100, (gameStats.value.currentMoney / gameStats.value.initialMoney - 1) * 50 + 50)
  
  return Math.round((profitScore * 0.4 + efficiencyScore * 0.3 + growthScore * 0.3))
})

const performanceRating = computed(() => {
  const score = overallPerformanceScore.value
  if (score >= 90) return 'Eccellente'
  if (score >= 75) return 'Molto Buono'
  if (score >= 60) return 'Buono'
  if (score >= 40) return 'Sufficiente'
  return 'Da Migliorare'
})

const performanceScoreClass = computed(() => {
  const score = overallPerformanceScore.value
  if (score >= 75) return 'border-green-500 text-green-600'
  if (score >= 60) return 'border-blue-500 text-blue-600'
  if (score >= 40) return 'border-yellow-500 text-yellow-600'
  return 'border-red-500 text-red-600'
})

const operationalEfficiency = computed(() => {
  return Math.min(100, (developersEfficiency.value + salesEfficiency.value) / 2)
})

const revenueGrowth = computed(() => {
  if (!gameStats.value) return 0
  const growth = ((gameStats.value.currentMoney / gameStats.value.initialMoney) - 1) * 100
  return Math.max(0, Math.min(100, growth))
})

const teamSatisfaction = computed(() => {
  return Math.min(100, (operationalEfficiency.value + revenueGrowth.value) / 2)
})

const averageProjectTime = computed(() => {
  if (!gameStats.value || gameStats.value.projectsCompleted === 0) return 0
  return Math.round(gameStats.value.totalPlayTime / gameStats.value.projectsCompleted)
})

const revenueStats = computed(() => ({
  peak: gameStats.value ? Math.max(gameStats.value.currentMoney, gameStats.value.totalRevenue * 0.3) : 0,
  weeklyAverage: gameStats.value ? Math.round(gameStats.value.totalRevenue / Math.max(1, projectsPerWeek.value)) : 0,
  trend: revenueGrowth.value > 10 ? 'Crescita' : revenueGrowth.value < -5 ? 'Declino' : 'Stabile'
}))

const revenueTrendClass = computed(() => {
  const trend = revenueStats.value.trend
  if (trend === 'Crescita') return 'text-green-600'
  if (trend === 'Declino') return 'text-red-600'
  return 'text-gray-600'
})

const revenueTrendText = computed(() => revenueStats.value.trend)

const productivityInsights = computed(() => ({
  developers: {
    score: developersEfficiency.value,
    description: developersEfficiency.value > 70 ? 'Ottima produttività' : 'Può migliorare',
    trend: developersEfficiency.value > 70 ? 'insight-item--positive' : 'insight-item--neutral'
  },
  sales: {
    score: salesEfficiency.value,
    description: salesEfficiency.value > 70 ? 'Eccellenti risultati' : 'Servono più progetti',
    trend: salesEfficiency.value > 70 ? 'insight-item--positive' : 'insight-item--neutral'
  }
}))

const complexityBreakdown = computed(() => {
  if (!gameStats.value) return []
  
  const total = gameStats.value.projectsCompleted
  return [
    {
      level: 1,
      label: 'Facile',
      count: Math.round(total * 0.4),
      percentage: 40,
      totalValue: gameStats.value.totalRevenue * 0.2,
      averageValue: gameStats.value.totalRevenue * 0.2 / Math.max(1, Math.round(total * 0.4))
    },
    {
      level: 2,
      label: 'Medio',
      count: Math.round(total * 0.35),
      percentage: 35,
      totalValue: gameStats.value.totalRevenue * 0.35,
      averageValue: gameStats.value.totalRevenue * 0.35 / Math.max(1, Math.round(total * 0.35))
    },
    {
      level: 3,
      label: 'Difficile',
      count: Math.round(total * 0.25),
      percentage: 25,
      totalValue: gameStats.value.totalRevenue * 0.45,
      averageValue: gameStats.value.totalRevenue * 0.45 / Math.max(1, Math.round(total * 0.25))
    }
  ]
})

const statusBreakdown = computed(() => [
  { name: 'completed', label: 'Completati', count: gameStats.value?.projectsCompleted || 0, color: '#22c55e' },
  { name: 'active', label: 'In Corso', count: gameStats.value?.projectsActive || 0, color: '#3b82f6' },
  { name: 'pending', label: 'In Attesa', count: gameStats.value?.projectsPending || 0, color: '#f59e0b' },
  { name: 'failed', label: 'Falliti', count: gameStats.value?.projectsFailed || 0, color: '#ef4444' }
])

const achievedMilestones = computed(() => {
  if (!gameStats.value) return []
  
  const milestones = []
  const now = new Date()
  
  if (gameStats.value.projectsCompleted >= 1) {
    milestones.push({
      id: 'first-project',
      title: 'Primo Progetto Completato',
      icon: '🎉',
      achievedAt: new Date(now.getTime() - (gameStats.value.projectsCompleted * 24 * 60 * 60 * 1000)),
      isRecent: gameStats.value.projectsCompleted === 1
    })
  }
  
  if (gameStats.value.projectsCompleted >= 10) {
    milestones.push({
      id: 'ten-projects',
      title: '10 Progetti Completati',
      icon: '🏆',
      achievedAt: new Date(now.getTime() - ((gameStats.value.projectsCompleted - 10) * 24 * 60 * 60 * 1000)),
      isRecent: gameStats.value.projectsCompleted >= 10 && gameStats.value.projectsCompleted <= 12
    })
  }
  
  if (gameStats.value.currentMoney > gameStats.value.initialMoney * 2) {
    milestones.push({
      id: 'doubled-money',
      title: 'Patrimonio Raddoppiato',
      icon: '💰',
      achievedAt: new Date(now.getTime() - (2 * 24 * 60 * 60 * 1000)),
      isRecent: gameStats.value.currentMoney < gameStats.value.initialMoney * 2.5
    })
  }
  
  if ((gameStats.value.developersCount + gameStats.value.salesPeopleCount) >= 5) {
    milestones.push({
      id: 'team-five',
      title: 'Team di 5 Persone',
      icon: '👥',
      achievedAt: new Date(now.getTime() - (3 * 24 * 60 * 60 * 1000)),
      isRecent: (gameStats.value.developersCount + gameStats.value.salesPeopleCount) <= 6
    })
  }
  
  return milestones.sort((a, b) => b.achievedAt - a.achievedAt)
})

const recommendations = computed(() => {
  if (!gameStats.value) return []
  
  const recs = []
  
  if (gameStats.value.currentMoney < 2000) {
    recs.push({
      id: 'urgent-money',
      title: 'Situazione Finanziaria Critica',
      description: 'Il patrimonio è molto basso. Concentrati sul completamento rapido dei progetti in corso e riduci i costi non essenziali.',
      icon: '🚨',
      priority: 'high',
      priorityLabel: 'Urgente',
      action: () => router.push('/game/production'),
      actionText: 'Vai alla Produzione',
      actionVariant: 'danger'
    })
  }
  
  if (developersEfficiency.value < 50) {
    recs.push({
      id: 'developer-efficiency',
      title: 'Migliora Efficienza Sviluppatori',
      description: 'Gli sviluppatori potrebbero essere più produttivi. Considera di assegnare progetti più adatti alle loro competenze.',
      icon: '⚡',
      priority: 'medium',
      priorityLabel: 'Importante',
      action: () => router.push('/game/production'),
      actionText: 'Ottimizza Assegnazioni',
      actionVariant: 'warning'
    })
  }
  
  if (gameStats.value.projectsPending === 0 && gameStats.value.salesPeopleCount > 0) {
    recs.push({
      id: 'generate-projects',
      title: 'Genera Nuovi Progetti',
      description: 'Non hai progetti in attesa. Fai lavorare i tuoi commerciali per generare nuove opportunità.',
      icon: '💼',
      priority: 'medium',
      priorityLabel: 'Consigliato',
      action: () => router.push('/game/sales'),
      actionText: 'Vai ai Sales',
      actionVariant: 'primary'
    })
  }
  
  if (gameStats.value.currentMoney > 10000 && (gameStats.value.developersCount + gameStats.value.salesPeopleCount) < 5) {
    recs.push({
      id: 'expand-team',
      title: 'Espandi il Team',
      description: 'Hai buone disponibilità finanziarie. Considera di assumere nuovo personale per accelerare la crescita.',
      icon: '📈',
      priority: 'low',
      priorityLabel: 'Opportunità',
      action: () => router.push('/game/hr'),
      actionText: 'Assumi Personale',
      actionVariant: 'success'
    })
  }
  
  if (profitMarginPercentage.value < 15 && profitMarginPercentage.value > 0) {
    recs.push({
      id: 'improve-margin',
      title: 'Migliora Margine di Profitto',
      description: 'Il margine di profitto è basso. Cerca di ottimizzare i costi o puntare su progetti di valore maggiore.',
      icon: '💡',
      priority: 'medium',
      priorityLabel: 'Consigliato',
      action: null,
      actionText: null,
      actionVariant: null
    })
  }
  
  return recs.sort((a, b) => {
    const priorityOrder = { high: 3, medium: 2, low: 1 }
    return priorityOrder[b.priority] - priorityOrder[a.priority]
  })
})

// Methods
const loadGameStats = async () => {
  loading.value = true
  
  try {
    const gameId = route.params.id || gameStore.currentGame?.id
    
    if (!gameId) {
      notificationStore.error('Nessuna partita selezionata')
      router.push({ name: 'GameList' })
      return
    }
    
    const response = await gameStore.getGameStats(gameId)
    gameStats.value = response
    
    await nextTick()
    initializeCharts()
    
  } catch (error) {
    console.error('Error loading game stats:', error)
    notificationStore.error('Errore nel caricamento delle statistiche')
  } finally {
    loading.value = false
  }
}

const refreshStats = async () => {
  await loadGameStats()
  notificationStore.success('Statistiche aggiornate!')
}

const exportStats = () => {
  if (!gameStats.value) return
  
  const csvData = [
    ['Metrica', 'Valore'],
    ['Patrimonio Attuale', gameStats.value.currentMoney],
    ['Patrimonio Iniziale', gameStats.value.initialMoney],
    ['Revenue Totale', gameStats.value.totalRevenue],
    ['Costi Totali', gameStats.value.totalCosts],
    ['Profitto Netto', netProfit.value],
    ['Margine di Profitto (%)', profitMarginPercentage.value],
    ['Progetti Completati', gameStats.value.projectsCompleted],
    ['Progetti Attivi', gameStats.value.projectsActive],
    ['Progetti in Attesa', gameStats.value.projectsPending],
    ['Tasso di Successo (%)', projectSuccessRate.value],
    ['Sviluppatori', gameStats.value.developersCount],
    ['Commerciali', gameStats.value.salesPeopleCount],
    ['Performance Score', overallPerformanceScore.value]
  ]
  
  const csvContent = csvData.map(row => row.join(',')).join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  
  const link = document.createElement('a')
  link.href = url
  link.download = `stats-${gameStats.value.gameName || 'partita'}-${new Date().toISOString().split('T')[0]}.csv`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
  
  notificationStore.success('Report esportato con successo!')
}

const handleRecommendationAction = (recommendation) => {
  if (recommendation.action) {
    recommendation.action()
  }
}

const formatPlayTime = (milliseconds) => {
  if (!milliseconds) return '0 minuti'
  
  const hours = Math.floor(milliseconds / (1000 * 60 * 60))
  const minutes = Math.floor((milliseconds % (1000 * 60 * 60)) / (1000 * 60))
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes} minuti`
}

const initializeCharts = async () => {
  if (typeof Chart === 'undefined') {
    console.warn('Chart.js not loaded, charts will not be displayed')
    return
  }
  
  if (revenueChart.value) {
    const ctx = revenueChart.value.getContext('2d')
    revenueChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: generateTimeLabels(),
        datasets: [{
          label: 'Revenue',
          data: generateRevenueData(),
          borderColor: '#14b8a6',
          backgroundColor: 'rgba(20, 184, 166, 0.1)',
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return formatCurrency(value)
              }
            }
          }
        }
      }
    })
  }
  
  if (productivityChart.value) {
    const ctx = productivityChart.value.getContext('2d')
    productivityChartInstance = new Chart(ctx, {
      type: 'radar',
      data: {
        labels: ['Sviluppatori', 'Commerciali', 'Progetti', 'Efficienza', 'Crescita'],
        datasets: [{
          label: 'Performance',
          data: [
            developersEfficiency.value,
            salesEfficiency.value,
            Math.min(100, gameStats.value.projectsCompleted * 5),
            operationalEfficiency.value,
            revenueGrowth.value
          ],
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.2)'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          r: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    })
  }
  
  if (statusPieChart.value) {
    const ctx = statusPieChart.value.getContext('2d')
    statusPieChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: statusBreakdown.value.map(s => s.label),
        datasets: [{
          data: statusBreakdown.value.map(s => s.count),
          backgroundColor: statusBreakdown.value.map(s => s.color)
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        }
      }
    })
  }
  
  if (hourlyChart.value) {
    const ctx = hourlyChart.value.getContext('2d')
    hourlyChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: Array.from({ length: 24 }, (_, i) => `${i}:00`),
        datasets: [{
          label: 'Produttività',
          data: generateHourlyData(),
          backgroundColor: 'rgba(34, 197, 94, 0.6)'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    })
  }
}

const generateTimeLabels = () => {
  const labels = []
  const now = new Date()
  for (let i = 6; i >= 0; i--) {
    const date = new Date(now.getTime() - i * 24 * 60 * 60 * 1000)
    labels.push(date.toLocaleDateString('it-IT', { weekday: 'short' }))
  }
  return labels
}

const generateRevenueData = () => {
  if (!gameStats.value) return []
  
  const baseRevenue = gameStats.value.totalRevenue / 7
  return Array.from({ length: 7 }, (_, i) => {
    return Math.round(baseRevenue * (0.7 + Math.random() * 0.6))
  })
}

const generateHourlyData = () => {
  return Array.from({ length: 24 }, (_, i) => {
    if (i >= 9 && i <= 18) {
      return Math.round(60 + Math.random() * 40)
    } else if (i >= 19 && i <= 22) {
      return Math.round(20 + Math.random() * 30)
    } else {
      return Math.round(Math.random() * 15)
    }
  })
}

const destroyCharts = () => {
  if (revenueChartInstance) {
    revenueChartInstance.destroy()
    revenueChartInstance = null
  }
  if (productivityChartInstance) {
    productivityChartInstance.destroy()
    productivityChartInstance = null
  }
  if (statusPieChartInstance) {
    statusPieChartInstance.destroy()
    statusPieChartInstance = null
  }
  if (hourlyChartInstance) {
    hourlyChartInstance.destroy()
    hourlyChartInstance = null
  }
}

// Lifecycle
onMounted(() => {
  loadGameStats()
})

onUnmounted(() => {
  destroyCharts()
})
</script>