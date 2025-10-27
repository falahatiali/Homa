<?php

/**
 * Groq Provider Example Usage
 * 
 * This demonstrates how to use the Groq provider with Homa.
 * Groq offers ultra-fast LLM inference with OpenAI-compatible API.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Homa\Facades\Homa;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;

echo "🚀 Groq Provider Example\n";
echo "========================\n\n";

// Example 1: Basic usage with Groq
echo "📝 Example 1: Basic Groq usage\n";
try {
    $response = Homa::provider('groq')
        ->model('openai/gpt-oss-20b')
        ->ask('What makes Groq special?');
    
    echo "Response: " . $response->content() . "\n";
    echo "Model: " . $response->model() . "\n";
    echo "Usage: " . json_encode($response->usage()) . "\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Using different Groq models
echo "📝 Example 2: Different Groq models\n";
$models = [
    'openai/gpt-oss-20b' => 'Large, capable model',
    'llama-3.1-70b-versatile' => 'Meta Llama model',
    'mixtral-8x7b-32768' => 'Mixtral model',
];

foreach ($models as $model => $description) {
    try {
        echo "Testing {$model} ({$description})...\n";
        $response = Homa::provider('groq')
            ->model($model)
            ->ask('Say hello in one word');
        
        echo "✅ {$model}: " . $response->content() . "\n";
    } catch (Exception $e) {
        echo "❌ {$model}: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Example 3: Value Objects approach
echo "📝 Example 3: Value Objects with Groq\n";
try {
    $messages = new MessageCollection();
    $messages->system('You are a helpful assistant that gives concise answers.')
             ->user('Explain Groq in one sentence.');

    $options = RequestOptions::balanced()
        ->merge(RequestOptions::withModel('openai/gpt-oss-7b'));

    $response = Homa::provider('groq')->send($messages, $options);
    
    echo "Response: " . $response->content() . "\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Performance comparison
echo "📝 Example 4: Performance comparison\n";
$providers = ['openai', 'groq'];

foreach ($providers as $provider) {
    $start = microtime(true);
    
    try {
        $response = Homa::provider($provider)
            ->ask('Count from 1 to 5');
        
        $end = microtime(true);
        $time = round(($end - $start) * 1000, 2);
        
        echo "✅ {$provider}: {$time}ms - " . substr($response->content(), 0, 30) . "...\n";
    } catch (Exception $e) {
        echo "❌ {$provider}: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Groq examples completed!\n";
echo "\n💡 To use Groq:\n";
echo "1. Get API key from: https://console.groq.com/\n";
echo "2. Set GROQ_API_KEY in your .env file\n";
echo "3. Use Homa::provider('groq')->ask('Your question')\n";
