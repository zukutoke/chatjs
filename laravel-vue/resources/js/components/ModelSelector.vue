<script setup lang="ts">
import { ref, computed } from 'vue'
import { useModelStore } from '@/stores/model'
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue'

const modelStore = useModelStore()

const selectedModel = computed({
    get: () => modelStore.currentModel,
    set: (model) => {
        if (model) {
            modelStore.setSelectedModel(model.id)
        }
    }
})

const providerIcons: Record<string, string> = {
    openai: 'O',
    anthropic: 'A',
    google: 'G',
    xai: 'X',
    mistral: 'M',
    deepseek: 'D',
}
</script>

<template>
    <Listbox v-model="selectedModel">
        <div class="relative">
            <ListboxButton
                class="flex items-center gap-2 px-3 py-2 rounded-lg bg-background hover:bg-accent transition-colors text-sm"
            >
                <span class="w-5 h-5 rounded bg-primary text-primary-foreground flex items-center justify-center text-xs font-medium">
                    {{ providerIcons[selectedModel?.provider || ''] || '?' }}
                </span>
                <span class="truncate max-w-[120px]">{{ selectedModel?.name || 'Select model' }}</span>
                <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </ListboxButton>

            <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <ListboxOptions
                    class="absolute bottom-full left-0 mb-2 w-64 max-h-80 overflow-auto bg-popover border border-border rounded-lg shadow-lg z-50"
                >
                    <div
                        v-for="(models, provider) in modelStore.modelsByProvider"
                        :key="provider"
                        class="p-1"
                    >
                        <div class="px-2 py-1 text-xs font-medium text-muted-foreground uppercase">
                            {{ provider }}
                        </div>
                        <ListboxOption
                            v-for="model in models"
                            :key="model.id"
                            :value="model"
                            v-slot="{ active, selected }"
                        >
                            <div
                                class="flex items-center gap-2 px-2 py-2 rounded cursor-pointer"
                                :class="[
                                    active ? 'bg-accent' : '',
                                    selected ? 'text-primary' : ''
                                ]"
                            >
                                <span
                                    class="w-5 h-5 rounded flex items-center justify-center text-xs font-medium"
                                    :class="[selected ? 'bg-primary text-primary-foreground' : 'bg-muted']"
                                >
                                    {{ providerIcons[provider] || '?' }}
                                </span>
                                <span class="flex-1 text-sm">{{ model.name }}</span>
                                <span
                                    v-if="model.reasoning"
                                    class="px-1.5 py-0.5 text-[10px] bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded"
                                >
                                    Reasoning
                                </span>
                                <span
                                    v-if="model.supports_vision"
                                    class="px-1.5 py-0.5 text-[10px] bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded"
                                >
                                    Vision
                                </span>
                            </div>
                        </ListboxOption>
                    </div>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>
