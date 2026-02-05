import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type { Model } from '@/types'

export const useModelStore = defineStore('model', () => {
    const models = ref<Model[]>([])
    const selectedModel = ref<string>(localStorage.getItem('selected_model') || '')
    const loading = ref(false)

    const enabledModels = computed(() => models.value.filter((m) => m.enabled !== false))

    const modelsByProvider = computed(() => {
        const grouped: Record<string, Model[]> = {}
        for (const model of enabledModels.value) {
            if (!grouped[model.provider]) {
                grouped[model.provider] = []
            }
            grouped[model.provider].push(model)
        }
        return grouped
    })

    const currentModel = computed(() => models.value.find((m) => m.id === selectedModel.value) || models.value[0])

    async function fetchModels() {
        loading.value = true
        try {
            const response = await api.get('/models')
            models.value = response.data

            // Set default model if not set
            if (!selectedModel.value && models.value.length > 0) {
                selectedModel.value = models.value[0].id
            }
        } finally {
            loading.value = false
        }
    }

    function setSelectedModel(modelId: string) {
        selectedModel.value = modelId
        localStorage.setItem('selected_model', modelId)
    }

    async function updateModelPreference(modelId: string, enabled: boolean) {
        await api.patch(`/models/${modelId}/preference`, { enabled })

        const index = models.value.findIndex((m) => m.id === modelId)
        if (index !== -1) {
            models.value[index].enabled = enabled
        }
    }

    return {
        models,
        selectedModel,
        loading,
        enabledModels,
        modelsByProvider,
        currentModel,
        fetchModels,
        setSelectedModel,
        updateModelPreference,
    }
})
