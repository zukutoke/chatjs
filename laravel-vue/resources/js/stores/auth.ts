import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    const token = ref<string | null>(localStorage.getItem('auth_token'))
    const loading = ref(false)

    const isAuthenticated = computed(() => !!token.value && !!user.value)

    async function login(email: string, password: string) {
        loading.value = true
        try {
            const response = await api.post('/auth/login', { email, password })
            token.value = response.data.token
            user.value = response.data.user
            localStorage.setItem('auth_token', response.data.token)
            return { success: true }
        } catch (error: any) {
            return {
                success: false,
                message: error.response?.data?.message || 'Login failed',
            }
        } finally {
            loading.value = false
        }
    }

    async function register(name: string, email: string, password: string, password_confirmation: string) {
        loading.value = true
        try {
            const response = await api.post('/auth/register', {
                name,
                email,
                password,
                password_confirmation,
            })
            token.value = response.data.token
            user.value = response.data.user
            localStorage.setItem('auth_token', response.data.token)
            return { success: true }
        } catch (error: any) {
            return {
                success: false,
                message: error.response?.data?.message || 'Registration failed',
                errors: error.response?.data?.errors,
            }
        } finally {
            loading.value = false
        }
    }

    async function logout() {
        try {
            await api.post('/auth/logout')
        } catch {
            // Ignore errors on logout
        }
        token.value = null
        user.value = null
        localStorage.removeItem('auth_token')
    }

    async function fetchUser() {
        if (!token.value) return

        loading.value = true
        try {
            const response = await api.get('/auth/user')
            user.value = response.data.user
        } catch {
            // Token is invalid, clear it
            token.value = null
            user.value = null
            localStorage.removeItem('auth_token')
        } finally {
            loading.value = false
        }
    }

    function oauthLogin(provider: 'google' | 'github') {
        window.location.href = `/api/auth/${provider}/redirect`
    }

    return {
        user,
        token,
        loading,
        isAuthenticated,
        login,
        register,
        logout,
        fetchUser,
        oauthLogin,
    }
})
