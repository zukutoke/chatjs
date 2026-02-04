<script setup lang="ts">
import { computed } from 'vue'
import type { Message } from '@/types'
import MarkdownRenderer from './MarkdownRenderer.vue'

const props = defineProps<{
    message: Message
}>()

const isUser = computed(() => props.message.role === 'user')

const textContent = computed(() => {
    const textPart = props.message.parts.find(p => p.type === 'text')
    return textPart?.content || ''
})

const reasoningContent = computed(() => {
    const reasoningPart = props.message.parts.find(p => p.type === 'reasoning')
    return reasoningPart?.content || ''
})
</script>

<template>
    <div
        class="flex gap-4"
        :class="[isUser ? 'justify-end' : 'justify-start']"
    >
        <!-- Avatar -->
        <div
            v-if="!isUser"
            class="flex-shrink-0 w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-sm font-medium"
        >
            AI
        </div>

        <!-- Message content -->
        <div
            class="max-w-[80%] rounded-2xl px-4 py-3"
            :class="[
                isUser
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-secondary'
            ]"
        >
            <!-- Reasoning (for reasoning models) -->
            <div
                v-if="reasoningContent"
                class="mb-3 pb-3 border-b border-border/50"
            >
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span class="text-xs font-medium text-muted-foreground">Thinking</span>
                </div>
                <div class="text-sm text-muted-foreground whitespace-pre-wrap">
                    {{ reasoningContent }}
                </div>
            </div>

            <!-- Text content -->
            <MarkdownRenderer
                v-if="textContent"
                :content="textContent"
                :class="[isUser ? 'text-primary-foreground' : '']"
            />

            <!-- Model info -->
            <div
                v-if="!isUser && message.selected_model"
                class="mt-2 pt-2 border-t border-border/30"
            >
                <span class="text-xs text-muted-foreground">
                    {{ message.selected_model }}
                </span>
            </div>
        </div>

        <!-- User avatar -->
        <div
            v-if="isUser"
            class="flex-shrink-0 w-8 h-8 rounded-full bg-secondary flex items-center justify-center text-sm font-medium"
        >
            U
        </div>
    </div>
</template>
