export default [
    // Game selection and management
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

    // Main game interface with nested routes
    {
        path: '/game',
        component: () => import('@/layouts/LayoutGame.vue'),
        meta: {
            requiresAuth: true,
            requiresGame: true
        },
        children: [
            // Game Dashboard - Overview
            {
                path: '',
                name: 'GameDashboard',
                component: () => import('@/views/game/DashboardView.vue'),
                meta: {
                    title: 'Dashboard',
                    icon: '🏠'
                }
            },

            // Production Section
            {
                path: 'production',
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
                path: 'sales',
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
                path: 'hr',
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

    // Game creation
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

    // Game statistics and history
    {
        path: '/game/:id/stats',
        name: 'GameStats',
        component: () => import('@/views/game/GameStatsView.vue'),
        meta: {
            title: 'Statistiche Partita',
            requiresAuth: true,
            layout: 'LayoutDefault'
        }
    }
]