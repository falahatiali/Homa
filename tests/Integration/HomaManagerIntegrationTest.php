<?php

namespace Homa\Tests\Integration;

use Homa\Contracts\AIProviderInterface;
use Homa\Conversation\Conversation;
use Homa\Factories\ProviderFactory;
use Homa\Manager\HomaManager;
use Homa\Response\AIResponse;
use Homa\Tests\TestCase;
use Mockery;

class HomaManagerIntegrationTest extends TestCase
{
    protected HomaManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'homa.default' => 'openai',
            'homa.providers.openai' => [
                'api_key' => 'test-key',
                'model' => 'gpt-4',
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ],
            'homa.providers.anthropic' => [
                'api_key' => 'test-key',
                'model' => 'claude-3-5-sonnet-20241022',
            ],
        ]);

        $this->manager = app('homa');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated_from_container(): void
    {
        $manager = app('homa');

        $this->assertInstanceOf(HomaManager::class, $manager);
    }

    /** @test */
    public function it_can_switch_providers(): void
    {
        $factory = Mockery::mock(ProviderFactory::class);
        $mockProvider = Mockery::mock(AIProviderInterface::class);

        $factory->shouldReceive('make')
            ->with('anthropic')
            ->once()
            ->andReturn($mockProvider);

        $manager = new HomaManager($factory);
        $result = $manager->provider('anthropic');

        $this->assertSame($manager, $result);
    }

    /** @test */
    public function it_chains_configuration_methods(): void
    {
        $result = $this->manager
            ->model('gpt-3.5-turbo')
            ->temperature(0.5)
            ->maxTokens(500)
            ->systemPrompt('Be concise');

        $this->assertInstanceOf(HomaManager::class, $result);
    }

    /** @test */
    public function it_can_ask_simple_questions(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockResponse = new AIResponse('Test response');

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::type('array'), Mockery::type('array'))
            ->andReturn($mockResponse);

        $factory = Mockery::mock(ProviderFactory::class);
        $factory->shouldReceive('make')
            ->andReturn($mockProvider);

        $manager = new HomaManager($factory);
        $response = $manager->ask('What is Laravel?');

        $this->assertInstanceOf(AIResponse::class, $response);
        $this->assertEquals('Test response', $response->content());
    }

    /** @test */
    public function it_includes_system_prompt_in_ask_method(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockResponse = new AIResponse('Test response');

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($messages) {
                return count($messages) === 2
                    && $messages[0]['role'] === 'system'
                    && $messages[0]['content'] === 'Be helpful'
                    && $messages[1]['role'] === 'user';
            }), Mockery::type('array'))
            ->andReturn($mockResponse);

        $factory = Mockery::mock(ProviderFactory::class);
        $factory->shouldReceive('make')
            ->andReturn($mockProvider);

        $manager = new HomaManager($factory);
        $manager->systemPrompt('Be helpful')->ask('Hello');

        // Test passes if mock expectations are met
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_send_chat_with_array_of_messages(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockResponse = new AIResponse('Test response');

        $messages = [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
            ['role' => 'user', 'content' => 'How are you?'],
        ];

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->with($messages, Mockery::type('array'))
            ->andReturn($mockResponse);

        $factory = Mockery::mock(ProviderFactory::class);
        $factory->shouldReceive('make')
            ->andReturn($mockProvider);

        $manager = new HomaManager($factory);
        $response = $manager->chat($messages);

        $this->assertInstanceOf(AIResponse::class, $response);
    }

    /** @test */
    public function it_can_send_chat_with_string_message(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockResponse = new AIResponse('Test response');

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($messages) {
                return count($messages) === 1
                    && $messages[0]['role'] === 'user'
                    && $messages[0]['content'] === 'Hello';
            }), Mockery::type('array'))
            ->andReturn($mockResponse);

        $factory = Mockery::mock(ProviderFactory::class);
        $factory->shouldReceive('make')
            ->andReturn($mockProvider);

        $manager = new HomaManager($factory);
        $response = $manager->chat('Hello');

        $this->assertInstanceOf(AIResponse::class, $response);
    }

    /** @test */
    public function it_can_start_conversation(): void
    {
        $conversation = $this->manager->startConversation();

        $this->assertInstanceOf(Conversation::class, $conversation);
    }

    /** @test */
    public function it_returns_available_providers(): void
    {
        $providers = $this->manager->availableProviders();

        $this->assertIsArray($providers);
        $this->assertContains('openai', $providers);
        $this->assertContains('anthropic', $providers);
    }

    /** @test */
    public function it_uses_default_provider_when_not_specified(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockResponse = new AIResponse('Default provider response');

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->andReturn($mockResponse);

        $factory = Mockery::mock(ProviderFactory::class);
        $factory->shouldReceive('make')
            ->with('openai')
            ->once()
            ->andReturn($mockProvider);

        config(['homa.default' => 'openai']);

        $manager = new HomaManager($factory);
        $response = $manager->ask('Test');

        $this->assertEquals('Default provider response', $response->content());
    }

    /** @test */
    public function it_passes_custom_options_to_provider(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockResponse = new AIResponse('Test');

        $mockProvider->shouldReceive('setModel')
            ->with('gpt-3.5-turbo')
            ->once()
            ->andReturnSelf();

        $mockProvider->shouldReceive('setTemperature')
            ->with(0.9)
            ->once()
            ->andReturnSelf();

        $mockProvider->shouldReceive('setMaxTokens')
            ->with(2000)
            ->once()
            ->andReturnSelf();

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->andReturn($mockResponse);

        $factory = Mockery::mock(ProviderFactory::class);
        $factory->shouldReceive('make')
            ->andReturn($mockProvider);

        $manager = new HomaManager($factory);
        $manager
            ->model('gpt-3.5-turbo')
            ->temperature(0.9)
            ->maxTokens(2000)
            ->ask('Test');

        // Test passes if mock expectations are met
        $this->assertTrue(true);
    }
}
