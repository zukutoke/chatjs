<script setup lang="ts">
import { ref, computed } from 'vue'
import { useModelStore } from '@/stores/model'
import ModelSelector from './ModelSelector.vue'

const emit = defineEmits<{
    (e: 'send', content: string, model: string): void
}>()

const props = defineProps<{
    disabled?: boolean
}>()

const modelStore = useModelStore()
const content = ref('')
const textareaRef = ref<HTMLTextAreaElement>()

const canSend = computed(() => content.value.trim().length > 0 && !props.disabled)

function handleSubmit() {
    if (!canSend.value) return

    emit('send', content.value.trim(), modelStore.selectedModel)
    content.value = ''

    // Reset textarea height
    if (textareaRef.value) {
        textareaRef.value.style.height = 'auto'
    }
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        handleSubmit()
    }
}

function autoResize() {
    if (textareaRef.value) {
        textareaRef.value.style.height = 'auto'
        textareaRef.value.style.height = Math.min(textareaRef.value.scrollHeight, 200) + 'px'
    }
}
</script>

<template>
    <div class="border-t border-border bg-background p-4">
        <div class="max-w-3xl mx-auto">
            <div class="relative flex items-end gap-2 bg-secondary rounded-xl p-2">
                <!-- Model selector -->
                <ModelSelector class="flex-shrink-0" />

                <!-- Input area -->
                <div class="flex-1 min-w-0">
                    <textarea
                        ref="textareaRef"
                        v-model="content"
                        @keydown="handleKeydown"
                        @input="autoResize"
                        :disabled="disabled"
                        placeholder="Message ChatJS..."
                        class="w-full bg-transparent border-0 resize-none focus:ring-0 focus:outline-none text-sm py-2 px-2 max-h-[200px]"
                        rows="1"
                    />
                </div>

                <!-- Send button -->
                <button
                    @click="handleSubmit"
                    :disabled="!canSend"
                    class="flex-shrink-0 p-2 rounded-lg transition-colors"
                    :class="[
                        canSend
                            ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                            : 'bg-muted text-muted-foreground cursor-not-allowed'
                    ]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>

            <p class="text-xs text-muted-foreground text-center mt-2">
                ChatJS can make mistakes. Check important info.
            </p>
        </div>
    </div>
</template>
