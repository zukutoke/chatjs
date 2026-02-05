<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import { useModelStore } from '@/stores/model'
import ChatInput from '@/components/ChatInput.vue'

const router = useRouter()
const chatStore = useChatStore()
const authStore = useAuthStore()
const modelStore = useModelStore()

async function handleSend(content: string, model: string) {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    // Create a new chat and send the message
    const chat = await chatStore.createChat()
    router.push(`/chat/${chat.id}`)

    // Wait for navigation then send message
    setTimeout(() => {
        chatStore.sendMessage(chat.id, content, model)
    }, 100)
}
</script>

<template>
    <div class="flex-1 flex flex-col">
        <!-- Hero section -->
        <div class="flex-1 flex flex-col items-center justify-center p-8">
            <div class="max-w-2xl text-center">
                <h1 class="text-4xl font-bold mb-4">ChatJS</h1>
                <p class="text-xl text-muted-foreground mb-8">
                    Your AI assistant powered by multiple language models
                </p>

                <!-- Quick model preview -->
                <div class="flex flex-wrap justify-center gap-2 mb-8">
                    <span
                        v-for="model in modelStore.enabledModels.slice(0, 6)"
                        :key="model.id"
                        class="px-3 py-1 bg-secondary rounded-full text-sm"
                    >
                        {{ model.name }}
                    </span>
                    <span
                        v-if="modelStore.enabledModels.length > 6"
                        class="px-3 py-1 bg-secondary rounded-full text-sm text-muted-foreground"
                    >
                        +{{ modelStore.enabledModels.length - 6 }} more
                    </span>
                </div>

                <!-- Feature cards -->
                <div class="grid md:grid-cols-3 gap-4 text-left">
                    <div class="p-4 bg-secondary/50 rounded-lg">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">Multiple Models</h3>
                        <p class="text-sm text-muted-foreground">
                            Access GPT-4, Claude, Gemini, and more AI models in one place.
                        </p>
                    </div>

                    <div class="p-4 bg-secondary/50 rounded-lg">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">Streaming Responses</h3>
                        <p class="text-sm text-muted-foreground">
                            See AI responses in real-time as they're generated.
                        </p>
                    </div>

                    <div class="p-4 bg-secondary/50 rounded-lg">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                        <h3 class="font-medium mb-1">Project Organization</h3>
                        <p class="text-sm text-muted-foreground">
                            Organize your chats into projects with custom instructions.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <ChatInput @send="handleSend" />
    </div>
</template>
