<?php

namespace Homa\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Homa\Exceptions\AIException;
use Homa\Providers\AnthropicProvider;
use Homa\Response\AIResponse;
use Homa\Tests\Helpers\MockResponseHelper;
use Homa\Tests\TestCase;

class AnthropicProviderTest extends TestCase
{
    /** @test */
    public function it_can_send_message_and_get_response(): void
    {
        $mockResponse = MockResponseHelper::anthropicMessagesResponse(
            content: 'Claude is an AI assistant by Anthropic.',
            model: 'claude-3-5-sonnet-20241022'
        );

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        // Inject mock client
        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $messages = [
            ['role' => 'user', 'content' => 'What is Claude?'],
        ];

        $response = $provider->sendMessage($messages);

        $this->assertInstanceOf(AIResponse::class, $response);
        $this->assertEquals('Claude is an AI assistant by Anthropic.', $response->content());
        $this->assertEquals('claude-3-5-sonnet-20241022', $response->model());
    }

    /** @test */
    public function it_separates_system_message_correctly(): void
    {
        $mockResponse = MockResponseHelper::anthropicMessagesResponse(
            content: 'I am a helpful assistant.'
        );

        $requestPayload = null;

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);

        // Add middleware to capture request
        $handlerStack->push(function (callable $handler) use (&$requestPayload) {
            return function ($request, array $options) use ($handler, &$requestPayload) {
                $requestPayload = json_decode($request->getBody()->getContents(), true);

                return $handler($request, $options);
            };
        });

        $mockClient = new Client(['handler' => $handlerStack]);

        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => 'Hello!'],
        ];

        $provider->sendMessage($messages);

        // Verify that system message was separated
        $this->assertArrayHasKey('system', $requestPayload);
        $this->assertEquals('You are a helpful assistant.', $requestPayload['system']);
        $this->assertCount(1, $requestPayload['messages']);
        $this->assertEquals('user', $requestPayload['messages'][0]['role']);
    }

    /** @test */
    public function it_handles_anthropic_api_errors_gracefully(): void
    {
        $errorResponse = MockResponseHelper::errorResponse(
            message: 'Invalid API key',
            type: 'authentication_error',
            statusCode: 401
        );

        $mock = new MockHandler([
            new Response(401, [], json_encode($errorResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $provider = new AnthropicProvider([
            'api_key' => 'invalid-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $this->expectException(AIException::class);

        $provider->sendMessage([
            ['role' => 'user', 'content' => 'Test'],
        ]);
    }

    /** @test */
    public function it_can_change_model_dynamically(): void
    {
        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $result = $provider->setModel('claude-3-opus-20240229');

        $this->assertSame($provider, $result);
    }

    /** @test */
    public function it_can_change_temperature_dynamically(): void
    {
        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $result = $provider->setTemperature(0.5);

        $this->assertSame($provider, $result);
    }

    /** @test */
    public function it_can_change_max_tokens_dynamically(): void
    {
        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $result = $provider->setMaxTokens(2000);

        $this->assertSame($provider, $result);
    }

    /** @test */
    public function it_validates_config_correctly(): void
    {
        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $this->assertTrue($provider->validateConfig());
    }

    /** @test */
    public function it_uses_custom_options_over_defaults(): void
    {
        $mockResponse = MockResponseHelper::anthropicMessagesResponse();

        $requestPayload = null;

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(function (callable $handler) use (&$requestPayload) {
            return function ($request, array $options) use ($handler, &$requestPayload) {
                $requestPayload = json_decode($request->getBody()->getContents(), true);

                return $handler($request, $options);
            };
        });

        $mockClient = new Client(['handler' => $handlerStack]);

        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $provider->sendMessage(
            [['role' => 'user', 'content' => 'Test']],
            [
                'model' => 'claude-3-opus-20240229',
                'temperature' => 0.9,
                'max_tokens' => 500,
            ]
        );

        $this->assertEquals('claude-3-opus-20240229', $requestPayload['model']);
        $this->assertEquals(0.9, $requestPayload['temperature']);
        $this->assertEquals(500, $requestPayload['max_tokens']);
    }

    /** @test */
    public function it_includes_usage_statistics_in_response(): void
    {
        $mockResponse = MockResponseHelper::anthropicMessagesResponse(
            inputTokens: 15,
            outputTokens: 30
        );

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $provider = new AnthropicProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $response = $provider->sendMessage([
            ['role' => 'user', 'content' => 'Test'],
        ]);

        $usage = $response->usage();
        $this->assertArrayHasKey('input_tokens', $usage);
        $this->assertArrayHasKey('output_tokens', $usage);
        $this->assertEquals(15, $usage['input_tokens']);
        $this->assertEquals(30, $usage['output_tokens']);
    }
}
