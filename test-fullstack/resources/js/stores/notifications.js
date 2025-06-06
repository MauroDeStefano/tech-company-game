import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

/**
 * Store Pinia per la gestione delle notifiche toast
 *
 * Pattern utilizzato: Composition API con defineStore
 * Basato sulle best practices di Pinia 2024 per Vue 3
 */
export const useNotificationStore = defineStore('notifications', () => {
  // ===== STATE =====
  const notifications = ref([])
  const maxNotifications = ref(5) // Limite massimo notifiche simultanee
  const defaultDuration = ref(5000) // 5 secondi default

  // ===== GETTERS =====
  const hasNotifications = computed(() => notifications.value.length > 0)

  const notificationsByType = computed(() => {
    return notifications.value.reduce((acc, notification) => {
      if (!acc[notification.type]) {
        acc[notification.type] = []
      }
      acc[notification.type].push(notification)
      return acc
    }, {})
  })

  const urgentNotifications = computed(() => {
    return notifications.value.filter(n => n.type === 'error' || n.type === 'warning')
  })

  // ===== ACTIONS =====

  /**
   * Genera un ID unico per le notifiche
   */
  function generateId() {
    return `notification-${Date.now()}-${Math.random().toString(36).substring(2)}`
  }

  /**
   * Aggiunge una nuova notifica
   * @param {Object} notification - Oggetto notifica
   * @param {string} notification.message - Messaggio da mostrare
   * @param {string} [notification.type='info'] - Tipo: success, error, warning, info
   * @param {string} [notification.title] - Titolo opzionale
   * @param {number} [notification.duration] - Durata in ms, null per persistente
   * @param {boolean} [notification.persistent=false] - Se true, non si chiude automaticamente
   * @param {string} [notification.icon] - Icona emoji opzionale
   * @param {Object} [notification.action] - Azione opzionale {text, handler}
   * @returns {string} ID della notifica creata
   */
  function add(notification) {
    const id = generateId()

    const newNotification = {
      id,
      message: notification.message || 'Notifica',
      type: notification.type || 'info',
      title: notification.title || null,
      icon: notification.icon || getDefaultIcon(notification.type),
      duration: notification.persistent ? null : (notification.duration ?? defaultDuration.value),
      persistent: notification.persistent || false,
      action: notification.action || null,
      createdAt: new Date().toISOString(),
      isVisible: true,
      timer: null
    }

    // Rimuovi notifiche eccedenti se raggiungiamo il limite
    if (notifications.value.length >= maxNotifications.value) {
      const oldestNotification = notifications.value[0]
      remove(oldestNotification.id)
    }

    // Aggiungi la nuova notifica
    notifications.value.push(newNotification)

    // Setup auto-dismiss se non è persistente
    if (!newNotification.persistent && newNotification.duration) {
      newNotification.timer = setTimeout(() => {
        remove(id)
      }, newNotification.duration)
    }

    console.log(`✅ Notification added: ${newNotification.type} - ${newNotification.message}`)
    return id
  }

  /**
   * Rimuove una notifica specifica
   * @param {string} id - ID della notifica da rimuovere
   */
  function remove(id) {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      const notification = notifications.value[index]

      // Pulisci il timer se esiste
      if (notification.timer) {
        clearTimeout(notification.timer)
      }

      // Rimuovi dall'array
      notifications.value.splice(index, 1)
      console.log(`🗑️ Notification removed: ${id}`)
    }
  }

  /**
   * Rimuove tutte le notifiche
   */
  function clear() {
    // Pulisci tutti i timer
    notifications.value.forEach(notification => {
      if (notification.timer) {
        clearTimeout(notification.timer)
      }
    })

    notifications.value = []
    console.log('🧹 All notifications cleared')
  }

  /**
   * Rimuove solo le notifiche di un tipo specifico
   * @param {string} type - Tipo di notifica da rimuovere
   */
  function clearByType(type) {
    const toRemove = notifications.value.filter(n => n.type === type)
    toRemove.forEach(notification => remove(notification.id))
    console.log(`🗑️ Cleared ${toRemove.length} notifications of type: ${type}`)
  }

  // ===== HELPER METHODS =====

  /**
   * Ottiene l'icona di default per un tipo di notifica
   * @param {string} type - Tipo di notifica
   * @returns {string} Emoji icona
   */
  function getDefaultIcon(type) {
    const icons = {
      success: '✅',
      error: '❌',
      warning: '⚠️',
      info: 'ℹ️'
    }
    return icons[type] || icons.info
  }

  /**
   * Ottiene il colore CSS per un tipo di notifica
   * @param {string} type - Tipo di notifica
   * @returns {string} Classe CSS Tailwind
   */
  function getNotificationColor(type) {
    const colors = {
      success: 'bg-success-50 border-success-200 text-success-800',
      error: 'bg-danger-50 border-danger-200 text-danger-800',
      warning: 'bg-warning-50 border-warning-200 text-warning-800',
      info: 'bg-blue-50 border-blue-200 text-blue-800'
    }
    return colors[type] || colors.info
  }

  // ===== CONVENIENCE METHODS =====

  /**
   * Mostra notifica di successo
   * @param {string} message - Messaggio
   * @param {Object} options - Opzioni aggiuntive
   */
  function success(message, options = {}) {
    return add({
      message,
      type: 'success',
      icon: '🎉',
      ...options
    })
  }

  /**
   * Mostra notifica di errore
   * @param {string} message - Messaggio
   * @param {Object} options - Opzioni aggiuntive
   */
  function error(message, options = {}) {
    return add({
      message,
      type: 'error',
      persistent: true, // Gli errori sono persistenti di default
      ...options
    })
  }

  /**
   * Mostra notifica di warning
   * @param {string} message - Messaggio
   * @param {Object} options - Opzioni aggiuntive
   */
  function warning(message, options = {}) {
    return add({
      message,
      type: 'warning',
      duration: 8000, // Durata più lunga per i warning
      ...options
    })
  }

  /**
   * Mostra notifica informativa
   * @param {string} message - Messaggio
   * @param {Object} options - Opzioni aggiuntive
   */
  function info(message, options = {}) {
    return add({
      message,
      type: 'info',
      ...options
    })
  }

  /**
   * Mostra notifica di loading
   * @param {string} message - Messaggio
   * @param {Object} options - Opzioni aggiuntive
   * @returns {string} ID della notifica (per rimuoverla quando il loading finisce)
   */
  function loading(message, options = {}) {
    return add({
      message,
      type: 'info',
      icon: '⏳',
      persistent: true, // I loading sono sempre persistenti
      ...options
    })
  }

  // ===== ADVANCED METHODS =====

  /**
   * Aggiorna una notifica esistente
   * @param {string} id - ID della notifica
   * @param {Object} updates - Oggetto con le proprietà da aggiornare
   */
  function update(id, updates) {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      Object.assign(notification, updates)
      console.log(`🔄 Notification updated: ${id}`)
    }
  }

  /**
   * Pausa l'auto-dismiss di una notifica
   * @param {string} id - ID della notifica
   */
  function pause(id) {
    const notification = notifications.value.find(n => n.id === id)
    if (notification && notification.timer) {
      clearTimeout(notification.timer)
      notification.timer = null
      console.log(`⏸️ Notification paused: ${id}`)
    }
  }

  /**
   * Riprende l'auto-dismiss di una notifica
   * @param {string} id - ID della notifica
   * @param {number} [remainingTime] - Tempo rimanente in ms
   */
  function resume(id, remainingTime = null) {
    const notification = notifications.value.find(n => n.id === id)
    if (notification && !notification.persistent) {
      const timeLeft = remainingTime || notification.duration
      notification.timer = setTimeout(() => {
        remove(id)
      }, timeLeft)
      console.log(`▶️ Notification resumed: ${id}`)
    }
  }

  /**
   * Imposta la configurazione globale
   * @param {Object} config - Configurazione
   * @param {number} [config.maxNotifications] - Numero massimo notifiche
   * @param {number} [config.defaultDuration] - Durata default in ms
   */
  function configure(config) {
    if (config.maxNotifications !== undefined) {
      maxNotifications.value = config.maxNotifications
    }
    if (config.defaultDuration !== undefined) {
      defaultDuration.value = config.defaultDuration
    }
    console.log('⚙️ Notification store configured:', config)
  }

  // ===== CLEANUP =====

  /**
   * Pulisce tutte le notifiche e timer quando lo store viene distrutto
   * Utile per evitare memory leaks
   */
  function $dispose() {
    clear()
  }

  // ===== RETURN INTERFACE =====
  return {
    // State
    notifications,
    maxNotifications,
    defaultDuration,

    // Getters
    hasNotifications,
    notificationsByType,
    urgentNotifications,

    // Core Actions
    add,
    remove,
    clear,
    clearByType,

    // Convenience Methods
    success,
    error,
    warning,
    info,
    loading,

    // Advanced Methods
    update,
    pause,
    resume,
    configure,

    // Helpers
    getDefaultIcon,
    getNotificationColor,

    // Cleanup
    $dispose
  }
})