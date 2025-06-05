import './bootstrap'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'

// Import global CSS (will use our design system)
import '../css/app.css'

// Layout components
import LayoutDefault from './layouts/LayoutDefault.vue'
import LayoutAuth from './layouts/LayoutAuth.vue'
import LayoutGame from './layouts/LayoutGame.vue'

// Global base components
import BaseButton from './components/base/BaseButton.vue'
import BaseCard from './components/base/BaseCard.vue'
import BaseInput from './components/base/BaseInput.vue'
import BaseModal from './components/base/BaseModal.vue'
import BaseSpinner from './components/base/BaseSpinner.vue'

// Create Vue app
const app = createApp({})

// Register global layouts
app.component('LayoutDefault', LayoutDefault)
app.component('LayoutAuth', LayoutAuth)
app.component('LayoutGame', LayoutGame)

// Register global base components
app.component('BaseButton', BaseButton)
app.component('BaseCard', BaseCard)
app.component('BaseInput', BaseInput)
app.component('BaseModal', BaseModal)
app.component('BaseSpinner', BaseSpinner)

// Setup state management
const pinia = createPinia()
app.use(pinia)

// Setup routing
app.use(router)

// Mount to Laravel blade template
app.mount('#app')

// Global error handler
app.config.errorHandler = (err, vm, info) => {
    console.error('Vue error:', err, info)

    // In production, you might want to send errors to a logging service
    if (process.env.NODE_ENV === 'production') {
        // Send to error tracking service (Sentry, Bugsnag, etc.)
        console.log('Error would be sent to logging service in production')
    }
}