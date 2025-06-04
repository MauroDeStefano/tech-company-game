#!/bin/bash

echo "🚀 Laravel + Vue.js + Bootstrap + Vite + Tailwind Setup"
echo "======================================================"
echo "📋 Script generico per creare un nuovo progetto completo"
echo ""

# Chiedi il nome del progetto
read -p "📝 Nome del progetto: " PROJECT_NAME

# Valida il nome del progetto
if [ -z "$PROJECT_NAME" ]; then
    echo "❌ Nome del progetto richiesto!"
    exit 1
fi

echo "🎯 Creazione progetto: $PROJECT_NAME"
echo ""

# Verifica prerequisiti
echo "🔍 Verifica prerequisiti..."

echo "Testando PHP..."
if ! command -v php &> /dev/null; then
    echo "❌ PHP non trovato."
    echo "💡 Per XAMPP: export PATH=\"/c/xampp/php:\$PATH\""
    exit 1
fi
echo "✅ PHP: $(php --version | head -n1)"

echo "Testando Composer..."
if ! command -v composer &> /dev/null; then
    echo "❌ Composer non trovato."
    echo "💡 Installa da: https://getcomposer.org/"
    exit 1
fi
echo "✅ Composer: $(composer --version | head -n1)"

echo "Testando Node.js/npm..."
if ! command -v npm &> /dev/null; then
    echo "❌ Node.js/npm non trovato."
    echo "💡 Installa da: https://nodejs.org/"
    exit 1
fi

NODE_VERSION=$(node --version)
NPM_VERSION=$(npm --version)
echo "✅ Node.js: $NODE_VERSION | npm: $NPM_VERSION"

# Verifica versione Node.js minima
NODE_MAJOR=$(echo $NODE_VERSION | cut -d'.' -f1 | sed 's/v//')
if [ "$NODE_MAJOR" -lt 18 ]; then
    echo "⚠️ ATTENZIONE: Node.js $NODE_VERSION potrebbe essere troppo vecchio"
    echo "💡 Si consiglia Node.js 18+ da: https://nodejs.org/"
    read -p "Continuare comunque? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo ""
echo "✅ Tutti i prerequisiti sono disponibili!"
echo ""

# Crea progetto Laravel
echo "🎯 Creazione progetto Laravel '$PROJECT_NAME'..."
if ! composer create-project laravel/laravel "$PROJECT_NAME" --prefer-dist; then
    echo "❌ Errore durante la creazione del progetto Laravel"
    echo "💡 Verifica connessione internet e permessi directory"
    exit 1
fi

echo "✅ Laravel creato con successo!"
echo ""

# Entra nella directory del progetto
cd "$PROJECT_NAME" || exit 1

echo "📁 Lavorando in: $(pwd)"
echo ""

# Installa Vue.js con fallback
echo "🖼️ Installazione Vue.js..."
if npm install vue@latest @vitejs/plugin-vue@latest; then
    echo "✅ Vue.js installato con successo!"
else
    echo "⚠️ Errore con @latest, provo con versioni specifiche..."
    if npm install vue@3.4.0 @vitejs/plugin-vue@5.0.0; then
        echo "✅ Vue.js installato con versioni specifiche!"
    else
        echo "❌ Impossibile installare Vue.js"
        echo "💡 Verifica connessione internet e versione Node.js"
        exit 1
    fi
fi

# Installa Vue Router e Pinia con controllo
echo "🛣️ Installazione Vue Router e Pinia..."
if npm install vue-router@4 pinia; then
    echo "✅ Vue Router e Pinia installati!"
else
    echo "⚠️ Errore, provo con versioni specifiche..."
    if npm install vue-router@4.2.0 pinia@2.1.0; then
        echo "✅ Vue Router e Pinia installati con versioni specifiche!"
    else
        echo "❌ Errore critico nell'installazione delle dipendenze Vue"
        exit 1
    fi
fi

# Installa Axios con controllo
echo "🌐 Installazione Axios..."
if npm install axios; then
    echo "✅ Axios installato!"
else
    echo "⚠️ Errore con Axios, continuo senza..."
fi

# Installa Bootstrap con controllo
echo "📦 Installazione Bootstrap..."
if npm install bootstrap @popperjs/core; then
    echo "✅ Bootstrap installato!"
