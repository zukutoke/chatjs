<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import type { Project } from '@/types'

const props = defineProps<{
    project: Project
}>()

const router = useRouter()
const route = useRoute()

const isActive = $computed(() => route.params.id === props.project.id && route.name === 'project')

function openProject() {
    router.push(`/project/${props.project.id}`)
}

const projectColors: Record<string, string> = {
    red: 'bg-red-500',
    orange: 'bg-orange-500',
    yellow: 'bg-yellow-500',
    green: 'bg-green-500',
    blue: 'bg-blue-500',
    purple: 'bg-purple-500',
    pink: 'bg-pink-500',
}

const iconColor = $computed(() => projectColors[props.project.icon_color || 'blue'] || 'bg-blue-500')
</script>

<template>
    <div
        class="flex items-center gap-2 px-2 py-2 rounded-lg cursor-pointer transition-colors"
        :class="[isActive ? 'bg-accent' : 'hover:bg-accent/50']"
        @click="openProject"
    >
        <div
            class="w-6 h-6 rounded flex items-center justify-center text-white text-xs"
            :class="iconColor"
        >
            {{ project.icon || project.name.charAt(0).toUpperCase() }}
        </div>

        <span class="flex-1 text-sm truncate">{{ project.name }}</span>

        <span v-if="project.chats_count" class="text-xs text-muted-foreground">
            {{ project.chats_count }}
        </span>
    </div>
</template>
