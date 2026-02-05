import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useUIStore = defineStore('ui', () => {
    const sidebarOpen = ref(true)
    const darkMode = ref(localStorage.getItem('dark_mode') === 'true')

    // Apply dark mode on init
    if (darkMode.value) {
        document.documentElement.classList.add('dark')
    }

    watch(darkMode, (value) => {
        localStorage.setItem('dark_mode', String(value))
        if (value) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    })

    function toggleSidebar() {
        sidebarOpen.value = !sidebarOpen.value
    }

    function toggleDarkMode() {
        darkMode.value = !darkMode.value
    }

    return {
        sidebarOpen,
        darkMode,
        toggleSidebar,
        toggleDarkMode,
    }
})
