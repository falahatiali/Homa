<?php

namespace Homa\Tests\Integration;

use Homa\Contracts\AIProviderInterface;
use Homa\Conversation\Conversation;
use Homa\Response\AIResponse;
use Homa\Tests\TestCase;
use Mockery;

class ConversationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $conversation = new Conversation($mockProvider);

        $this->assertInstanceOf(Conversation::class, $conversation);
    }

    /** @test */
    public function it_starts_with_system_prompt_from_config(): void
    {
        config(['homa.system_prompt' => 'You are a helpful assistant.']);

        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $conversation = new Conversation($mockProvider, []);

        $messages = $conversation->getMessages();

        $this->assertGreaterThanOrEqual(1, count($messages));
        $this->assertEquals('system', $messages[0]['role']);
        $this->assertStringContainsString('helpful assistant', strtolower($messages[0]['content']));
    }

    /** @test */
    public function it_can_add_messages_to_conversation(): void
    {
        config(['homa.system_prompt' => null]); // Disable default system prompt

        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockResponse = new AIResponse('I am doing well, thank you!');

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->andReturn($mockResponse);

        $conversation = new Conversation($mockProvider, []);
        $response = $conversation->ask('How are you?');

        $messages = $conversation->getMessages();

        $this->assertGreaterThanOrEqual(2, count($messages));
        // Find user message
        $userMessage = collect($messages)->firstWhere('role', 'user');
        $this->assertEquals('How are you?', $userMessage['content']);
        // Find assistant message
        $assistantMessage = collect($messages)->firstWhere('role', 'assistant');
        $this->assertEquals('I am doing well, thank you!', $assistantMessage['content']);
    }

    /** @test */
    public function it_maintains_conversation_context(): void
    {
        config(['homa.system_prompt' => null]);

        $mockProvider = Mockery::mock(AIProviderInterface::class);

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($messages) {
                $userMessages = collect($messages)->where('role', 'user');

                return $userMessages->count() === 1
                    && $userMessages->first()['content'] === 'My name is Alice';
            }), Mockery::type('array'))
            ->andReturn(new AIResponse('Nice to meet you, Alice!'));

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(function ($messages) {
                $userMessages = collect($messages)->where('role', 'user');

                return $userMessages->count() === 2
                    && $userMessages->last()['content'] === 'What is my name?';
            }), Mockery::type('array'))
            ->andReturn(new AIResponse('Your name is Alice.'));

        $conversation = new Conversation($mockProvider, []);

        $response1 = $conversation->ask('My name is Alice');
        $this->assertEquals('Nice to meet you, Alice!', $response1->content());

        $response2 = $conversation->ask('What is my name?');
        $this->assertEquals('Your name is Alice.', $response2->content());

        $this->assertCount(4, $conversation->getMessages());
    }

    /** @test */
    public function it_can_add_system_messages_mid_conversation(): void
    {
        config(['homa.system_prompt' => null]);

        $mockProvider = Mockery::mock(AIProviderInterface::class);

        $conversation = new Conversation($mockProvider, []);
        $conversation->system('You are now an expert in Laravel.');

        $messages = $conversation->getMessages();

        $systemMessage = collect($messages)->firstWhere('role', 'system');
        $this->assertNotNull($systemMessage);
        $this->assertEquals('You are now an expert in Laravel.', $systemMessage['content']);
    }

    /** @test */
    public function it_can_clear_conversation_history(): void
    {
        config(['homa.system_prompt' => null]);

        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockProvider->shouldReceive('sendMessage')
            ->andReturn(new AIResponse('Response'));

        $conversation = new Conversation($mockProvider, []);
        $conversation->ask('Hello');
        $conversation->ask('How are you?');

        $this->assertGreaterThanOrEqual(4, count($conversation->getMessages()));

        $conversation->clear();

        $this->assertCount(0, $conversation->getMessages());
    }

    /** @test */
    public function it_can_get_conversation_history_as_string(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockProvider->shouldReceive('sendMessage')
            ->andReturn(new AIResponse('I am doing well!'));

        $conversation = new Conversation($mockProvider, []);
        $conversation->ask('How are you?');

        $history = $conversation->history();

        $this->assertStringContainsString('user: How are you?', $history);
        $this->assertStringContainsString('assistant: I am doing well!', $history);
    }

    /** @test */
    public function it_passes_config_to_provider(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);

        $config = [
            'temperature' => 0.9,
            'max_tokens' => 500,
        ];

        $mockProvider->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::type('array'), $config)
            ->andReturn(new AIResponse('Response'));

        $conversation = new Conversation($mockProvider, $config);
        $conversation->ask('Test');

        // Test passes if mock expectations are met
        $this->assertTrue(true);
    }

    /** @test */
    public function it_returns_self_when_adding_system_message(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $conversation = new Conversation($mockProvider, []);

        $result = $conversation->system('You are helpful.');

        $this->assertSame($conversation, $result);
    }

    /** @test */
    public function it_returns_self_when_clearing(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $conversation = new Conversation($mockProvider, []);

        $result = $conversation->clear();

        $this->assertSame($conversation, $result);
    }

    /** @test */
    public function it_can_handle_multiple_system_prompts(): void
    {
        $mockProvider = Mockery::mock(AIProviderInterface::class);

        $config = [
            'system_prompt' => 'You are a helpful assistant.',
        ];

        $conversation = new Conversation($mockProvider, $config);
        $conversation->system('You are also an expert in Laravel.');

        $messages = $conversation->getMessages();

        $this->assertCount(2, $messages);
        $this->assertEquals('system', $messages[0]['role']);
        $this->assertEquals('You are a helpful assistant.', $messages[0]['content']);
        $this->assertEquals('system', $messages[1]['role']);
        $this->assertEquals('You are also an expert in Laravel.', $messages[1]['content']);
    }

    /** @test */
    public function it_maintains_message_order(): void
    {
        config(['homa.system_prompt' => null]);

        $mockProvider = Mockery::mock(AIProviderInterface::class);
        $mockProvider->shouldReceive('sendMessage')
            ->times(3)
            ->andReturn(
                new AIResponse('Response 1'),
                new AIResponse('Response 2'),
                new AIResponse('Response 3')
            );

        $conversation = new Conversation($mockProvider, []);
        $conversation->ask('Question 1');
        $conversation->ask('Question 2');
        $conversation->ask('Question 3');

        $messages = $conversation->getMessages();
        $userMessages = collect($messages)->where('role', 'user')->values();
        $assistantMessages = collect($messages)->where('role', 'assistant')->values();

        $this->assertEquals('Question 1', $userMessages[0]['content']);
        $this->assertEquals('Response 1', $assistantMessages[0]['content']);
        $this->assertEquals('Question 2', $userMessages[1]['content']);
        $this->assertEquals('Response 2', $assistantMessages[1]['content']);
        $this->assertEquals('Question 3', $userMessages[2]['content']);
        $this->assertEquals('Response 3', $assistantMessages[2]['content']);
    }
}
