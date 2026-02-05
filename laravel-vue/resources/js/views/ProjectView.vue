<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/project'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import SidebarChatItem from '@/components/SidebarChatItem.vue'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()
const chatStore = useChatStore()
const authStore = useAuthStore()

const projectId = ref(route.params.id as string)
const editing = ref(false)
const editName = ref('')
const editInstructions = ref('')

onMounted(async () => {
    await loadProject()
})

watch(
    () => route.params.id,
    async (newId) => {
        if (newId && newId !== projectId.value) {
            projectId.value = newId as string
            await loadProject()
        }
    }
)

async function loadProject() {
    try {
        await projectStore.fetchProject(projectId.value)
        await chatStore.fetchChats(projectId.value)
        editName.value = projectStore.currentProject?.name || ''
        editInstructions.value = projectStore.currentProject?.instructions || ''
    } catch (error) {
        console.error('Failed to load project:', error)
        router.push('/')
    }
}

function startEditing() {
    editName.value = projectStore.currentProject?.name || ''
    editInstructions.value = projectStore.currentProject?.instructions || ''
    editing.value = true
}

async function saveChanges() {
    await projectStore.updateProject(projectId.value, {
        name: editName.value,
        instructions: editInstructions.value,
    })
    editing.value = false
}

async function deleteProject() {
    if (confirm('Are you sure you want to delete this project? All chats in this project will be unassigned.')) {
        await projectStore.deleteProject(projectId.value)
        router.push('/')
    }
}

async function createNewChat() {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    const chat = await chatStore.createChat(projectId.value)
    router.push(`/chat/${chat.id}`)
}
</script>

<template>
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="px-6 py-4 border-b border-border">
            <div v-if="!editing" class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ projectStore.currentProject?.name }}</h1>
                    <p v-if="projectStore.currentProject?.instructions" class="text-muted-foreground mt-1">
                        {{ projectStore.currentProject.instructions.slice(0, 100) }}{{ projectStore.currentProject.instructions.length > 100 ? '...' : '' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="startEditing"
                        class="px-3 py-1.5 text-sm rounded-lg hover:bg-accent transition-colors"
                    >
                        Edit
                    </button>
                    <button
                        @click="createNewChat"
                        class="px-3 py-1.5 text-sm bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                    >
                        New Chat
                    </button>
                </div>
            </div>

            <div v-else class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Project Name</label>
                    <input
                        v-model="editName"
                        type="text"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">System Instructions</label>
                    <textarea
                        v-model="editInstructions"
                        rows="4"
                        placeholder="Custom instructions for all chats in this project..."
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                    />
                </div>
                <div class="flex items-center justify-between">
                    <button
                        @click="deleteProject"
                        class="px-3 py-1.5 text-sm text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                    >
                        Delete Project
                    </button>
                    <div class="flex items-center gap-2">
                        <button
                            @click="editing = false"
                            class="px-3 py-1.5 text-sm rounded-lg hover:bg-accent transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="saveChanges"
                            class="px-3 py-1.5 text-sm bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Chats list -->
        <div class="flex-1 overflow-y-auto p-6">
            <h2 class="text-lg font-medium mb-4">Chats in this project</h2>

            <div v-if="chatStore.chats.length > 0" class="space-y-2">
                <SidebarChatItem
                    v-for="chat in chatStore.chats"
                    :key="chat.id"
                    :chat="chat"
                />
            </div>

            <div v-else class="text-center py-12 text-muted-foreground">
                <p>No chats in this project yet.</p>
                <button
                    @click="createNewChat"
                    class="mt-4 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                >
                    Create your first chat
                </button>
            </div>
        </div>
    </div>
</template>
