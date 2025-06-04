# 🎮 Tech Company Game - Videogioco Gestionale

Un videogioco gestionale completo dove l'utente gestisce una software house, bilanciando talenti e risorse per evitare la bancarotta.

## 🚀 Quick Start

### Prerequisiti
- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL
- Docker (opzionale)

### Setup Manuale (Raccomandato)

```bash
# Esegui lo script di setup automatico
chmod +x setup.sh
./setup.sh

# Avvia il database PostgreSQL
# Configura le credenziali in .env

# Avvia Vite (development)
npm run dev

# Avvia Laravel (nuovo terminale)
php artisan serve
```

### Setup con Docker (Alternativo)

```bash
# Avvia tutto con Docker
docker-compose up --build
```

### URLs Sviluppo
- **Applicazione**: http://localhost:8000
- **API**: http://localhost:8000/api
- **Database**: PostgreSQL su porta 5432

## 🏗️ Architettura

### Stack Integrato
- **Backend**: Laravel 11.x con API RESTful
- **Frontend**: Vue.js 3 integrato in Laravel
- **Database**: PostgreSQL 15
- **Build Tool**: Vite (al posto di Laravel Mix)
- **Styling**: Tailwind CSS
- **State Management**: Pinia
- **Router**: Vue Router 4

### Database Schema
```
games (partite)
├── developers (sviluppatori)
├── sales_people (commerciali)  
├── projects (progetti)
└── project_generations (generazioni progetti)
```

## 🎯 Funzionalità Principali

### 🏗️ Schermata Produzione
- Visualizza developers disponibili/occupati
- Gestisce progetti in pending e in corso
- Assegna developers ai progetti
- Calcola tempo completamento (complessità/seniority)

### 💼 Schermata Sales
- Gestisce commerciali disponibili
- Genera nuovi progetti
- Tempo generazione basato su esperienza
- Valore progetto calcolato dinamicamente

### 🧑‍💼 Schermata HR
- Mercato developers e commerciali
- Assunzione nuove risorse
- Aggiornamento patrimonio e costi

## 🛠️ Sviluppo

### Comandi Utili

```bash
# Rebuild completo
docker-compose down -v
docker-compose up --build

# Solo backend
docker-compose up postgres redis backend

# Solo frontend  
docker-compose up frontend

# Logs specifici
docker-compose logs -f backend
docker-compose logs -f frontend

# Accesso container
docker-compose exec backend bash
docker-compose exec frontend sh
```

### Laravel Commands

```bash
# Migrations
docker-compose exec backend php artisan migrate

# Seeders
docker-compose exec backend php artisan db:seed

# Tests
docker-compose exec backend php artisan test

# Cache clear
docker-compose exec backend php artisan cache:clear
```

### Vue.js Commands

```bash
# Install dependencies
docker-compose exec frontend npm install

# Run tests
docker-compose exec frontend npm run test

# Build production
docker-compose exec frontend npm run build
```

## 🧪 Testing

### Backend Tests (PHPUnit)
```bash
docker-compose exec backend php artisan test
```

### Frontend Tests (Vitest)
```bash
docker-compose exec frontend npm run test
```

## 📊 Meccaniche di Gioco

### Stato Iniziale
- 1 Developer (seniority base)
- 1 Commerciale (esperienza base)
- 5.000 EUR patrimonio

### Obiettivo
Evitare la bancarotta gestendo strategicamente team e progetti.

### Calcoli
- **Tempo Progetto**: `complessità_progetto / seniority_developer`
- **Valore Progetto**: `esperienza_commerciale * fattore_moltiplicativo`
- **Tempo Generazione**: Basato su esperienza commerciale

## 📁 Struttura Progetto

```
tech-company-game/
├── docker-compose.yml
├── setup.sh
├── README.md
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Models/
│   │   ├── Http/Controllers/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── tests/
└── frontend/               # Vue.js SPA
    ├── src/
    │   ├── components/
    │   ├── views/
    │   ├── stores/
    │   └── composables/
    └── tests/
```

## 🚀 Deployment

### Produzione
```bash
# Build immagini produzione
docker-compose -f docker-compose.prod.yml build

# Deploy
docker-compose -f docker-compose.prod.yml up -d
```

## 🐛 Troubleshooting

### Problemi Comuni

**Database non si connette:**
```bash
docker-compose down
docker volume rm tech-company-game_postgres_data
docker-compose up --build
```

**Frontend non raggiunge API:**
- Verifica CORS in `backend/config/cors.php`
- Controlla variabili ambiente in `.env`

**Permessi Laravel:**
```bash
docker-compose exec backend chown -R www-data:www-data storage bootstrap/cache
docker-compose exec backend chmod -R 775 storage bootstrap/cache
```

## 📈 Roadmap

- [x] Setup base progetto
- [ ] Models e Migrations
- [ ] API Endpoints
- [ ] Frontend Components
- [ ] Sistema di Tempo
- [ ] Test Suite
- [ ] Documentazione API
- [ ] Deploy Scripts

## 🤝 Contribuire

1. Fork del progetto
2. Crea feature branch (`git checkout -b feature/nuova-funzionalita`)
3. Commit changes (`git commit -am 'Aggiunge nuova funzionalità'`)
4. Push branch (`git push origin feature/nuova-funzionalita`)
5. Crea Pull Request

## 📄 Licenza

Questo progetto è sotto licenza MIT. Vedi `LICENSE` per dettagli.

---

**Developed with ❤️ for the Fullstack Senior Test**