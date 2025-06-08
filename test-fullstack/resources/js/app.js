import './bootstrap'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
// Import global CSS (will use our design system)
import '../css/app.css'

// Layout components
import LayoutDefault from './layouts/LayoutDefault.vue'
import LayoutAuth from './layouts/LayoutAuth.vue'
import LayoutGame from './layouts/LayoutGame.vue'

const baseComponents = import.meta.glob('./components/base/Base*.vue', { eager: true })
// Create Vue app
const app = createApp(App)

// Register global layouts
app.component('LayoutDefault', LayoutDefault)
app.component('LayoutAuth', LayoutAuth)
app.component('LayoutGame', LayoutGame)


for (const path in baseComponents) {
  const component = baseComponents[path].default
  app.component(component.name, component)
}
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