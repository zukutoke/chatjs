<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import type { Chat } from '@/types'

const props = defineProps<{
    chat: Chat
}>()

const router = useRouter()
const route = useRoute()
const chatStore = useChatStore()
const showMenu = ref(false)

const isActive = $computed(() => route.params.id === props.chat.id)

function openChat() {
    router.push(`/chat/${props.chat.id}`)
}

async function togglePin() {
    await chatStore.togglePin(props.chat.id)
    showMenu.value = false
}

async function deleteChat() {
    if (confirm('Are you sure you want to delete this chat?')) {
        await chatStore.deleteChat(props.chat.id)
        if (isActive) {
            router.push('/')
        }
    }
    showMenu.value = false
}
</script>

<template>
    <div
        class="group relative flex items-center gap-2 px-2 py-2 rounded-lg cursor-pointer transition-colors"
        :class="[isActive ? 'bg-accent' : 'hover:bg-accent/50']"
        @click="openChat"
    >
        <svg class="w-4 h-4 flex-shrink-0 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>

        <span class="flex-1 text-sm truncate">{{ chat.title }}</span>

        <svg
            v-if="chat.is_pinned"
            class="w-3 h-3 text-muted-foreground"
            fill="currentColor"
            viewBox="0 0 24 24"
        >
            <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z" />
        </svg>

        <!-- Menu button -->
        <div class="relative">
            <button
                class="p-1 rounded opacity-0 group-hover:opacity-100 hover:bg-background transition-all"
                @click.stop="showMenu = !showMenu"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div
                v-if="showMenu"
                class="absolute right-0 top-full mt-1 w-40 bg-popover border border-border rounded-lg shadow-lg z-50"
                @click.stop
            >
                <button
                    class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-accent transition-colors"
                    @click="togglePin"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    {{ chat.is_pinned ? 'Unpin' : 'Pin' }}
                </button>
                <button
                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-destructive hover:bg-destructive/10 transition-colors"
                    @click="deleteChat"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>
