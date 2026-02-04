<script setup lang="ts">
import { computed } from 'vue'
import { marked } from 'marked'
import hljs from 'highlight.js'

const props = defineProps<{
    content: string
}>()

// Configure marked for code highlighting
marked.setOptions({
    highlight: function (code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(code, { language: lang }).value
            } catch {
                // Ignore highlighting errors
            }
        }
        return code
    },
    breaks: true,
    gfm: true,
})

const renderedContent = computed(() => {
    try {
        return marked(props.content)
    } catch {
        return props.content
    }
})
</script>

<template>
    <div
        class="prose prose-sm dark:prose-invert max-w-none"
        v-html="renderedContent"
    />
</template>

<style>
.prose pre {
    @apply bg-muted rounded-lg overflow-x-auto;
}

.prose pre code {
    @apply text-xs;
}

.prose code:not(pre code) {
    @apply bg-muted px-1.5 py-0.5 rounded text-sm;
}

.prose a {
    @apply text-primary hover:underline;
}

.prose ul, .prose ol {
    @apply my-2;
}

.prose li {
    @apply my-0.5;
}
</style>
