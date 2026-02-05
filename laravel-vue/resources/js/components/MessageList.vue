<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import type { Message } from '@/types'
import MessageItem from './MessageItem.vue'

const props = defineProps<{
    messages: Message[]
    streaming?: boolean
}>()

const containerRef = ref<HTMLElement>()

function scrollToBottom() {
    nextTick(() => {
        if (containerRef.value) {
            containerRef.value.scrollTop = containerRef.value.scrollHeight
        }
    })
}

// Auto-scroll when new messages arrive
watch(
    () => props.messages.length,
    () => scrollToBottom()
)

// Auto-scroll during streaming
watch(
    () => props.streaming,
    () => {
        if (props.streaming) {
            scrollToBottom()
        }
    }
)

defineExpose({ scrollToBottom })
</script>

<template>
    <div
        ref="containerRef"
        class="flex-1 overflow-y-auto"
    >
        <div class="max-w-3xl mx-auto py-8 px-4 space-y-6">
            <MessageItem
                v-for="message in messages"
                :key="message.id"
                :message="message"
            />

            <div
                v-if="streaming && messages.length > 0"
                class="flex items-center gap-2 text-muted-foreground"
            >
                <div class="flex gap-1">
                    <span class="w-2 h-2 bg-current rounded-full animate-pulse-dot" style="animation-delay: 0ms"></span>
                    <span class="w-2 h-2 bg-current rounded-full animate-pulse-dot" style="animation-delay: 150ms"></span>
                    <span class="w-2 h-2 bg-current rounded-full animate-pulse-dot" style="animation-delay: 300ms"></span>
                </div>
            </div>
        </div>
    </div>
</template>
