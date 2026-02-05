# ChatJS - Laravel + Vue.js

A full-featured AI chat application built with Laravel 11 and Vue 3, supporting multiple AI providers including OpenAI, Anthropic, Google, xAI, Mistral, and DeepSeek.

## Features

- **Multiple AI Providers**: Support for OpenAI (GPT-4), Anthropic (Claude), Google (Gemini), xAI (Grok), Mistral, and DeepSeek
- **Streaming Responses**: Real-time AI response streaming with Server-Sent Events
- **Authentication**: Email/password and OAuth (Google, GitHub) authentication
- **Project Organization**: Organize chats into projects with custom system instructions
- **Chat Management**: Pin, share, and organize your conversations
- **Dark Mode**: Full dark mode support
- **Responsive Design**: Works on desktop and mobile

## Tech Stack

### Backend
- Laravel 11
- PHP 8.2+
- PostgreSQL (or MySQL)
- Redis (optional, for caching)
- Laravel Sanctum (API authentication)
- Laravel Socialite (OAuth)

### Frontend
- Vue 3 with Composition API
- TypeScript
- Pinia (state management)
- Vue Router
- Tailwind CSS
- Vite

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm/pnpm
- PostgreSQL or MySQL
- Redis (optional)

## Installation

### 1. Clone and Install Dependencies

```bash
cd laravel-vue

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Environment Variables

Edit `.env` and set:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chatjs
DB_USERNAME=your_username
DB_PASSWORD=your_password

# AI Providers (add at least one)
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GOOGLE_AI_API_KEY=...

# OAuth (optional)
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GITHUB_CLIENT_ID=...
GITHUB_CLIENT_SECRET=...
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate
```

### 5. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Start the Application

```bash
# Start Laravel development server
php artisan serve

# In a separate terminal, start Vite dev server
npm run dev
```

Visit `http://localhost:8000` in your browser.

## Configuration

### AI Providers

Configure available AI models in `config/ai.php`. Each provider requires an API key set in your `.env` file.

### Adding New Models

To add a new model, edit `config/ai.php`:

```php
'openai' => [
    'models' => [
        'gpt-4o' => [
            'name' => 'GPT-4o',
            'context_window' => 128000,
            'max_tokens' => 16384,
            'supports_vision' => true,
            'supports_tools' => true,
        ],
        // Add more models here
    ],
],
```

### OAuth Setup

1. Create OAuth apps on Google Cloud Console and GitHub
2. Set redirect URIs to `{APP_URL}/api/auth/{provider}/callback`
3. Add client ID and secret to `.env`

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/user` - Get current user
- `GET /api/auth/{provider}/redirect` - OAuth redirect
- `GET /api/auth/{provider}/callback` - OAuth callback

### Chats
- `GET /api/chats` - List user's chats
- `POST /api/chats` - Create new chat
- `GET /api/chats/{id}` - Get chat details
- `PATCH /api/chats/{id}` - Update chat
- `DELETE /api/chats/{id}` - Delete chat
- `GET /api/chats/{id}/messages` - Get chat messages
- `POST /api/chats/{id}/messages` - Send message (streaming)

### Projects
- `GET /api/projects` - List projects
- `POST /api/projects` - Create project
- `GET /api/projects/{id}` - Get project
- `PATCH /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Delete project

### Models
- `GET /api/models` - List available models
- `PATCH /api/models/{id}/preference` - Update model preference

## Project Structure

```
laravel-vue/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/                 # API controllers
│   │   └── Auth/                # Authentication
│   ├── Models/                  # Eloquent models
│   ├── Providers/               # Service providers
│   └── Services/
│       └── AI/                  # AI provider services
├── config/
│   ├── ai.php                   # AI configuration
│   └── ...
├── database/
│   └── migrations/              # Database migrations
├── resources/
│   ├── css/                     # Stylesheets
│   ├── js/
│   │   ├── components/          # Vue components
│   │   ├── layouts/             # Layout components
│   │   ├── router/              # Vue Router
│   │   ├── services/            # API services
│   │   ├── stores/              # Pinia stores
│   │   ├── types/               # TypeScript types
│   │   └── views/               # Page components
│   └── views/                   # Blade templates
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # Web routes
└── ...
```

## Development

### Running Tests

```bash
# PHP tests
php artisan test

# TypeScript type checking
npm run type-check
```

### Code Formatting

```bash
# Laravel Pint (PHP)
./vendor/bin/pint

# Prettier (JS/Vue)
npm run format
```

## Deployment

### Production Build

```bash
# Build frontend
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Server Requirements

- PHP 8.2+ with required extensions
- Nginx or Apache
- PostgreSQL/MySQL
- Redis (recommended for production)
- SSL certificate (required for OAuth)

## License

MIT License