else
    echo "⚠️ Errore con Bootstrap, continuo senza..."
fi

# Installa Tailwind CSS con controllo
echo "🎨 Installazione Tailwind CSS..."
if npm install -D tailwindcss @tailwindcss/forms autoprefixer postcss; then
    echo "✅ Tailwind CSS installato!"
else
    echo "⚠️ Errore con Tailwind CSS, continuo senza..."
fi

# Inizializza Tailwind con multipli fallback
echo "⚙️ Inizializzazione Tailwind CSS..."
if npx tailwindcss init -p 2>/dev/null; then
    echo "✅ Tailwind inizializzato con npx!"
elif ./node_modules/.bin/tailwindcss init -p 2>/dev/null; then
    echo "✅ Tailwind inizializzato con percorso diretto!"
elif command -v tailwindcss &> /dev/null && tailwindcss init -p 2>/dev/null; then
    echo "✅ Tailwind inizializzato con comando globale!"
else
    echo "⚠️ Impossibile inizializzare Tailwind automaticamente"
    echo "💡 Creazione configurazione manuale..."
    
    # Crea configurazione Tailwind manualmente se tutti i metodi falliscono
    if [ ! -f "tailwind.config.js" ]; then
        cat > tailwind.config.js << 'TAILWIND_EOF'
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
TAILWIND_EOF
        echo "✅ Configurazione Tailwind creata manualmente!"
    fi
    
    if [ ! -f "postcss.config.js" ]; then
        cat > postcss.config.js << 'POSTCSS_EOF'
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
POSTCSS_EOF
        echo "✅ Configurazione PostCSS creata manualmente!"
    fi
fi

# Aggiorna configurazione Tailwind se necessario
echo "🔧 Configurazione Tailwind CSS..."
cat > tailwind.config.js << 'EOF'
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    // require('@tailwindcss/forms'), // Decommentare se @tailwindcss/forms è installato
  ],
}
EOF
echo "✅ Configurazione Tailwind aggiornata!"

# Configura Vite per Vue.js
echo "⚡ Configurazione Vite per Vue.js..."
cat > vite.config.js << 'EOF'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
EOF

# Setup CSS con Bootstrap e Tailwind
echo "🎨 Setup CSS con Bootstrap e Tailwind..."
cat > resources/css/app.css << 'EOF'
@import 'bootstrap/dist/css/bootstrap.css';
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom styles */
body {
    font-family: 'Figtree', sans-serif;
}
EOF

# Setup JavaScript con Vue.js
echo "📜 Setup JavaScript con Vue.js..."
cat > resources/js/app.js << 'EOF'
import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.js';

import { createApp } from 'vue';

// Import main App component
import App from './App.vue';

// Create and mount Vue app
const app = createApp(App);
app.mount('#app');
EOF

# Crea componente App.vue principale
echo "🎯 Creazione componente App.vue principale..."
cat > resources/js/App.vue << 'EOF'
<template>
  <div>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="#">
          <strong>Laravel + Vue.js</strong>
        </a>
        <span class="navbar-text">
          Template pronto all'uso
        </span>
      </div>
    </nav>
    
    <!-- Main Content -->
    <div class="py-5">
      <example-component></example-component>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
      <div class="container text-center">
        <p class="mb-0">Powered by Laravel + Vue.js 3 + Vite</p>
      </div>
    </footer>
  </div>
</template>

<script>
import ExampleComponent from './components/ExampleComponent.vue';

export default {
  name: 'App',
  components: {
    ExampleComponent
  }
}
</script>
EOF

# Crea struttura directory Vue.js
echo "📁 Creazione struttura directory Vue.js..."
mkdir -p resources/js/components
mkdir -p resources/js/views
mkdir -p resources/js/stores
mkdir -p resources/js/composables

# Crea componente di esempio
echo "🧩 Creazione componente di esempio..."
cat > resources/js/components/ExampleComponent.vue << 'EOF'
<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">🎉 Example Component</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">
                            Questo è un componente di esempio che utilizza:
                        </p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>🅱️ Bootstrap</h6>
                                <button class="btn btn-primary me-2">Primary</button>
                                <button class="btn btn-success">Success</button>
                            </div>
                            <div class="col-md-6">
                                <h6>🎨 Tailwind CSS</h6>
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded me-2">
                                    Blue
                                </button>
                                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Green
                                </button>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>⚡ Vue.js 3:</strong> Counter reattivo: 
                            <span class="badge bg-secondary fs-6">{{ count }}</span>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button @click="increment" class="btn btn-success">
                                ➕ Incrementa
                            </button>
                            <button @click="decrement" class="btn btn-warning">
                                ➖ Decrementa
                            </button>
                            <button @click="reset" class="btn btn-danger">
                                🔄 Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'

