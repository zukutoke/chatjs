<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { useProjectStore } from '@/stores/project'
import { useAuthStore } from '@/stores/auth'
import { useUIStore } from '@/stores/ui'
import SidebarChatItem from './SidebarChatItem.vue'
import SidebarProjectItem from './SidebarProjectItem.vue'

const router = useRouter()
const chatStore = useChatStore()
const projectStore = useProjectStore()
const authStore = useAuthStore()
const uiStore = useUIStore()

const pinnedChats = computed(() => chatStore.pinnedChats)
const recentChats = computed(() => chatStore.unpinnedChats.slice(0, 20))

async function createNewChat() {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    const chat = await chatStore.createChat()
    router.push(`/chat/${chat.id}`)
}
</script>

<template>
    <aside class="h-full bg-secondary/50 border-r border-border flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-border">
            <button
                @click="createNewChat"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Chat
            </button>
        </div>

        <!-- Chat list -->
        <div class="flex-1 overflow-y-auto">
            <!-- Pinned chats -->
            <div v-if="pinnedChats.length > 0" class="p-2">
                <h3 class="px-2 py-1 text-xs font-medium text-muted-foreground uppercase">Pinned</h3>
                <SidebarChatItem v-for="chat in pinnedChats" :key="chat.id" :chat="chat" />
            </div>

            <!-- Recent chats -->
            <div class="p-2">
                <h3 class="px-2 py-1 text-xs font-medium text-muted-foreground uppercase">Recent</h3>
                <SidebarChatItem v-for="chat in recentChats" :key="chat.id" :chat="chat" />

                <p v-if="recentChats.length === 0" class="px-2 py-4 text-sm text-muted-foreground text-center">
                    No chats yet
                </p>
            </div>

            <!-- Projects -->
            <div v-if="projectStore.projects.length > 0" class="p-2 border-t border-border">
                <h3 class="px-2 py-1 text-xs font-medium text-muted-foreground uppercase">Projects</h3>
                <SidebarProjectItem v-for="project in projectStore.projects" :key="project.id" :project="project" />
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-border">
            <div v-if="authStore.isAuthenticated" class="flex items-center gap-3">
                <img
                    v-if="authStore.user?.image"
                    :src="authStore.user.image"
                    :alt="authStore.user.name"
                    class="w-8 h-8 rounded-full"
                />
                <div v-else class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-sm font-medium">
                    {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ authStore.user?.name }}</p>
                    <p class="text-xs text-muted-foreground truncate">{{ authStore.user?.email }}</p>
                </div>
                <button
                    @click="uiStore.toggleDarkMode"
                    class="p-2 rounded-lg hover:bg-accent transition-colors"
                    :title="uiStore.darkMode ? 'Light mode' : 'Dark mode'"
                >
                    <svg v-if="uiStore.darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>
            <div v-else class="space-y-2">
                <RouterLink
                    to="/login"
                    class="block w-full text-center px-4 py-2 rounded-lg border border-border hover:bg-accent transition-colors"
                >
                    Sign In
                </RouterLink>
            </div>
        </div>
    </aside>
</template>
