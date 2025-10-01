<?php

namespace Homa\Tests\Integration;

use Homa\Exceptions\AIException;
use Homa\Providers\OpenAIProvider;
use Homa\Response\AIResponse;
use Homa\Tests\TestCase;
use Mockery;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;

class OpenAIProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     *
     * @group skip
     * Note: OpenAI Client is final, so we skip deep mocking.
     * These tests would require actual API integration testing.
     */
    public function it_can_send_message_and_get_response(): void
    {
        $this->markTestSkipped('OpenAI Client is final - use real API integration tests');

        $mockClient = Mockery::mock(Client::class);
        $mockChat = Mockery::mock();

        $mockResponse = CreateResponse::from([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'created' => time(),
            'model' => 'gpt-4',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Laravel is a PHP framework.',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30,
            ],
        ]);

        $mockClient->shouldReceive('chat')->andReturn($mockChat);
        $mockChat->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($params) {
                return $params['model'] === 'gpt-4'
                    && isset($params['messages'])
                    && $params['temperature'] === 0.7;
            }))
            ->andReturn($mockResponse);

        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
            'temperature' => 0.7,
        ]);

        // Use reflection to inject mock client
        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $messages = [
            ['role' => 'user', 'content' => 'What is Laravel?'],
        ];

        $response = $provider->sendMessage($messages);

        $this->assertInstanceOf(AIResponse::class, $response);
        $this->assertEquals('Laravel is a PHP framework.', $response->content());
        $this->assertEquals('gpt-4', $response->model());
        $this->assertEquals(30, $response->usage()['total_tokens']);
    }

    /**
     * @test
     *
     * @group skip
     */
    public function it_handles_openai_api_errors_gracefully(): void
    {
        $this->markTestSkipped('OpenAI Client is final - use real API integration tests');

        $mockClient = Mockery::mock(Client::class);
        $mockChat = Mockery::mock();

        $mockClient->shouldReceive('chat')->andReturn($mockChat);
        $mockChat->shouldReceive('create')
            ->andThrow(new \OpenAI\Exceptions\ErrorException([
                'message' => 'Invalid API key',
                'type' => 'invalid_request_error',
            ]));

        $provider = new OpenAIProvider([
            'api_key' => 'invalid-key',
            'model' => 'gpt-4',
        ]);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $this->expectException(AIException::class);
        $this->expectExceptionMessage('OpenAI API Error');

        $provider->sendMessage([
            ['role' => 'user', 'content' => 'Test'],
        ]);
    }

    /** @test */
    public function it_can_change_model_dynamically(): void
    {
        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
        ]);

        $result = $provider->setModel('gpt-3.5-turbo');

        $this->assertSame($provider, $result);
    }

    /** @test */
    public function it_can_change_temperature_dynamically(): void
    {
        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
        ]);

        $result = $provider->setTemperature(0.9);

        $this->assertSame($provider, $result);
    }

    /** @test */
    public function it_can_change_max_tokens_dynamically(): void
    {
        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
        ]);

        $result = $provider->setMaxTokens(2000);

        $this->assertSame($provider, $result);
    }

    /** @test */
    public function it_validates_config_correctly(): void
    {
        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
        ]);

        $this->assertTrue($provider->validateConfig());
    }

    /**
     * @test
     *
     * @group skip
     */
    public function it_uses_custom_options_over_defaults(): void
    {
        $this->markTestSkipped('OpenAI Client is final - use real API integration tests');

        $mockClient = Mockery::mock(Client::class);
        $mockChat = Mockery::mock();

        $mockResponse = CreateResponse::from([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'created' => time(),
            'model' => 'gpt-3.5-turbo',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Test response',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 5,
                'completion_tokens' => 10,
                'total_tokens' => 15,
            ],
        ]);

        $mockClient->shouldReceive('chat')->andReturn($mockChat);
        $mockChat->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($params) {
                return $params['model'] === 'gpt-3.5-turbo'
                    && $params['temperature'] === 0.9
                    && $params['max_tokens'] === 500;
            }))
            ->andReturn($mockResponse);

        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
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
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.9,
                'max_tokens' => 500,
            ]
        );

        // Test passes if mock expectations are met
        $this->assertTrue(true);
    }
}