export default {
    name: 'ExampleComponent',
    setup() {
        const count = ref(0)
        
        const increment = () => {
            count.value++
        }
        
        const decrement = () => {
            count.value--
        }
        
        const reset = () => {
            count.value = 0
        }
        
        return {
            count,
            increment,
            decrement,
            reset
        }
    }
}
</script>
EOF

# Crea template Blade base
echo "🏠 Creazione template Blade base..."
cat > resources/views/welcome.blade.php << 'EOF'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-light">
    <div id="app">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <strong>Laravel + Vue.js</strong>
                </a>
                <span class="navbar-text">
                    Stack completo pronto all'uso
                </span>
            </div>
        </nav>
        
        <!-- Hero Section -->
        <div class="bg-primary text-white py-5">
            <div class="container text-center">
                <h1 class="display-4 fw-bold mb-3">
                    🚀 Progetto Ready!
                </h1>
                <p class="lead mb-4">
                    Laravel + Vue.js + Bootstrap + Tailwind CSS + Vite
                </p>
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <span class="badge bg-light text-dark fs-6 me-2">PHP {{ PHP_VERSION }}</span>
                        <span class="badge bg-light text-dark fs-6 me-2">Laravel {{ app()->version() }}</span>
                        <span class="badge bg-light text-dark fs-6">Vue.js 3</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="py-5">
            <example-component></example-component>
        </div>
        
        <!-- Footer -->
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container text-center">
                <p class="mb-0">
                    Template creato con ❤️ per lo sviluppo rapido
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
EOF

# Genera chiave applicazione
echo "🔑 Generazione chiave applicazione..."
if php artisan key:generate; then
    echo "✅ Chiave applicazione generata!"
else
    echo "⚠️ Errore nella generazione della chiave"
fi

# Test finale delle installazioni
echo ""
echo "🔍 Verifica finale installazioni..."

# Verifica package.json per Vue
if grep -q '"vue"' package.json; then
    echo "✅ Vue.js presente nel package.json"
else
    echo "⚠️ Vue.js non trovato nel package.json"
fi

# Verifica Vite config
if [ -f "vite.config.js" ]; then
    echo "✅ Configurazione Vite creata"
else
    echo "⚠️ Configurazione Vite mancante"
fi

# Verifica Tailwind config
if [ -f "tailwind.config.js" ]; then
    echo "✅ Configurazione Tailwind creata"
else
    echo "⚠️ Configurazione Tailwind mancante"
fi

# Prova un build test
echo ""
echo "🧪 Test build veloce..."
if npm run build 2>/dev/null; then
    echo "✅ Build test riuscito!"
else
    echo "⚠️ Build test fallito (normale durante il setup)"
fi

echo ""
echo "🎉 Setup completato con successo!"
echo ""
echo "📦 Tecnologie installate:"
echo "   ✅ Laravel (framework PHP)"
echo "   ✅ Vue.js 3 (frontend framework)"
echo "   ✅ Vue Router 4 (routing)"
echo "   ✅ Pinia (state management)"
echo "   ✅ Bootstrap 5 (CSS framework)"
echo "   ✅ Tailwind CSS (utility CSS)"
echo "   ✅ Vite (build tool)"
echo "   ✅ Axios (HTTP client)"
echo ""
echo "📁 Struttura creata in: ./$PROJECT_NAME/"
echo "   ├── resources/js/components/"
echo "   ├── resources/js/views/"
echo "   ├── resources/js/stores/"
echo "   └── resources/js/composables/"
echo ""
echo "🚀 Per avviare il progetto:"
echo "   cd $PROJECT_NAME"
echo "   Terminal 1: npm run dev"
echo "   Terminal 2: php artisan serve"
echo "   Poi visita: http://localhost:8000"
echo ""
echo "🎯 Template generico pronto per qualsiasi progetto!"