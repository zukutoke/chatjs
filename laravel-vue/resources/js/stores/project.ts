import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { Project } from '@/types'

export const useProjectStore = defineStore('project', () => {
    const projects = ref<Project[]>([])
    const currentProject = ref<Project | null>(null)
    const loading = ref(false)

    async function fetchProjects() {
        loading.value = true
        try {
            const response = await api.get('/projects')
            projects.value = response.data
        } finally {
            loading.value = false
        }
    }

    async function createProject(data: {
        name: string
        instructions?: string
        icon?: string
        icon_color?: string
    }) {
        const response = await api.post('/projects', data)
        const project = response.data
        projects.value.push(project)
        return project
    }

    async function fetchProject(id: string) {
        loading.value = true
        try {
            const response = await api.get(`/projects/${id}`)
            currentProject.value = response.data
            return response.data
        } finally {
            loading.value = false
        }
    }

    async function updateProject(id: string, updates: Partial<Project>) {
        const response = await api.patch(`/projects/${id}`, updates)
        const updated = response.data

        const index = projects.value.findIndex((p) => p.id === id)
        if (index !== -1) {
            projects.value[index] = { ...projects.value[index], ...updated }
        }

        if (currentProject.value?.id === id) {
            currentProject.value = { ...currentProject.value, ...updated }
        }

        return updated
    }

    async function deleteProject(id: string) {
        await api.delete(`/projects/${id}`)
        projects.value = projects.value.filter((p) => p.id !== id)

        if (currentProject.value?.id === id) {
            currentProject.value = null
        }
    }

    function clearCurrentProject() {
        currentProject.value = null
    }

    return {
        projects,
        currentProject,
        loading,
        fetchProjects,
        createProject,
        fetchProject,
        updateProject,
        deleteProject,
        clearCurrentProject,
    }
})
