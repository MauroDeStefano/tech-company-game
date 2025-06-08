export default [
    {
        path: '/login',
        name: 'Login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: {
            title: 'Accedi',
            isAuthPage: true,
            layout: 'LayoutAuth'
        }
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('@/views/auth/RegisterView.vue'),
        meta: {
            title: 'Registrati',
            isAuthPage: true,
            layout: 'LayoutAuth'
        }
    },
    {
        path: '/forgot-password',
        name: 'ForgotPassword',
        component: () => import('@/views/auth/ForgotPasswordView.vue'),
        meta: {
            title: 'Recupera Password',
            isAuthPage: true,
            layout: 'LayoutAuth'
        }
    },
    {
        path: '/reset-password',
        name: 'ResetPassword',
        component: () => import('@/views/auth/ResetPasswordView.vue'),
        meta: {
            title: 'Reimposta Password',
            isAuthPage: true,
            layout: 'LayoutAuth'
        }
    }
]