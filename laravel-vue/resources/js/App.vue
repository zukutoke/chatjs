<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
    // Check if user has a saved token
    const token = localStorage.getItem('auth_token')
    if (token) {
        await authStore.fetchUser()
    }
})
</script>

<template>
    <div class="min-h-screen bg-background">
        <RouterView />
    </div>
</template>
