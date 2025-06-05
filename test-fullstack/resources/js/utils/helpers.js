/**
 * Genera un ID univoco con prefisso opzionale
 * @param {string} prefix - Prefisso per l'ID
 * @returns {string} ID univoco
 */
export function generateId(prefix = 'id') {
  const timestamp = Date.now().toString(36)
  const randomStr = Math.random().toString(36).substring(2, 15)
  return `${prefix}-${timestamp}-${randomStr}`
}

/**
 * Debounce function per limitare le chiamate frequenti
 * @param {Function} func - Funzione da chiamare
 * @param {number} wait - Millisecondi di attesa
 * @param {boolean} immediate - Se eseguire immediatamente
 * @returns {Function} Funzione con debounce
 */
export function debounce(func, wait, immediate = false) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      timeout = null
      if (!immediate) func(...args)
    }
    const callNow = immediate && !timeout
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
    if (callNow) func(...args)
  }
}

/**
 * Throttle function per limitare la frequenza di esecuzione
 * @param {Function} func - Funzione da chiamare
 * @param {number} limit - Millisecondi tra chiamate
 * @returns {Function} Funzione con throttle
 */
export function throttle(func, limit) {
  let inThrottle
  return function (...args) {
    if (!inThrottle) {
      func.apply(this, args)
      inThrottle = true
      setTimeout(() => (inThrottle = false), limit)
    }
  }
}

/**
 * Formatta un numero come valuta
 * @param {number} amount - Importo da formattare
 * @param {string} currency - Codice valuta (default: EUR)
 * @param {string} locale - Locale per la formattazione (default: it-IT)
 * @returns {string} Importo formattato
 */
export function formatCurrency(amount, currency = 'EUR', locale = 'it-IT') {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

/**
 * Formatta una data in formato leggibile
 * @param {Date|string} date - Data da formattare
 * @param {object} options - Opzioni di formattazione
 * @returns {string} Data formattata
 */
export function formatDate(date, options = {}) {
  const defaultOptions = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    ...options
  }

  return new Intl.DateTimeFormat('it-IT', defaultOptions).format(new Date(date))
}

/**
 * Formatta una data relativa (es: "2 ore fa")
 * @param {Date|string} date - Data da formattare
 * @returns {string} Data relativa
 */
