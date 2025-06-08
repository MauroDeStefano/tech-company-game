// src/js/router/game.js
export default [
    // Game selection and management (fuori dal layout game)
    {
        path: '/games',
        name: 'GameList',
        component: () => import('@/views/game/GameListView.vue'),
        meta: {
            title: 'Le Tue Partite',
            requiresAuth: true,
            layout: 'LayoutDefault'
        }
    },

    // Game creation (fuori dal layout game)
    {
        path: '/new-game',
        name: 'NewGame',
        component: () => import('@/views/game/NewGameView.vue'),
        meta: {
            title: 'Nuova Partita',
            requiresAuth: true,
            layout: 'LayoutDefault'
        }
    },
    {
        path: '/game',
        component: () => import('@/js/layouts/LayoutGame.vue'),
        meta: {
            requiresAuth: true,
            requiresGame: true
        },
        children: [
            {
                path: '', 
                redirect: 'dashboard'
            },

            // Game Dashboard - Overview (route principale)
            {
                path: 'dashboard',  // /game/dashboard
                name: 'GameDashboard',
                component: () => import('@/views/game/DashboardView.vue'),
                meta: {
                    title: 'Dashboard',
                    icon: '🏠',
                    description: 'Panoramica della tua software house'
                }
            },

            // Production Section
            {
                path: 'production',  // /game/production
                name: 'Production',
                component: () => import('@/views/game/ProductionView.vue'),
                meta: {
                    title: 'Produzione',
                    icon: '🏗️',
                    description: 'Gestisci sviluppatori e progetti'
                }
            },

            // Sales Section
            {
                path: 'sales',  // /game/sales
                name: 'Sales',
                component: () => import('@/views/game/SalesView.vue'),
                meta: {
                    title: 'Sales',
                    icon: '💼',
                    description: 'Gestisci commerciali e acquisizione clienti'
                }
            },

            // HR Section
            {
                path: 'hr',  // /game/hr
                name: 'HR',
                component: () => import('@/views/game/HRView.vue'),
                meta: {
                    title: 'Risorse Umane',
                    icon: '🧑‍💼',
                    description: 'Assumi nuovo personale'
                }
            }
        ]
    },

    // 🎯 AGGIUNTA: Game statistics (fuori dal layout game - pagina standalone)
    {
        path: '/game/:id/stats',
        name: 'GameStats',
        component: () => import('@/views/game/dashboard/GameStatsOverView.vue'),
        meta: {
            title: 'Statistiche Partita',
            requiresAuth: true,
            layout: 'LayoutDefault'
        }
    }
]