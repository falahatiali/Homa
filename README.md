<div align="center">
  <img src=".github/images/homa.png" alt="Homa - The legendary bird" width="300">
  
  # Homa 🦅
  
  **The legendary bird that brings AI wisdom to Laravel.**
  
  [![Tests](https://github.com/falahatiali/homa/actions/workflows/tests.yml/badge.svg)](https://github.com/falahatiali/homa/actions/workflows/tests.yml)
  [![Code Quality](https://github.com/falahatiali/homa/actions/workflows/code-quality.yml/badge.svg)](https://github.com/falahatiali/homa/actions/workflows/code-quality.yml)
  [![Latest Version](https://img.shields.io/packagist/v/falahatiali/homa.svg?style=flat-square)](https://packagist.org/packages/falahatiali/homa)
  [![Total Downloads](https://img.shields.io/packagist/dt/falahatiali/homa.svg?style=flat-square)](https://packagist.org/packages/falahatiali/homa)
  [![License](https://img.shields.io/packagist/l/falahatiali/homa.svg?style=flat-square)](https://packagist.org/packages/falahatiali/homa)
</div>

---

Homa is a simple and elegant AI assistant package for Laravel applications. Integrate multiple AI providers (OpenAI, Anthropic Claude, and more) with a clean, fluent API. Named after the mythical Persian bird that brings good fortune and wisdom to those it flies over.

## ✨ Features

- 🦅 **Simple, Fluent API** - Elegant interface inspired by Laravel's design philosophy
- 🔌 **Multiple AI Providers** - Support for OpenAI (GPT-4, GPT-3.5), Anthropic (Claude), Grok, Groq, and Google Gemini
- 💬 **Conversation Management** - Built-in context-aware multi-turn conversations
- ⚙️ **Highly Configurable** - Extensive configuration options for every use case
- 🧪 **Fully Tested** - 70 tests with 135 assertions covering all critical paths
- 📦 **Zero Configuration** - Works out of the box with sensible defaults
- 🎯 **Extensible** - Easy to add custom AI providers via Factory Pattern
- 🔒 **Type Safe** - Full PHP 8.1+ type hints and return types
- 🏗️ **SOLID Principles** - Clean architecture following best practices
- ⚡ **Production Ready** - Uses official OpenAI PHP client for reliability
- 🔍 **Code Quality** - PHPStan level 5 + Laravel Pint for consistency

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- API keys for your chosen AI provider(s)

## 📦 Installation

Install the package via Composer:

```bash
composer require falahatiali/homa
```

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=homa-config
```

This will create a `config/homa.php` configuration file.

### Quick Setup

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Add your API keys to `.env`:**
   ```env
   HOMA_PROVIDER=openai
   OPENAI_API_KEY=sk-your-actual-api-key
   ```

3. **Start using Homa:**
   ```php
   use Homa\Facades\Homa;
   $response = Homa::ask('Hello!');
   ```

### Configure Your API Keys

Copy the example environment file and add your AI provider API keys:

```bash
# Copy the example environment file
cp .env.example .env

# Edit with your actual API keys
nano .env
```

**Required Environment Variables:**

```env
# Choose your default provider (openai, anthropic, grok, groq, or gemini)
HOMA_PROVIDER=openai

# OpenAI Configuration
OPENAI_API_KEY=sk-your-openai-api-key-here
OPENAI_MODEL=gpt-4

# Anthropic Configuration  
ANTHROPIC_API_KEY=sk-ant-your-anthropic-api-key-here
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022

# Grok Configuration
GROK_API_KEY=xai-your-grok-api-key-here
GROK_MODEL=grok-2

# Groq Configuration (Ultra-fast inference)
GROQ_API_KEY=gsk_your-groq-api-key-here
GROQ_MODEL=openai/gpt-oss-20b

# Gemini Configuration (Google AI with multimodal)
GEMINI_API_KEY=your-gemini-api-key-here
GEMINI_MODEL=gemini-2.0-flash-exp
```

**Get API Keys:**
- **OpenAI**: [platform.openai.com/api-keys](https://platform.openai.com/api-keys)
- **Anthropic**: [console.anthropic.com](https://console.anthropic.com/)
- **Grok**: [console.x.ai](https://console.x.ai/)
- **Groq**: [console.groq.com](https://console.groq.com/)
- **Gemini**: [aistudio.google.com/apikey](https://aistudio.google.com/apikey)

## 🚀 Quick Start

### Basic Usage

Ask a simple question:

```php
use Homa\Facades\Homa;

$response = Homa::ask('What is Laravel?');
echo $response->content();
```

### Configure On-The-Fly

Chain configuration methods for custom behavior:

```php
$response = Homa::model('gpt-4')
    ->temperature(0.7)
    ->maxTokens(500)
    ->ask('Explain dependency injection in Laravel');

echo $response->content();
```

### Switch Between Providers

Easily switch between different AI providers:

```php
// Use OpenAI
$openaiResponse = Homa::provider('openai')
    ->model('gpt-4')
    ->ask('What is Laravel?');

// Use Anthropic Claude
$claudeResponse = Homa::provider('anthropic')
    ->model('claude-3-5-sonnet-20241022')
    ->ask('What is Laravel?');

// Use Groq (Ultra-fast inference)
$groqResponse = Homa::provider('groq')
    ->model('openai/gpt-oss-20b')
    ->ask('What is Laravel?');

// Use Gemini (Google AI with multimodal)
$geminiResponse = Homa::provider('gemini')
    ->model('gemini-2.0-flash-exp')
    ->ask('What is Laravel?');
```

### Custom System Prompts

Set custom system prompts for specialized behavior:

```php
$response = Homa::systemPrompt('You are a Laravel expert. Answer concisely.')
    ->ask('What is a service provider?');
```

### Multi-Turn Conversations

Create context-aware conversations:

```php
$conversation = Homa::startConversation();

$response1 = $conversation->ask('Hello! My name is Ali.');
// AI: Hello Ali! Nice to meet you...

$response2 = $conversation->ask('What is my name?');
// AI: Your name is Ali.

// Access conversation history
$history = $conversation->history();

// Clear conversation and start fresh
$conversation->clear();
```

### Advanced Chat Control

For full control over the conversation, use the `chat()` method:

```php
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful Laravel assistant.'],
    ['role' => 'user', 'content' => 'What are service containers?'],
    ['role' => 'assistant', 'content' => 'Service containers are...'],
    ['role' => 'user', 'content' => 'Can you give me an example?'],
];

$response = Homa::chat($messages);
```

### Working with Responses

The `AIResponse` object provides several useful methods:

```php
$response = Homa::ask('Hello!');

// Get the response content
$content = $response->content();

// Get the model used
$model = $response->model();

// Get usage statistics (tokens, etc.)
$usage = $response->usage();

// Get raw API response
$raw = $response->raw();

// Convert to array
$array = $response->toArray();

// Convert to JSON
$json = $response->toJson();

// Use as string
echo $response; // Automatically calls content()
```

## ⚙️ Configuration

### Configuration File

After publishing the config file, you can customize all aspects in `config/homa.php`. For environment variables, see `.env.example` for all available options:

```php
return [
    // Default AI provider
    'default' => env('HOMA_PROVIDER', 'openai'),

    // Provider configurations
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 1000),
            'timeout' => env('OPENAI_TIMEOUT', 30),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'api_url' => env('ANTHROPIC_API_URL', 'https://api.anthropic.com/v1'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
            'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1000),
            'timeout' => env('ANTHROPIC_TIMEOUT', 30),
        ],
        'grok' => [
            'api_key' => env('GROK_API_KEY'),
            'model' => env('GROK_MODEL', 'grok-2'),
            'temperature' => env('GROK_TEMPERATURE', 0.7),
            'max_tokens' => env('GROK_MAX_TOKENS', 1000),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'api_url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1'),
            'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
            'temperature' => env('GROQ_TEMPERATURE', 0.7),
            'max_tokens' => env('GROQ_MAX_TOKENS', 1000),
            'timeout' => env('GROQ_TIMEOUT', 30),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'base_uri' => env('GEMINI_BASE_URI', 'https://generativelanguage.googleapis.com/v1beta'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash-exp'),
            'temperature' => env('GEMINI_TEMPERATURE', 0.7),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 1000),
            'timeout' => env('GEMINI_TIMEOUT', 30),
        ],
    ],

    // Default system prompt
    'system_prompt' => env('HOMA_SYSTEM_PROMPT', 'You are a helpful AI assistant.'),

    // Logging configuration
    'logging' => [
        'enabled' => env('HOMA_LOGGING', false),
        'channel' => env('HOMA_LOG_CHANNEL', 'stack'),
    ],

    // Caching configuration
    'cache' => [
        'enabled' => env('HOMA_CACHE_ENABLED', false),
        'ttl' => env('HOMA_CACHE_TTL', 3600),
        'prefix' => 'homa_',
    ],
];
```

### Available Models

**OpenAI:**
- `gpt-4` - Most capable, best for complex tasks
- `gpt-4-turbo-preview` - Faster GPT-4 variant
- `gpt-3.5-turbo` - Fast and cost-effective

**Anthropic:**
- `claude-3-5-sonnet-20241022` - Latest, most capable
- `claude-3-opus-20240229` - Most powerful for complex tasks
- `claude-3-sonnet-20240229` - Balanced performance
- `claude-3-haiku-20240307` - Fastest, most cost-effective

**Groq (Ultra-fast inference):**
- `openai/gpt-oss-20b` - Large, capable model
- `openai/gpt-oss-7b` - Smaller, faster model
- `llama-3.1-70b-versatile` - Meta's Llama model
- `llama-3.1-8b-instant` - Fast Llama model
- `mixtral-8x7b-32768` - Mixtral model
- `gemma-7b-it` - Google's Gemma model

**Gemini (Google AI with multimodal capabilities):**
- `gemini-2.0-flash-exp` - Latest, fastest (experimental)
- `gemini-1.5-pro-latest` - Most capable
- `gemini-1.5-flash-latest` - Balanced speed and capability
- `gemini-1.5-pro` - Stable pro model
- `gemini-1.5-flash` - Fast and efficient
- `gemini-1.5-pro-002` - Versioned pro model
- `gemini-1.5-flash-002` - Versioned flash model

## 🎯 Use Cases

### Content Generation

```php
$blogPost = Homa::model('gpt-4')
    ->maxTokens(2000)
    ->ask('Write a blog post about Laravel best practices');
```

### Code Assistance

```php
$response = Homa::systemPrompt('You are an expert PHP developer.')
    ->ask('Review this code and suggest improvements: ' . $code);
```

### Customer Support Bot

```php
$conversation = Homa::systemPrompt('You are a helpful customer support agent.')
    ->startConversation();

$response = $conversation->ask($customerQuestion);
```

### Data Analysis

```php
$analysis = Homa::model('claude-3-5-sonnet-20241022')
    ->ask("Analyze this data and provide insights: " . json_encode($data));
```

## 🏗️ Architecture

### Package Structure

```
homa/
├── config/
│   └── homa.php                    # Configuration file
├── src/
│   ├── Contracts/
│   │   └── AIProviderInterface.php # Provider interface
│   ├── Conversation/
│   │   └── Conversation.php        # Conversation manager
│   ├── Exceptions/
│   │   ├── AIException.php         # Base exception
│   │   └── ConfigurationException.php
│   ├── Facades/
│   │   └── Homa.php                # Laravel facade
│   ├── Manager/
│   │   └── HomaManager.php         # Main manager class
│   ├── Providers/
│   │   ├── AnthropicProvider.php   # Anthropic implementation
│   │   └── OpenAIProvider.php      # OpenAI implementation
│   ├── Response/
│   │   └── AIResponse.php          # Response wrapper
│   └── HomaServiceProvider.php     # Laravel service provider
└── tests/                          # Comprehensive test suite
```

### Adding Custom Providers

You can extend Homa with custom AI providers by implementing the `AIProviderInterface`:

```php
use Homa\Contracts\AIProviderInterface;
use Homa\Response\AIResponse;

class CustomProvider implements AIProviderInterface
{
    public function sendMessage(array $messages, array $options = []): AIResponse
    {
        // Your implementation
    }
    
    // Implement other required methods...
}
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Or with PHPUnit directly:

```bash
./vendor/bin/phpunit
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🔒 Security

If you discover any security-related issues, please email the maintainer instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🌟 Credits

- **Author**: [Ali Falahati](https://github.com/falahatiali)
- **Inspired by**: The mythical Persian Homa bird, a symbol of wisdom and good fortune

## 📖 About Homa

In Persian mythology, the **Homa** (also spelled Huma) is a legendary bird that brings good fortune and wisdom to those fortunate enough to be graced by its shadow. The bird is said to never land, continuously soaring through the skies, much like how this package aims to elevate your Laravel applications with the power of AI.

Just as the Homa bird is known for its wisdom and grace, this package strives to bring intelligent, elegant solutions to your AI integration needs, making it effortless to incorporate cutting-edge AI capabilities into your Laravel applications.

---

<div align="center">
  
  **May the wisdom of Homa guide your code!** 🦅
  
  Made with ❤️ for the Laravel community
  
</div>