export function formatRelativeTime(date) {
  const now = new Date()
  const then = new Date(date)
  const diffInSeconds = Math.floor((now - then) / 1000)

  if (diffInSeconds < 60) return 'Ora'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minuti fa`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} ore fa`
  if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} giorni fa`
  if (diffInSeconds < 31536000) return `${Math.floor(diffInSeconds / 2592000)} mesi fa`

  return `${Math.floor(diffInSeconds / 31536000)} anni fa`
}

/**
 * Capitalizza la prima lettera di una stringa
 * @param {string} str - Stringa da capitalizzare
 * @returns {string} Stringa capitalizzata
 */
export function capitalize(str) {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
}

/**
 * Tronca una stringa alla lunghezza specificata
 * @param {string} str - Stringa da troncare
 * @param {number} length - Lunghezza massima
 * @param {string} suffix - Suffisso da aggiungere (default: '...')
 * @returns {string} Stringa troncata
 */
export function truncate(str, length, suffix = '...') {
  if (!str || str.length <= length) return str
  return str.substring(0, length) + suffix
}

/**
 * Converte una stringa in slug URL-friendly
 * @param {string} str - Stringa da convertire
 * @returns {string} Slug generato
 */
export function slugify(str) {
  return str
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

/**
 * Valida un indirizzo email
 * @param {string} email - Email da validare
 * @returns {boolean} True se valida
 */
export function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

/**
 * Copia testo negli appunti
 * @param {string} text - Testo da copiare
 * @returns {Promise<boolean>} Promise che risolve con successo/errore
 */
export async function copyToClipboard(text) {
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text)
      return true
    } else {
      // Fallback per browser più vecchi
      const textArea = document.createElement('textarea')
      textArea.value = text
      textArea.style.position = 'fixed'
      textArea.style.left = '-999999px'
      textArea.style.top = '-999999px'
      document.body.appendChild(textArea)
      textArea.focus()
      textArea.select()
      const successful = document.execCommand('copy')
      document.body.removeChild(textArea)
      return successful
    }
  } catch (error) {
    console.error('Errore copia negli appunti:', error)
    return false
  }
}

/**
 * Ottiene la larghezza della scrollbar del browser
 * @returns {number} Larghezza in pixel
 */
export function getScrollbarWidth() {
  const scrollDiv = document.createElement('div')
  scrollDiv.style.cssText = 'width: 100px; height: 100px; overflow: scroll; position: absolute; top: -9999px;'
  document.body.appendChild(scrollDiv)
  const scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
  document.body.removeChild(scrollDiv)
  return scrollbarWidth
}

/**
 * Verifica se l'elemento è visibile nel viewport
 * @param {Element} element - Elemento da controllare
 * @returns {boolean} True se visibile
 */
export function isElementInViewport(element) {
  const rect = element.getBoundingClientRect()
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  )
}

/**
 * Smooth scroll verso un elemento
 * @param {Element|string} target - Elemento o selettore CSS
 * @param {object} options - Opzioni di scroll
 */
export function smoothScrollTo(target, options = {}) {
  const element = typeof target === 'string' ? document.querySelector(target) : target
  if (!element) return

  const defaultOptions = {
    behavior: 'smooth',
    block: 'start',
    inline: 'nearest',
    ...options
  }

  element.scrollIntoView(defaultOptions)
}

/**
 * Genera un colore casuale in formato hex
 * @returns {string} Colore hex
 */
export function randomColor() {
  return '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0')
}

/**
 * Converte i byte in formato leggibile
 * @param {number} bytes - Numero di byte
 * @param {number} decimals - Decimali da mostrare
 * @returns {string} Dimensione formattata
 */
export function formatBytes(bytes, decimals = 2) {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']

  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

/**
 * Controlla se il dispositivo è mobile
 * @returns {boolean} True se mobile
 */
export function isMobile() {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
}

/**
 * Controlla se il browser supporta una feature
 * @param {string} feature - Nome della feature
 * @returns {boolean} True se supportata
 */
export function supportsFeature(feature) {
  switch (feature) {
    case 'webp':
      return document.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') === 0
    case 'localStorage':
      try {
        const test = 'test'
        localStorage.setItem(test, test)
        localStorage.removeItem(test)
        return true
      } catch (e) {
        return false
      }
    case 'touchEvents':
      return 'ontouchstart' in window
    default:
      return false
  }
}

/**
 * Ritardo asincrono
 * @param {number} ms - Millisecondi di attesa
 * @returns {Promise} Promise che risolve dopo il ritardo
 */
export function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * Clamp di un valore tra min e max
 * @param {number} value - Valore da limitare
 * @param {number} min - Valore minimo
 * @param {number} max - Valore massimo
 * @returns {number} Valore limitato
 */
export function clamp(value, min, max) {
  return Math.min(Math.max(value, min), max)
}

/**
 * Interpola linearmente tra due valori
 * @param {number} start - Valore iniziale
 * @param {number} end - Valore finale
 * @param {number} factor - Fattore di interpolazione (0-1)
 * @returns {number} Valore interpolato
 */
export function lerp(start, end, factor) {
  return start + (end - start) * clamp(factor, 0, 1)
}

/**
 * Mappa un valore da un range a un altro
 * @param {number} value - Valore da mappare
 * @param {number} inMin - Minimo del range di input
 * @param {number} inMax - Massimo del range di input
 * @param {number} outMin - Minimo del range di output
 * @param {number} outMax - Massimo del range di output
 * @returns {number} Valore mappato
 */
export function mapRange(value, inMin, inMax, outMin, outMax) {
  return ((value - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin
}

/**
 * Genera un numero casuale tra min e max
 * @param {number} min - Valore minimo
 * @param {number} max - Valore massimo
 * @param {boolean} integer - Se restituire un intero
 * @returns {number} Numero casuale
 */
export function randomBetween(min, max, integer = false) {
  const value = Math.random() * (max - min) + min
  return integer ? Math.floor(value) : value
}

/**
 * Seleziona un elemento casuale da un array
 * @param {Array} array - Array da cui selezionare
 * @returns {*} Elemento selezionato
 */
export function randomChoice(array) {
  return array[Math.floor(Math.random() * array.length)]
}

/**
 * Mescola un array (Fisher-Yates shuffle)
 * @param {Array} array - Array da mescolare
 * @returns {Array} Array mescolato (nuovo array)
 */
export function shuffle(array) {
  const shuffled = [...array]
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
  }
  return shuffled
}

/**
 * Raggruppa elementi di un array per chiave
 * @param {Array} array - Array da raggruppare
 * @param {string|Function} key - Chiave o funzione per raggruppare
 * @returns {Object} Oggetto con gruppi
 */
export function groupBy(array, key) {
  return array.reduce((groups, item) => {
    const groupKey = typeof key === 'function' ? key(item) : item[key]
    if (!groups[groupKey]) {
      groups[groupKey] = []
    }
    groups[groupKey].push(item)
    return groups
  }, {})
}

/**
 * Rimuove duplicati da un array
 * @param {Array} array - Array da cui rimuovere duplicati
 * @param {string|Function} key - Chiave per confronto (opzionale)
 * @returns {Array} Array senza duplicati
 */
export function unique(array, key = null) {
  if (!key) {
    return [...new Set(array)]
  }

  const seen = new Set()
  return array.filter(item => {
    const value = typeof key === 'function' ? key(item) : item[key]
    if (seen.has(value)) {
      return false
    }
    seen.add(value)
    return true
  })
}

/**
 * Converte un oggetto in query string
 * @param {Object} params - Parametri da convertire
 * @returns {string} Query string
 */
export function objectToQueryString(params) {
  return Object.keys(params)
    .filter(key => params[key] !== null && params[key] !== undefined)
    .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
    .join('&')
}

/**
 * Converte una query string in oggetto
 * @param {string} queryString - Query string da convertire
 * @returns {Object} Oggetto con parametri
 */
export function queryStringToObject(queryString) {
  const params = new URLSearchParams(queryString.startsWith('?') ? queryString.slice(1) : queryString)
  const result = {}

  for (const [key, value] of params.entries()) {
    result[key] = value
  }

  return result
}

/**
 * Deep clone di un oggetto
 * @param {*} obj - Oggetto da clonare
 * @returns {*} Oggetto clonato
 */
export function deepClone(obj) {
  if (obj === null || typeof obj !== 'object') return obj
  if (obj instanceof Date) return new Date(obj.getTime())
  if (obj instanceof Array) return obj.map(item => deepClone(item))
  if (typeof obj === 'object') {
    const cloned = {}
    for (const key in obj) {
      if (obj.hasOwnProperty(key)) {
        cloned[key] = deepClone(obj[key])
      }
    }
    return cloned
  }
}

/**
 * Merge profondo di oggetti
 * @param {Object} target - Oggetto target
 * @param {...Object} sources - Oggetti sorgente
 * @returns {Object} Oggetto merged
 */
export function deepMerge(target, ...sources) {
  if (!sources.length) return target
  const source = sources.shift()

  if (isObject(target) && isObject(source)) {
    for (const key in source) {
      if (isObject(source[key])) {
        if (!target[key]) Object.assign(target, { [key]: {} })
        deepMerge(target[key], source[key])
      } else {
        Object.assign(target, { [key]: source[key] })
      }
    }
  }

  return deepMerge(target, ...sources)
}

/**
 * Controlla se un valore è un oggetto
 * @param {*} item - Valore da controllare
 * @returns {boolean} True se è un oggetto
 */
function isObject(item) {
  return item && typeof item === 'object' && !Array.isArray(item)
}

/**
 * Ottiene un valore annidato da un oggetto usando dot notation
 * @param {Object} obj - Oggetto da cui estrarre
 * @param {string} path - Percorso dot notation (es: 'user.profile.name')
 * @param {*} defaultValue - Valore di default
 * @returns {*} Valore estratto
 */
export function get(obj, path, defaultValue = undefined) {
  const keys = path.split('.')
  let result = obj

  for (const key of keys) {
    if (result === null || result === undefined || !(key in result)) {
      return defaultValue
    }
    result = result[key]
  }

  return result
}

/**
 * Imposta un valore annidato in un oggetto usando dot notation
 * @param {Object} obj - Oggetto da modificare
 * @param {string} path - Percorso dot notation
 * @param {*} value - Valore da impostare
 * @returns {Object} Oggetto modificato
 */
export function set(obj, path, value) {
  const keys = path.split('.')
  const lastKey = keys.pop()
  let current = obj

  for (const key of keys) {
    if (!(key in current) || typeof current[key] !== 'object') {
      current[key] = {}
    }
    current = current[key]
  }

  current[lastKey] = value
  return obj
}

/**
 * Converte RGB in HEX
 * @param {number} r - Rosso (0-255)
 * @param {number} g - Verde (0-255)
 * @param {number} b - Blu (0-255)
 * @returns {string} Colore HEX
 */
export function rgbToHex(r, g, b) {
  return '#' + [r, g, b].map(x => {
    const hex = x.toString(16)
    return hex.length === 1 ? '0' + hex : hex
  }).join('')
}

/**
 * Converte HEX in RGB
 * @param {string} hex - Colore HEX
 * @returns {Object} Oggetto con r, g, b
 */
export function hexToRgb(hex) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null
}

/**
 * Calcola la luminosità di un colore
 * @param {string} hex - Colore HEX
 * @returns {number} Luminosità (0-255)
 */
export function colorLuminance(hex) {
  const rgb = hexToRgb(hex)
  if (!rgb) return 0

  // Formula per calcolare la luminosità percepita
  return 0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b
}

/**
 * Determina se usare testo bianco o nero su uno sfondo
 * @param {string} backgroundColor - Colore di sfondo HEX
 * @returns {string} 'white' o 'black'
 */
export function getContrastColor(backgroundColor) {
  const luminance = colorLuminance(backgroundColor)
  return luminance > 128 ? 'black' : 'white'
}

/**
 * Formatta un numero con separatori delle migliaia
 * @param {number} num - Numero da formattare
 * @param {string} locale - Locale per la formattazione
 * @returns {string} Numero formattato
 */
export function formatNumber(num, locale = 'it-IT') {
  return new Intl.NumberFormat(locale).format(num)
}

/**
 * Converte secondi in formato tempo leggibile
 * @param {number} seconds - Secondi da convertire
 * @returns {string} Tempo formattato (es: "2h 30m 15s")
 */
export function formatDuration(seconds) {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const remainingSeconds = Math.floor(seconds % 60)

  const parts = []
  if (hours > 0) parts.push(`${hours}h`)
  if (minutes > 0) parts.push(`${minutes}m`)
  if (remainingSeconds > 0 || parts.length === 0) parts.push(`${remainingSeconds}s`)

  return parts.join(' ')
}

/**
 * Calcola la percentuale di un valore
 * @param {number} value - Valore attuale
 * @param {number} total - Valore totale
 * @param {number} decimals - Decimali da mostrare
 * @returns {number} Percentuale
 */
export function calculatePercentage(value, total, decimals = 1) {
  if (total === 0) return 0
  return parseFloat(((value / total) * 100).toFixed(decimals))
}

/**
 * Crea un array di numeri in sequenza
 * @param {number} start - Numero iniziale
 * @param {number} end - Numero finale
 * @param {number} step - Incremento
 * @returns {Array} Array di numeri
 */
export function range(start, end, step = 1) {
  const result = []
  for (let i = start; i <= end; i += step) {
    result.push(i)
  }
  return result
}

/**
 * Crea chunks da un array
 * @param {Array} array - Array da dividere
 * @param {number} size - Dimensione dei chunks
 * @returns {Array} Array di chunks
 */
export function chunk(array, size) {
  const chunks = []
  for (let i = 0; i < array.length; i += size) {
    chunks.push(array.slice(i, i + size))
  }
  return chunks
}

/**
 * Escape HTML entities
 * @param {string} str - Stringa da escape
 * @returns {string} Stringa escaped
 */
export function escapeHtml(str) {
  const div = document.createElement('div')
  div.textContent = str
  return div.innerHTML
}

/**
 * Unescape HTML entities
 * @param {string} str - Stringa da unescape
 * @returns {string} Stringa unescaped
 */
export function unescapeHtml(str) {
  const div = document.createElement('div')
  div.innerHTML = str
  return div.textContent || div.innerText || ''
}