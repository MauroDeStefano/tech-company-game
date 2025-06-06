<template>
  <div class="welcome-view">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-background">
        <div class="hero-pattern"></div>
        <div class="hero-glow"></div>
      </div>

      <div class="hero-content">
        <div class="hero-text">
          <h1 class="hero-title">
            <span class="title-emoji">🏢</span>
            <span class="title-text">Tech Company Game</span>
          </h1>
          <p class="hero-subtitle">
            Gestisci la tua software house e porta il tuo team al successo
          </p>
          <p class="hero-description">
            Un videogioco gestionale dove dovrai bilanciare sviluppatori, commerciali e progetti
            per far crescere la tua azienda ed evitare il fallimento!
          </p>
        </div>

        <div class="hero-actions">
          <BaseButton
            v-if="!authStore.isAuthenticated"
            variant="primary"
            size="xl"
            icon="🚀"
            @click="getStarted"
            class="hero-cta"
          >
            Inizia a Giocare
          </BaseButton>

          <BaseButton
            v-else
            variant="primary"
            size="xl"
            icon="🎮"
            @click="goToGames"
            class="hero-cta"
          >
            Le Tue Partite
          </BaseButton>

          <BaseButton
            v-if="!authStore.isAuthenticated"
            variant="outline"
            size="lg"
            icon="📖"
            @click="scrollToFeatures"
            class="hero-secondary"
          >
            Scopri di Più
          </BaseButton>
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number">{{ formatNumber(totalGames) }}+</span>
            <span class="stat-label">Partite Create</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">{{ formatNumber(totalProjects) }}+</span>
            <span class="stat-label">Progetti Completati</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">{{ formatNumber(totalPlayers) }}+</span>
            <span class="stat-label">Giocatori Attivi</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
      <div class="section-container">
        <div class="section-header">
          <h2 class="section-title">Come Funziona</h2>
          <p class="section-subtitle">
            Gestisci ogni aspetto della tua software house attraverso tre aree principali
          </p>
        </div>

        <div class="features-grid">
          <!-- Production Feature -->
          <div class="feature-card feature-card--production">
            <div class="feature-icon">🏗️</div>
            <h3 class="feature-title">Produzione</h3>
            <p class="feature-description">
              Assegna progetti ai tuoi sviluppatori. Ogni progetto ha una complessità
              diversa e richiede competenze specifiche per essere completato.
            </p>
            <div class="feature-highlights">
              <div class="highlight-item">
                <span class="highlight-icon">⭐</span>
                <span class="highlight-text">5 livelli di seniority</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-icon">💰</span>
                <span class="highlight-text">Progetti di valore variabile</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-icon">⏱️</span>
                <span class="highlight-text">Timing realistici</span>
              </div>
            </div>
          </div>

          <!-- Sales Feature -->
          <div class="feature-card feature-card--sales">
            <div class="feature-icon">💼</div>
            <h3 class="feature-title">Sales & Marketing</h3>
            <p class="feature-description">
              I tuoi commerciali generano nuovi progetti. Più sono esperti,
              più velocemente trovano clienti e maggiore è il valore dei contratti.
            </p>
            <div class="feature-highlights">
              <div class="highlight-item">
                <span class="highlight-icon">📈</span>
                <span class="highlight-text">Crescita esponenziale</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-icon">🎯</span>
                <span class="highlight-text">Targeting clienti</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-icon">💎</span>
                <span class="highlight-text">Progetti premium</span>
              </div>
            </div>
          </div>

          <!-- HR Feature -->
          <div class="feature-card feature-card--hr">
            <div class="feature-icon">🧑‍💼</div>
            <h3 class="feature-title">Risorse Umane</h3>
            <p class="feature-description">
              Assumi nuovi talenti dal mercato del lavoro. Ogni persona ha
              costi diversi e competenze uniche che possono fare la differenza.
            </p>
            <div class="feature-highlights">
              <div class="highlight-item">
                <span class="highlight-icon">👥</span>
                <span class="highlight-text">Team scaling</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-icon">🔍</span>
                <span class="highlight-text">Selezione talenti</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-icon">📊</span>
                <span class="highlight-text">ROI del personale</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Game Mechanics Section -->
    <section class="mechanics-section">
      <div class="section-container">
        <div class="section-header">
          <h2 class="section-title">Meccaniche di Gioco</h2>
          <p class="section-subtitle">
            Strategia, timing e gestione delle risorse sono la chiave del successo
          </p>
        </div>

        <div class="mechanics-grid">
          <div class="mechanic-item">
            <div class="mechanic-icon">⚖️</div>
            <h4 class="mechanic-title">Bilanciamento</h4>
            <p class="mechanic-description">
              Trova l'equilibrio perfetto tra costi del personale e ricavi dai progetti
            </p>
          </div>

          <div class="mechanic-item">
            <div class="mechanic-icon">⏰</div>
            <h4 class="mechanic-title">Timing</h4>
            <p class="mechanic-description">
              Ogni progetto ha tempi di sviluppo realistici basati su complessità e seniority
            </p>
          </div>

          <div class="mechanic-item">
            <div class="mechanic-icon">📈</div>
            <h4 class="mechanic-title">Crescita</h4>
            <p class="mechanic-description">
              Espandi il team, aumenta i ricavi e diventa leader nel mercato tech
            </p>
          </div>

          <div class="mechanic-item">
            <div class="mechanic-icon">🎯</div>
            <h4 class="mechanic-title">Strategia</h4>
            <p class="mechanic-description">
              Pianifica assunzioni e progetti per massimizzare profitti ed efficienza
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Screenshots Section -->
    <section class="screenshots-section">
      <div class="section-container">
        <div class="section-header">
          <h2 class="section-title">Interfaccia di Gioco</h2>
          <p class="section-subtitle">
            Un'interfaccia moderna e intuitiva per gestire la tua software house
          </p>
        </div>

        <div class="screenshots-grid">
          <div class="screenshot-item">
            <div class="screenshot-placeholder">
              <div class="screenshot-icon">🏠</div>
              <span class="screenshot-label">Dashboard</span>
            </div>
            <p class="screenshot-description">
              Panoramica completa con statistiche in tempo reale
            </p>
          </div>

          <div class="screenshot-item">
            <div class="screenshot-placeholder">
              <div class="screenshot-icon">📊</div>
              <span class="screenshot-label">Produzione</span>
            </div>
            <p class="screenshot-description">
              Gestione progetti e sviluppatori con progress bar dinamiche
            </p>
          </div>

          <div class="screenshot-item">
            <div class="screenshot-placeholder">
              <div class="screenshot-icon">💼</div>
              <span class="screenshot-label">Sales</span>
            </div>
            <p class="screenshot-description">
              Monitoraggio commerciali e generazione nuovi progetti
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
      <div class="section-container">
        <div class="section-header">
          <h2 class="section-title">Cosa Dicono i Giocatori</h2>
        </div>

        <div class="testimonials-grid">
          <div class="testimonial-item">
            <div class="testimonial-content">
              <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
              <p class="testimonial-text">
                "Finalmente un gestionale che simula veramente la vita in una software house!
                Le meccaniche sono realistiche e coinvolgenti."
              </p>
            </div>
            <div class="testimonial-author">
              <div class="author-avatar">👨‍💻</div>
              <div class="author-info">
                <span class="author-name">Marco R.</span>
                <span class="author-role">Tech Lead</span>
              </div>
            </div>
          </div>

          <div class="testimonial-item">
            <div class="testimonial-content">
              <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
              <p class="testimonial-text">
                "Perfetto per capire le dinamiche di gestione di un team.
                Mi ha aiutato anche nel lavoro reale!"
              </p>
            </div>
            <div class="testimonial-author">
              <div class="author-avatar">👩‍💼</div>
              <div class="author-info">
                <span class="author-name">Sara M.</span>
                <span class="author-role">Project Manager</span>
              </div>
            </div>
          </div>

          <div class="testimonial-item">
            <div class="testimonial-content">
              <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
              <p class="testimonial-text">
                "Addictive! Una volta iniziato non riesco a smettere.
                L'interfaccia è bellissima e molto intuitiva."
              </p>
            </div>
            <div class="testimonial-author">
              <div class="author-avatar">👨‍🎓</div>
              <div class="author-info">
                <span class="author-name">Luca B.</span>
                <span class="author-role">Studente CS</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="section-container">
        <div class="cta-content">
          <h2 class="cta-title">Pronto a Costruire il Tuo Impero Tech?</h2>
          <p class="cta-description">
            Unisciti a migliaia di giocatori che stanno già gestendo le loro software house di successo
          </p>

          <div class="cta-actions">
            <BaseButton
              v-if="!authStore.isAuthenticated"
              variant="primary"
              size="xl"
              icon="🚀"
              @click="getStarted"
              class="cta-primary"
            >
              Inizia Gratis Ora
            </BaseButton>

            <BaseButton
              v-else
              variant="primary"
              size="xl"
              icon="🎮"
              @click="goToGames"
              class="cta-primary"
            >
              Vai alle Tue Partite
            </BaseButton>
          </div>

          <div class="cta-features">
            <div class="cta-feature">
              <span class="feature-check">✅</span>
              <span class="feature-text">Gratis per sempre</span>
            </div>
            <div class="cta-feature">
              <span class="feature-check">✅</span>
              <span class="feature-text">Nessun download richiesto</span>
            </div>
            <div class="cta-feature">
              <span class="feature-check">✅</span>
              <span class="feature-text">Salvataggio cloud</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="welcome-footer">
      <div class="footer-container">
        <div class="footer-content">
          <div class="footer-brand">
            <h3 class="footer-title">
              <span class="footer-icon">🏢</span>
              Tech Company Game
            </h3>
            <p class="footer-description">
              Il simulatore di gestione software house più realistico del web.
            </p>
          </div>

          <div class="footer-links">
            <div class="footer-section">
              <h4 class="footer-section-title">Gioco</h4>
              <ul class="footer-list">
                <li><a href="#features" class="footer-link">Come Funziona</a></li>
                <li><router-link to="/help" class="footer-link">Tutorial</router-link></li>
                <li><router-link to="/about" class="footer-link">About</router-link></li>
              </ul>
            </div>

            <div class="footer-section">
              <h4 class="footer-section-title">Account</h4>
              <ul class="footer-list">
                <li v-if="!authStore.isAuthenticated">
                  <router-link to="/register" class="footer-link">Registrati</router-link>
                </li>
                <li v-if="!authStore.isAuthenticated">
                  <router-link to="/login" class="footer-link">Accedi</router-link>
                </li>
                <li v-if="authStore.isAuthenticated">
                  <router-link to="/profile" class="footer-link">Profilo</router-link>
                </li>
                <li v-if="authStore.isAuthenticated">
                  <router-link to="/settings" class="footer-link">Impostazioni</router-link>
                </li>
              </ul>
            </div>

            <div class="footer-section">
              <h4 class="footer-section-title">Supporto</h4>
              <ul class="footer-list">
                <li><router-link to="/help" class="footer-link">Aiuto</router-link></li>
                <li><a href="mailto:support@techcompanygame.com" class="footer-link">Contatti</a></li>
                <li><a href="#" class="footer-link">Bug Report</a></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p class="footer-copyright">
            © 2025 Tech Company Game. Tutti i diritti riservati.
          </p>
          <div class="footer-legal">
            <a href="#" class="legal-link">Privacy Policy</a>
            <a href="#" class="legal-link">Termini di Servizio</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'

