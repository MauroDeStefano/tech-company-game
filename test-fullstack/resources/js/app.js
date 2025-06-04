import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.js';

import { createApp } from 'vue';

// Import main App component
import App from './App.vue';

// Create and mount Vue app
const app = createApp(App);
app.mount('#app');
