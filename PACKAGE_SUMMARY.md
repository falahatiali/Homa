# Homa 🦅 - Package Summary

**The legendary Persian bird that brings AI wisdom to Laravel**

## Package Information

- **Package Name**: `falahatiali/homa`
- **GitHub Username**: `falahatiali`
- **Namespace**: `Homa`
- **Facade**: `Homa`
- **Config File**: `homa.php`

## Commit History (8 commits)

1. ✅ Initial project setup with composer.json and README
2. ✅ Add configuration file and service provider setup  
3. ✅ Add core contracts and exception classes
4. ✅ Implement SageManager and AIResponse classes
5. ✅ Add OpenAI and Anthropic provider implementations
6. ✅ Add conversation management for multi-turn interactions
7. ✅ Add testing infrastructure with PHPUnit and Orchestra Testbench
8. ✅ Rebrand package from Laravel Sage to Homa

## Package Structure

```
homa/
├── config/
│   └── homa.php                    # Configuration
├── src/
│   ├── Contracts/
│   │   └── AIProviderInterface.php
│   ├── Conversation/
│   │   └── Conversation.php
│   ├── Exceptions/
│   │   ├── AIException.php
│   │   └── ConfigurationException.php
│   ├── Facades/
│   │   └── Homa.php
│   ├── Manager/
│   │   └── HomaManager.php
│   ├── Providers/
│   │   ├── AnthropicProvider.php
│   │   └── OpenAIProvider.php
│   ├── Response/
│   │   └── AIResponse.php
│   └── HomaServiceProvider.php
├── tests/
│   ├── Feature/
│   │   └── HomaManagerTest.php
│   ├── Unit/
│   │   └── AIResponseTest.php
│   └── TestCase.php
├── .gitignore
├── composer.json
├── phpunit.xml
└── README.md
```

## Usage Examples

### Basic Usage
```php
use Homa\Facades\Homa;

$response = Homa::ask('What is Laravel?');
echo $response->content();
```

### With Configuration
```php
$response = Homa::model('gpt-4')
    ->temperature(0.7)
    ->maxTokens(500)
    ->ask('Explain dependency injection');
```

### Conversations
```php
$conversation = Homa::startConversation();
$response1 = $conversation->ask('Hello!');
$response2 = $conversation->ask('Tell me about PHP');
```

### Switch Providers
```php
// Use OpenAI
$response = Homa::provider('openai')
    ->model('gpt-4')
    ->ask('Question');

// Use Anthropic
$response = Homa::provider('anthropic')
    ->model('claude-3-5-sonnet-20241022')
    ->ask('Question');
```

## Environment Variables

```env
# Provider Selection
HOMA_PROVIDER=openai

# OpenAI
OPENAI_API_KEY=your-key-here
OPENAI_MODEL=gpt-4

# Anthropic
ANTHROPIC_API_KEY=your-key-here
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022

# Grok
GROK_API_KEY=xai-your-key-here
GROK_MODEL=grok-2

# Groq (Ultra-fast inference)
GROQ_API_KEY=gsk_your-key-here
GROQ_MODEL=openai/gpt-oss-20b

# System Prompt
HOMA_SYSTEM_PROMPT="You are a helpful AI assistant."

# Logging
HOMA_LOGGING=false
HOMA_LOG_CHANNEL=stack

# Caching
HOMA_CACHE_ENABLED=false
HOMA_CACHE_TTL=3600
```

**💡 Tip**: Copy `.env.example` to `.env` and fill in your actual API keys!

## Next Steps

1. ✅ Basic structure complete
2. ✅ Configuration system ready
3. ✅ Core functionality implemented
4. ✅ Tests infrastructure setup
5. 🔜 Add more AI providers (Gemini, Ollama, etc.)
6. 🔜 Add streaming support
7. 🔜 Add function calling/tools support
8. 🔜 Add rate limiting
9. 🔜 Add response caching
10. 🔜 Add conversation persistence
11. 🔜 Create comprehensive documentation
12. 🔜 Publish to Packagist

## Mythology Behind "Homa"

In Persian mythology, the Homa (or Huma) bird is a legendary creature that brings good fortune and wisdom to those it flies over. The bird is said to never land, continuously soaring through the skies. Like its namesake, this package soars above the complexity of AI integration, bringing wisdom and simplicity to your Laravel applications.
