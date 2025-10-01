# Laravel Sage

A simple and elegant AI assistant package for Laravel applications. Integrate multiple AI providers (OpenAI, Anthropic, etc.) with a clean, fluent API.

## Installation

```bash
composer require your-vendor/laravel-sage
```

## Quick Start

```php
use LaravelSage\Facades\Sage;

// Simple question
$response = Sage::ask('What is Laravel?');
echo $response->content();

// With configuration
$response = Sage::model('gpt-4')
    ->temperature(0.7)
    ->ask('Explain dependency injection');

// Start a conversation
$conversation = Sage::startConversation();
$response1 = $conversation->ask('Hello, who are you?');
$response2 = $conversation->ask('Can you help me with Laravel?');
```

## Features

- 🚀 Simple, fluent API
- 🔌 Multiple AI provider support (OpenAI, Anthropic)
- 💬 Conversation management
- ⚙️ Highly configurable
- 🧪 Fully tested
- 📦 Zero configuration required

## License

MIT

