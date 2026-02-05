<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useModelStore } from '@/stores/model'
import { useUIStore } from '@/stores/ui'

const authStore = useAuthStore()
const modelStore = useModelStore()
const uiStore = useUIStore()

onMounted(async () => {
    await modelStore.fetchModels()
})

async function toggleModel(modelId: string, enabled: boolean) {
    await modelStore.updateModelPreference(modelId, enabled)
}

async function logout() {
    await authStore.logout()
    window.location.href = '/'
}
</script>

<template>
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-2xl mx-auto py-8 px-4">
            <h1 class="text-2xl font-bold mb-8">Settings</h1>

            <!-- Account section -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4">Account</h2>
                <div class="bg-card border border-border rounded-lg p-4">
                    <div class="flex items-center gap-4">
                        <img
                            v-if="authStore.user?.image"
                            :src="authStore.user.image"
                            :alt="authStore.user.name"
                            class="w-16 h-16 rounded-full"
                        />
                        <div v-else class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-xl font-medium">
                            {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium">{{ authStore.user?.name }}</p>
                            <p class="text-muted-foreground">{{ authStore.user?.email }}</p>
                        </div>
                        <button
                            @click="logout"
                            class="px-4 py-2 text-sm border border-border rounded-lg hover:bg-accent transition-colors"
                        >
                            Sign out
                        </button>
                    </div>
                </div>
            </section>

            <!-- Appearance section -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4">Appearance</h2>
                <div class="bg-card border border-border rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">Dark Mode</p>
                            <p class="text-sm text-muted-foreground">Toggle dark mode appearance</p>
                        </div>
                        <button
                            @click="uiStore.toggleDarkMode"
                            class="relative w-12 h-6 rounded-full transition-colors"
                            :class="[uiStore.darkMode ? 'bg-primary' : 'bg-muted']"
                        >
                            <span
                                class="absolute top-1 w-4 h-4 rounded-full bg-white transition-transform"
                                :class="[uiStore.darkMode ? 'translate-x-7' : 'translate-x-1']"
                            />
                        </button>
                    </div>
                </div>
            </section>

            <!-- Models section -->
            <section>
                <h2 class="text-lg font-medium mb-4">Models</h2>
                <p class="text-muted-foreground mb-4">Choose which models appear in the model selector</p>

                <div class="space-y-4">
                    <div
                        v-for="(models, provider) in modelStore.modelsByProvider"
                        :key="provider"
                        class="bg-card border border-border rounded-lg overflow-hidden"
                    >
                        <div class="px-4 py-2 bg-muted border-b border-border">
                            <h3 class="font-medium capitalize">{{ provider }}</h3>
                        </div>
                        <div class="divide-y divide-border">
                            <div
                                v-for="model in models"
                                :key="model.id"
                                class="flex items-center justify-between px-4 py-3"
                            >
                                <div>
                                    <p class="font-medium">{{ model.name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-muted-foreground">
                                            {{ (model.context_window / 1000).toFixed(0) }}K context
                                        </span>
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
                                        <span
                                            v-if="model.supports_tools"
                                            class="px-1.5 py-0.5 text-[10px] bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded"
                                        >
                                            Tools
                                        </span>
                                    </div>
                                </div>
                                <button
                                    @click="toggleModel(model.id, model.enabled === false)"
                                    class="relative w-10 h-5 rounded-full transition-colors"
                                    :class="[model.enabled !== false ? 'bg-primary' : 'bg-muted']"
                                >
                                    <span
                                        class="absolute top-0.5 w-4 h-4 rounded-full bg-white transition-transform"
                                        :class="[model.enabled !== false ? 'translate-x-5' : 'translate-x-0.5']"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
