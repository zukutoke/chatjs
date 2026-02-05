<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import AppSidebar from '@/components/AppSidebar.vue'
import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '@/stores/chat'
import { useProjectStore } from '@/stores/project'
import { useModelStore } from '@/stores/model'
import { useUIStore } from '@/stores/ui'

const authStore = useAuthStore()
const chatStore = useChatStore()
const projectStore = useProjectStore()
const modelStore = useModelStore()
const uiStore = useUIStore()

onMounted(async () => {
    await modelStore.fetchModels()

    if (authStore.isAuthenticated) {
        await Promise.all([chatStore.fetchChats(), projectStore.fetchProjects()])
    }
})
</script>

<template>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <AppSidebar v-if="uiStore.sidebarOpen" class="w-64 flex-shrink-0" />

        <!-- Main content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <RouterView />
        </main>
    </div>
</template>