// Stores
const authStore = useAuthStore()
const router = useRouter()

// Reactive state
const totalGames = ref(1247)
const totalProjects = ref(8936)
const totalPlayers = ref(3421)

// Methods
const getStarted = () => {
  router.push({ name: 'Register' })
}

const goToGames = () => {
  router.push({ name: 'GameList' })
}

const scrollToFeatures = () => {
  const featuresSection = document.getElementById('features')
  if (featuresSection) {
    featuresSection.scrollIntoView({ behavior: 'smooth' })
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('it-IT').format(num)
}

// Lifecycle
onMounted(() => {
  // Animate numbers
  animateNumbers()
})

const animateNumbers = () => {
  const duration = 2000 // 2 seconds
  const startTime = Date.now()

  const finalGames = totalGames.value
  const finalProjects = totalProjects.value
  const finalPlayers = totalPlayers.value

  const animate = () => {
    const elapsed = Date.now() - startTime
    const progress = Math.min(elapsed / duration, 1)

    // Easing function for smooth animation
    const eased = 1 - Math.pow(1 - progress, 3)

    totalGames.value = Math.floor(finalGames * eased)
    totalProjects.value = Math.floor(finalProjects * eased)
    totalPlayers.value = Math.floor(finalPlayers * eased)

    if (progress < 1) {
      requestAnimationFrame(animate)
    }
  }

  // Reset to 0 and start animation
  totalGames.value = 0
  totalProjects.value = 0
  totalPlayers.value = 0

  requestAnimationFrame(animate)
}
</script>

<style scoped>
.welcome-view {
  @apply min-h-screen;
}

/* Hero Section */
.hero-section {
  @apply relative min-h-screen flex items-center justify-center;
  @apply bg-gradient-to-br from-brand-600 via-brand-700 to-brand-800;
  @apply overflow-hidden;
}

.hero-background {
  @apply absolute inset-0;
}

.hero-pattern {
  @apply absolute inset-0 opacity-10;
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='1'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.hero-glow {
  @apply absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2;
  @apply w-96 h-96 bg-white opacity-5 rounded-full blur-3xl;
}

.hero-content {
  @apply relative z-10 text-center px-4 max-w-4xl mx-auto;
}

.hero-text {
  @apply mb-8;
}

.hero-title {
  @apply text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6;
  @apply flex items-center justify-center flex-wrap;
}

.title-emoji {
  @apply text-5xl sm:text-6xl lg:text-7xl mr-4;
}

.title-text {
  @apply bg-gradient-to-r from-white to-brand-100 bg-clip-text text-transparent;
}

.hero-subtitle {
  @apply text-xl sm:text-2xl text-brand-100 mb-4 font-medium;
}

.hero-description {
  @apply text-lg text-brand-200 max-w-2xl mx-auto leading-relaxed;
}

.hero-actions {
  @apply flex flex-col sm:flex-row items-center justify-center gap-4 mb-12;
}

.hero-cta {
  @apply transform hover:scale-105 transition-transform duration-200;
}

.hero-secondary {
  @apply border-white text-white hover:bg-white hover:text-brand-600;
}

.hero-stats {
  @apply grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-2xl mx-auto;
}

.stat-item {
  @apply text-center;
}

.stat-number {
  @apply block text-3xl sm:text-4xl font-bold text-white mb-2;
}

.stat-label {
  @apply text-brand-200 text-sm uppercase tracking-wide;
}

/* Features Section */
.features-section {
  @apply py-20 bg-neutral-50;
}

.section-container {
  @apply max-w-6xl mx-auto px-4;
}

.section-header {
  @apply text-center mb-16;
}

.section-title {
  @apply text-3xl sm:text-4xl font-bold text-neutral-900 mb-4;
}

.section-subtitle {
  @apply text-lg text-neutral-600 max-w-2xl mx-auto;
}

.features-grid {
  @apply grid grid-cols-1 lg:grid-cols-3 gap-8;
}

.feature-card {
  @apply bg-white rounded-2xl p-8 shadow-lg border border-neutral-200;
  @apply transition-all duration-300 hover:shadow-xl hover:-translate-y-2;
}

.feature-card--production {
  @apply border-l-4 border-l-brand-500;
}

.feature-card--sales {
  @apply border-l-4 border-l-success-500;
}

.feature-card--hr {
  @apply border-l-4 border-l-warning-500;
}

.feature-icon {
  @apply text-4xl mb-4;
}

.feature-title {
  @apply text-xl font-bold text-neutral-900 mb-4;
}

.feature-description {
  @apply text-neutral-600 mb-6 leading-relaxed;
}

.feature-highlights {
  @apply space-y-3;
}

.highlight-item {
  @apply flex items-center;
}

.highlight-icon {
  @apply mr-2;
}

.highlight-text {
  @apply text-sm text-neutral-700;
}

/* Mechanics Section */
.mechanics-section {
  @apply py-20 bg-white;
}

.mechanics-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6;
}

.mechanic-item {
  @apply text-center p-6 rounded-xl;
  @apply bg-neutral-50 hover:bg-neutral-100 transition-colors duration-200;
}

.mechanic-icon {
  @apply text-3xl mb-4;
}

.mechanic-title {
  @apply text-lg font-semibold text-neutral-900 mb-3;
}

.mechanic-description {
  @apply text-neutral-600 text-sm leading-relaxed;
}

/* Screenshots Section */
.screenshots-section {
  @apply py-20 bg-neutral-100;
}

.screenshots-grid {
  @apply grid grid-cols-1 md:grid-cols-3 gap-8;
}

.screenshot-item {
  @apply text-center;
}

.screenshot-placeholder {
  @apply bg-white rounded-xl p-12 shadow-md mb-4;
  @apply border-2 border-dashed border-neutral-300;
  @apply flex flex-col items-center justify-center;
  @apply min-h-48;
}

.screenshot-icon {
  @apply text-4xl mb-3;
}

.screenshot-label {
  @apply text-lg font-semibold text-neutral-700;
}

.screenshot-description {
  @apply text-neutral-600;
}

/* Testimonials Section */
.testimonials-section {
  @apply py-20 bg-white;
}

.testimonials-grid {
  @apply grid grid-cols-1 md:grid-cols-3 gap-8;
}

.testimonial-item {
  @apply bg-neutral-50 rounded-xl p-6;
}

.testimonial-content {
  @apply mb-6;
}

.testimonial-stars {
  @apply text-yellow-500 mb-4;
}

.testimonial-text {
  @apply text-neutral-700 italic leading-relaxed;
}

.testimonial-author {
  @apply flex items-center;
}

.author-avatar {
  @apply text-2xl mr-3;
}

.author-info {
  @apply flex flex-col;
}

.author-name {
  @apply font-semibold text-neutral-900;
}

.author-role {
  @apply text-sm text-neutral-600;
}

/* CTA Section */
.cta-section {
  @apply py-20 bg-gradient-to-r from-brand-600 to-brand-700;
}

.cta-content {
  @apply text-center;
}

.cta-title {
  @apply text-3xl sm:text-4xl font-bold text-white mb-4;
}

.cta-description {
  @apply text-xl text-brand-100 mb-8 max-w-2xl mx-auto;
}

.cta-actions {
  @apply mb-8;
}

.cta-primary {
  @apply transform hover:scale-105 transition-transform duration-200;
}

.cta-features {
  @apply flex flex-col sm:flex-row items-center justify-center gap-6 text-brand-100;
}

.cta-feature {
  @apply flex items-center;
}

.feature-check {
  @apply mr-2;
}

.feature-text {
  @apply text-sm;
}

/* Footer */
.welcome-footer {
  @apply bg-neutral-900 text-white py-12;
}

.footer-container {
  @apply max-w-6xl mx-auto px-4;
}

.footer-content {
  @apply grid grid-cols-1 md:grid-cols-4 gap-8 mb-8;
}

.footer-brand {
  @apply md:col-span-1;
}

.footer-title {
  @apply text-xl font-bold mb-3 flex items-center;
}

.footer-icon {
  @apply mr-2;
}

.footer-description {
  @apply text-neutral-400 text-sm leading-relaxed;
}

.footer-links {
  @apply md:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-8;
}

.footer-section-title {
  @apply font-semibold mb-4;
}

.footer-list {
  @apply space-y-2;
}

.footer-link {
  @apply text-neutral-400 hover:text-white transition-colors duration-200 text-sm;
}

.footer-bottom {
  @apply border-t border-neutral-800 pt-8;
  @apply flex flex-col sm:flex-row items-center justify-between;
}

.footer-copyright {
  @apply text-neutral-500 text-sm mb-4 sm:mb-0;
}

.footer-legal {
  @apply flex space-x-6;
}

.legal-link {
  @apply text-neutral-500 hover:text-white transition-colors duration-200 text-sm;
}

/* Responsive */
@media (max-width: 640px) {
  .hero-title {
    @apply text-3xl flex-col;
  }

  .title-emoji {
    @apply text-4xl mr-0 mb-2;
  }

  .hero-subtitle {
    @apply text-lg;
  }

  .hero-description {
    @apply text-base;
  }

  .section-title {
    @apply text-2xl;
  }
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.feature-card,
.mechanic-item,
.testimonial-item {
  animation: fadeInUp 0.6s ease-out;
}

/* Scroll behavior */
html {
  scroll-behavior: smooth;
}
</style>