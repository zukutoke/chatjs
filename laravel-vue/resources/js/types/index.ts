export interface User {
    id: string
    name: string
    email: string
    image?: string
    credits?: number
    created_at: string
}

export interface Project {
    id: string
    name: string
    instructions?: string
    icon?: string
    icon_color?: string
    chats_count?: number
    created_at: string
    updated_at: string
}

export interface Chat {
    id: string
    title: string
    visibility: 'public' | 'private'
    is_pinned: boolean
    project_id?: string
    project?: Project
    messages_count?: number
    created_at: string
    updated_at: string
}

export interface Message {
    id: string
    role: 'user' | 'assistant' | 'system'
    selected_model?: string
    attachments?: Attachment[]
    parts: MessagePart[]
    created_at: string
}

export interface MessagePart {
    id: string
    type: 'text' | 'reasoning' | 'file' | 'tool-call' | 'source-url'
    content?: string
    tool_name?: string
    tool_state?: 'pending' | 'running' | 'completed' | 'failed'
    tool_input?: any
    tool_output?: any
}

export interface Attachment {
    id: string
    name: string
    url: string
    mime_type: string
    size: number
}

export interface Model {
    id: string
    provider: string
    name: string
    context_window: number
    max_tokens: number
    supports_vision: boolean
    supports_tools: boolean
    reasoning?: boolean
    enabled?: boolean
}

export interface Document {
    id: string
    title: string
    content?: string
    kind: 'text' | 'code' | 'sheet'
    language?: string
    created_at: string
    updated_at: string
}

export interface Vote {
    chat_id: string
    message_id: string
    is_upvoted: boolean
}
