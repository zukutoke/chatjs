import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type { Chat, Message, MessagePart } from '@/types'

export const useChatStore = defineStore('chat', () => {
    const chats = ref<Chat[]>([])
    const currentChat = ref<Chat | null>(null)
    const messages = ref<Message[]>([])
    const loading = ref(false)
    const streaming = ref(false)
    const streamingContent = ref('')
    const streamingReasoning = ref('')

    const pinnedChats = computed(() => chats.value.filter((c) => c.is_pinned))
    const unpinnedChats = computed(() => chats.value.filter((c) => !c.is_pinned))

    async function fetchChats(projectId?: string) {
        loading.value = true
        try {
            const params = projectId ? { project_id: projectId } : {}
            const response = await api.get('/chats', { params })
            chats.value = response.data.data || response.data
        } finally {
            loading.value = false
        }
    }

    async function createChat(projectId?: string) {
        const response = await api.post('/chats', {
            project_id: projectId,
        })
        const chat = response.data
        chats.value.unshift(chat)
        return chat
    }

    async function fetchChat(id: string) {
        loading.value = true
        try {
            const response = await api.get(`/chats/${id}`)
            currentChat.value = response.data
            return response.data
        } finally {
            loading.value = false
        }
    }

    async function fetchMessages(chatId: string) {
        loading.value = true
        try {
            const response = await api.get(`/chats/${chatId}/messages`)
            messages.value = response.data
        } finally {
            loading.value = false
        }
    }

    async function sendMessage(chatId: string, content: string, model: string, attachments?: any[]) {
        streaming.value = true
        streamingContent.value = ''
        streamingReasoning.value = ''

        // Add user message optimistically
        const userMessage: Message = {
            id: `temp-${Date.now()}`,
            role: 'user',
            created_at: new Date().toISOString(),
            selected_model: model,
            parts: [{ id: `temp-part-${Date.now()}`, type: 'text', content }],
        }
        messages.value.push(userMessage)

        // Add placeholder for assistant message
        const assistantMessage: Message = {
            id: `temp-assistant-${Date.now()}`,
            role: 'assistant',
            created_at: new Date().toISOString(),
            selected_model: model,
            parts: [],
        }
        messages.value.push(assistantMessage)

        try {
            const response = await fetch(`/api/chats/${chatId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'text/event-stream',
                    Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
                },
                body: JSON.stringify({ content, model, attachments }),
            })

            const reader = response.body?.getReader()
            const decoder = new TextDecoder()

            if (!reader) throw new Error('No reader available')

            let realMessageId: string | null = null

            while (true) {
                const { done, value } = await reader.read()
                if (done) break

                const chunk = decoder.decode(value)
                const lines = chunk.split('\n')

                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue

                    const data = line.slice(6)
                    if (!data) continue

                    try {
                        const parsed = JSON.parse(data)

                        if (parsed.type === 'text') {
                            streamingContent.value += parsed.content
                            updateAssistantMessage()
                        } else if (parsed.type === 'reasoning') {
                            streamingReasoning.value += parsed.content
                            updateAssistantMessage()
                        } else if (parsed.type === 'finish') {
                            realMessageId = parsed.message_id
                        } else if (parsed.type === 'error') {
                            console.error('Stream error:', parsed.message)
                        }
                    } catch {
                        // Ignore parse errors for incomplete JSON
                    }
                }
            }

            // Update the message with real ID
            if (realMessageId) {
                const lastMessage = messages.value[messages.value.length - 1]
                if (lastMessage.role === 'assistant') {
                    lastMessage.id = realMessageId
                }
            }
        } catch (error) {
            console.error('Send message error:', error)
            // Remove the assistant placeholder on error
            messages.value.pop()
        } finally {
            streaming.value = false
        }
    }

    function updateAssistantMessage() {
        const lastMessage = messages.value[messages.value.length - 1]
        if (lastMessage.role !== 'assistant') return

        const parts: MessagePart[] = []

        if (streamingReasoning.value) {
            parts.push({
                id: 'streaming-reasoning',
                type: 'reasoning',
                content: streamingReasoning.value,
            })
        }

        if (streamingContent.value) {
            parts.push({
                id: 'streaming-text',
                type: 'text',
                content: streamingContent.value,
            })
        }

        lastMessage.parts = parts
    }

    async function updateChat(id: string, updates: Partial<Chat>) {
        const response = await api.patch(`/chats/${id}`, updates)
        const updated = response.data

        // Update in list
        const index = chats.value.findIndex((c) => c.id === id)
        if (index !== -1) {
            chats.value[index] = { ...chats.value[index], ...updated }
        }

        // Update current if same
        if (currentChat.value?.id === id) {
            currentChat.value = { ...currentChat.value, ...updated }
        }

        return updated
    }

    async function deleteChat(id: string) {
        await api.delete(`/chats/${id}`)
        chats.value = chats.value.filter((c) => c.id !== id)

        if (currentChat.value?.id === id) {
            currentChat.value = null
            messages.value = []
        }
    }

    async function togglePin(id: string) {
        const chat = chats.value.find((c) => c.id === id)
        if (!chat) return

        await updateChat(id, { is_pinned: !chat.is_pinned })
    }

    function clearCurrentChat() {
        currentChat.value = null
        messages.value = []
    }

    return {
        chats,
        currentChat,
        messages,
        loading,
        streaming,
        streamingContent,
        streamingReasoning,
        pinnedChats,
        unpinnedChats,
        fetchChats,
        createChat,
        fetchChat,
        fetchMessages,
        sendMessage,
        updateChat,
        deleteChat,
        togglePin,
        clearCurrentChat,
    }
})
