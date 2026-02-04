import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import('@/layouts/MainLayout.vue'),
            children: [
                {
                    path: '',
                    name: 'home',
                    component: () => import('@/views/HomeView.vue'),
                },
                {
                    path: 'chat/:id',
                    name: 'chat',
                    component: () => import('@/views/ChatView.vue'),
                },
                {
                    path: 'project/:id',
                    name: 'project',
                    component: () => import('@/views/ProjectView.vue'),
                },
                {
                    path: 'settings',
                    name: 'settings',
                    component: () => import('@/views/SettingsView.vue'),
                    meta: { requiresAuth: true },
                },
            ],
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/LoginView.vue'),
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('@/views/RegisterView.vue'),
        },
        {
            path: '/share/:id',
            name: 'share',
            component: () => import('@/views/SharedChatView.vue'),
        },
    ],
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login', query: { redirect: to.fullPath } })
    } else {
        next()
    }
})

export default router
