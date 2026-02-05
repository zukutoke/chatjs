<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const error = ref('')
const errors = ref<Record<string, string[]>>({})

async function handleSubmit() {
    error.value = ''
    errors.value = {}

    const result = await authStore.register(
        name.value,
        email.value,
        password.value,
        password_confirmation.value
    )

    if (result.success) {
        router.push('/')
    } else {
        error.value = result.message || 'Registration failed'
        errors.value = result.errors || {}
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold">Create an account</h1>
                <p class="text-muted-foreground mt-2">Start chatting with AI models</p>
            </div>

            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div v-if="error" class="p-3 bg-destructive/10 text-destructive text-sm rounded-lg">
                        {{ error }}
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input
                            v-model="name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                        <p v-if="errors.name" class="text-destructive text-sm mt-1">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input
                            v-model="email"
                            type="email"
                            required
                            class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                        <p v-if="errors.email" class="text-destructive text-sm mt-1">{{ errors.email[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Password</label>
                        <input
                            v-model="password"
                            type="password"
                            required
                            class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                        <p v-if="errors.password" class="text-destructive text-sm mt-1">{{ errors.password[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input
                            v-model="password_confirmation"
                            type="password"
                            required
                            class="w-full px-3 py-2 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="authStore.loading"
                        class="w-full py-2.5 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50"
                    >
                        {{ authStore.loading ? 'Creating account...' : 'Create account' }}
                    </button>
                </form>

                <p class="text-center text-sm text-muted-foreground mt-6">
                    Already have an account?
                    <RouterLink to="/login" class="text-primary hover:underline">Sign in</RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>
