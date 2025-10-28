<?php

/**
 * Gemini Provider Example Usage
 * 
 * This demonstrates how to use the Gemini provider with Homa.
 * Gemini offers multimodal capabilities including vision and video.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Homa\Facades\Homa;
use Homa\ValueObjects\MessageCollection;
use Homa\ValueObjects\RequestOptions;

echo "🚀 Gemini Provider Example\n";
echo "==========================\n\n";

// Example 1: Basic usage with Gemini
echo "📝 Example 1: Basic Gemini usage\n";
try {
    $response = Homa::provider('gemini')
        ->model('gemini-2.0-flash-exp')
        ->ask('What is Google Gemini?');
    
    echo "Response: " . $response->content() . "\n";
    echo "Model: " . $response->model() . "\n";
    echo "Usage: " . json_encode($response->usage()) . "\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Using different Gemini models
echo "📝 Example 2: Different Gemini models\n";
$models = [
    'gemini-2.0-flash-exp' => 'Latest, fastest experimental model',
    'gemini-1.5-pro-latest' => 'Most capable model',
    'gemini-1.5-flash-latest' => 'Fast and efficient',
];

foreach ($models as $model => $description) {
    try {
        echo "Testing {$model} ({$description})...\n";
        $response = Homa::provider('gemini')
            ->model($model)
            ->ask('Say hello in one word');
        
        echo "✅ {$model}: " . $response->content() . "\n";
    } catch (Exception $e) {
        echo "❌ {$model}: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Example 3: Value Objects approach
echo "📝 Example 3: Value Objects with Gemini\n";
try {
    $messages = new MessageCollection();
    $messages->system('You are a helpful assistant that gives concise answers.')
             ->user('Explain Gemini in one sentence.');

    $options = RequestOptions::balanced()
        ->merge(RequestOptions::withModel('gemini-1.5-flash-latest'));

    $response = Homa::provider('gemini')->send($messages, $options);
    
    echo "Response: " . $response->content() . "\n\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Temperature variations
echo "📝 Example 4: Testing temperature variations\n";
$temperatures = [0.0, 0.7, 1.2];

foreach ($temperatures as $temp) {
    try {
        echo "Testing temperature: {$temp}...\n";
        $response = Homa::provider('gemini')
            ->model('gemini-1.5-flash-latest')
            ->temperature($temp)
            ->ask('Write a creative one-sentence story about a robot.');
        
        echo "✅ Temp {$temp}: " . substr($response->content(), 0, 50) . "...\n";
    } catch (Exception $e) {
        echo "❌ Temp {$temp}: " . $e->getMessage() . "\n";
    }
}

// Example 5: Conversation context
echo "\n📝 Example 5: Conversation context\n";
try {
    $conversation = Homa::provider('gemini')
        ->model('gemini-2.0-flash-exp')
        ->startConversation();
    
    $response1 = $conversation->ask('My name is Bob.');
    echo "Response 1: " . $response1->content() . "\n";
    
    $response2 = $conversation->ask('What is my name?');
    echo "Response 2: " . $response2->content() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎉 Gemini examples completed!\n";
echo "\n💡 To use Gemini:\n";
echo "1. Get API key from: https://aistudio.google.com/app/apikey\n";
echo "2. Set GEMINI_API_KEY in your .env file\n";
echo "3. Use Homa::provider('gemini')->ask('Your question')\n";
echo "\n🌟 Gemini Features:\n";
echo "- Multimodal understanding (text, images, video)\n";
echo "- Advanced reasoning capabilities\n";
echo "- Large context window (up to 1M tokens)\n";
echo "- Fast response times\n";
