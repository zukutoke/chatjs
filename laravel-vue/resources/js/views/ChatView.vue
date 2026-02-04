<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import ChatInput from '@/components/ChatInput.vue'
import MessageList from '@/components/MessageList.vue'

const route = useRoute()
const router = useRouter()
const chatStore = useChatStore()
const authStore = useAuthStore()

const chatId = ref(route.params.id as string)
const messageListRef = ref<InstanceType<typeof MessageList>>()

onMounted(async () => {
    await loadChat()
})

watch(
    () => route.params.id,
    async (newId) => {
        if (newId && newId !== chatId.value) {
            chatId.value = newId as string
            await loadChat()
        }
    }
)

async function loadChat() {
    try {
        await chatStore.fetchChat(chatId.value)
        await chatStore.fetchMessages(chatId.value)
    } catch (error) {
        console.error('Failed to load chat:', error)
        router.push('/')
    }
}

async function handleSend(content: string, model: string) {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    await chatStore.sendMessage(chatId.value, content, model)
}
</script>

<template>
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="flex items-center justify-between px-4 py-3 border-b border-border">
            <div class="flex items-center gap-3">
                <h1 class="font-medium truncate">
                    {{ chatStore.currentChat?.title || 'Chat' }}
                </h1>
                <span
                    v-if="chatStore.currentChat?.project"
                    class="px-2 py-0.5 text-xs bg-secondary rounded"
                >
                    {{ chatStore.currentChat.project.name }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    v-if="chatStore.currentChat?.visibility === 'private'"
                    @click="chatStore.updateChat(chatId, { visibility: 'public' })"
                    class="px-3 py-1.5 text-sm rounded-lg hover:bg-accent transition-colors"
                >
                    Share
                </button>
                <button
                    v-else-if="chatStore.currentChat?.visibility === 'public'"
                    @click="chatStore.updateChat(chatId, { visibility: 'private' })"
                    class="px-3 py-1.5 text-sm bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg hover:opacity-80 transition-opacity"
                >
                    Public
                </button>
            </div>
        </header>

        <!-- Messages -->
        <MessageList
            ref="messageListRef"
            :messages="chatStore.messages"
            :streaming="chatStore.streaming"
        />

        <!-- Input -->
        <ChatInput
            @send="handleSend"
            :disabled="chatStore.streaming"
        />
    </div>
</template>
