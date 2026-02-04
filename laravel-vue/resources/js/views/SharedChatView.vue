<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import type { Chat, Message } from '@/types'
import MessageList from '@/components/MessageList.vue'

const route = useRoute()
const router = useRouter()

const chat = ref<Chat | null>(null)
const messages = ref<Message[]>([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
    const chatId = route.params.id as string

    try {
        const [chatResponse, messagesResponse] = await Promise.all([
            api.get(`/chats/${chatId}`),
            api.get(`/chats/${chatId}/messages`),
        ])

        chat.value = chatResponse.data
        messages.value = messagesResponse.data

        if (chat.value?.visibility !== 'public') {
            error.value = 'This chat is private'
        }
    } catch (e: any) {
        if (e.response?.status === 404) {
            error.value = 'Chat not found'
        } else {
            error.value = 'Failed to load chat'
        }
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="flex items-center justify-between px-4 py-3 border-b border-border">
            <div class="flex items-center gap-3">
                <RouterLink to="/" class="text-xl font-bold">ChatJS</RouterLink>
                <span class="text-muted-foreground">/</span>
                <span>Shared Chat</span>
            </div>
            <RouterLink
                to="/"
                class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
            >
                Start your own chat
            </RouterLink>
        </header>

        <!-- Loading state -->
        <div v-if="loading" class="flex-1 flex items-center justify-center">
            <div class="flex gap-1">
                <span class="w-2 h-2 bg-primary rounded-full animate-pulse-dot" style="animation-delay: 0ms"></span>
                <span class="w-2 h-2 bg-primary rounded-full animate-pulse-dot" style="animation-delay: 150ms"></span>
                <span class="w-2 h-2 bg-primary rounded-full animate-pulse-dot" style="animation-delay: 300ms"></span>
            </div>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <h2 class="text-xl font-medium mb-2">{{ error }}</h2>
                <RouterLink to="/" class="text-primary hover:underline">
                    Go to home
                </RouterLink>
            </div>
        </div>

        <!-- Chat content -->
        <template v-else>
            <div class="px-4 py-3 bg-muted/50 border-b border-border">
                <h1 class="font-medium">{{ chat?.title }}</h1>
                <p class="text-sm text-muted-foreground">
                    Shared chat with {{ messages.length }} messages
                </p>
            </div>

            <MessageList :messages="messages" class="flex-1" />
        </template>
    </div>
</template>
