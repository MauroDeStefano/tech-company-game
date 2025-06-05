export default [
    // Welcome/Home page
    {
        path: '/welcome',
        name: 'Welcome',
        component: () => import('@/views/general/WelcomeView.vue'),
        meta: {
            title: 'Benvenuto in Tech Company Game',
            layout: 'LayoutDefault'
        }
    },

    // User profile
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('@/views/general/ProfileView.vue'),
        meta: {
            title: 'Il Tuo Profilo',
            requiresAuth: true,
            layout: 'LayoutDefault'
        }
    },

    // Settings
    {
        path: '/settings',
        name: 'Settings',
        component: () => import('@/views/general/SettingsView.vue'),
        meta: {
            title: 'Impostazioni',
            requiresAuth: true,
            layout: 'LayoutDefault'
        }
    },

    // About page
    {
        path: '/about',
        name: 'About',
        component: () => import('@/views/general/AboutView.vue'),
        meta: {
            title: 'Info sul Gioco',
            layout: 'LayoutDefault'
        }
    },

    // Help/Tutorial
    {
        path: '/help',
        name: 'Help',
        component: () => import('@/views/general/HelpView.vue'),
        meta: {
            title: 'Aiuto e Tutorial',
            layout: 'LayoutDefault'
        }
    },

    // Forbidden page
    {
        path: '/forbidden',
        name: 'Forbidden',
        component: () => import('@/views/general/ForbiddenView.vue'),
        meta: {
            title: 'Accesso Negato',
            layout: 'LayoutDefault'
        }
    }
]