# Homa 🦅

**The legendary bird that brings AI wisdom to Laravel.**

Homa is a simple and elegant AI assistant package for Laravel applications. Integrate multiple AI providers (OpenAI, Anthropic, etc.) with a clean, fluent API. Named after the mythical Persian bird that brings good fortune and wisdom.

## Installation

```bash
composer require falahatiali/homa
```

## Quick Start

```php
use Homa\Facades\Homa;

// Simple question
$response = Homa::ask('What is Laravel?');
echo $response->content();

// With configuration
$response = Homa::model('gpt-4')
    ->temperature(0.7)
    ->ask('Explain dependency injection');

// Start a conversation
$conversation = Homa::startConversation();
$response1 = $conversation->ask('Hello, who are you?');
$response2 = $conversation->ask('Can you help me with Laravel?');
```

## Features

- 🦅 Simple, fluent API inspired by elegance
- 🔌 Multiple AI provider support (OpenAI, Anthropic)
- 💬 Conversation management with context
- ⚙️ Highly configurable
- 🧪 Fully tested
- 📦 Zero configuration required

## Why Homa?

In Persian mythology, the Homa (Huma) bird is a legendary creature that brings good fortune and wisdom to those it flies over. Like its namesake, this package soars above the complexity of AI integration, bringing wisdom and simplicity to your Laravel applications.

## License

MIT

