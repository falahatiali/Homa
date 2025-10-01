<?php

namespace Homa\Tests\Unit;

use Homa\Response\AIResponse;
use PHPUnit\Framework\TestCase;

class AIResponseEnhancedTest extends TestCase
{
    /** @test */
    public function it_can_handle_empty_content(): void
    {
        $response = new AIResponse('');

        $this->assertEquals('', $response->content());
        $this->assertEquals('', (string) $response);
    }

    /** @test */
    public function it_can_handle_multiline_content(): void
    {
        $content = "Line 1\nLine 2\nLine 3";
        $response = new AIResponse($content);

        $this->assertEquals($content, $response->content());
        $this->assertStringContainsString('Line 1', $response->content());
        $this->assertStringContainsString('Line 3', $response->content());
    }

    /** @test */
    public function it_can_handle_unicode_content(): void
    {
        $content = 'مرحبا 🦅 Hello 你好';
        $response = new AIResponse($content);

        $this->assertEquals($content, $response->content());
    }

    /** @test */
    public function it_can_handle_null_model(): void
    {
        $response = new AIResponse('Test', null);

        $this->assertNull($response->model());
    }

    /** @test */
    public function it_can_handle_empty_usage(): void
    {
        $response = new AIResponse('Test', 'gpt-4', []);

        $this->assertEquals([], $response->usage());
    }

    /** @test */
    public function it_can_handle_complex_usage_data(): void
    {
        $usage = [
            'prompt_tokens' => 100,
            'completion_tokens' => 200,
            'total_tokens' => 300,
            'cost' => 0.015,
            'model' => 'gpt-4',
        ];

        $response = new AIResponse('Test', 'gpt-4', $usage);

        $this->assertEquals(100, $response->usage()['prompt_tokens']);
        $this->assertEquals(0.015, $response->usage()['cost']);
    }

    /** @test */
    public function it_can_handle_empty_raw_data(): void
    {
        $response = new AIResponse('Test', 'gpt-4', [], []);

        $this->assertEquals([], $response->raw());
    }

    /** @test */
    public function it_stores_complete_raw_response(): void
    {
        $raw = [
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'created' => 1234567890,
            'choices' => [],
            'usage' => [],
        ];

        $response = new AIResponse('Test', 'gpt-4', [], $raw);

        $this->assertEquals('chatcmpl-123', $response->raw()['id']);
        $this->assertEquals('chat.completion', $response->raw()['object']);
    }

    /** @test */
    public function it_converts_to_array_correctly(): void
    {
        $response = new AIResponse(
            'Hello World',
            'gpt-4',
            ['tokens' => 50]
        );

        $array = $response->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('model', $array);
        $this->assertArrayHasKey('usage', $array);
        $this->assertEquals('Hello World', $array['content']);
        $this->assertEquals('gpt-4', $array['model']);
        $this->assertEquals(['tokens' => 50], $array['usage']);
    }

    /** @test */
    public function it_converts_to_json_correctly(): void
    {
        $response = new AIResponse(
            'Test content',
            'claude-3-5-sonnet-20241022',
            ['tokens' => 100]
        );

        $json = $response->toJson();

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertEquals('Test content', $decoded['content']);
        $this->assertEquals('claude-3-5-sonnet-20241022', $decoded['model']);
        $this->assertEquals(100, $decoded['usage']['tokens']);
    }

    /** @test */
    public function it_converts_to_json_with_pretty_print(): void
    {
        $response = new AIResponse('Test', 'gpt-4', []);

        $json = $response->toJson(JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString('    ', $json);
    }

    /** @test */
    public function it_handles_special_characters_in_json(): void
    {
        $content = 'Test with "quotes" and \'apostrophes\' and <html>';
        $response = new AIResponse($content);

        $json = $response->toJson();
        $decoded = json_decode($json, true);

        $this->assertEquals($content, $decoded['content']);
    }

    /** @test */
    public function it_can_be_serialized_and_unserialized(): void
    {
        $response = new AIResponse(
            'Test content',
            'gpt-4',
            ['tokens' => 50],
            ['raw' => 'data']
        );

        $serialized = serialize($response);
        $unserialized = unserialize($serialized);

        $this->assertEquals($response->content(), $unserialized->content());
        $this->assertEquals($response->model(), $unserialized->model());
        $this->assertEquals($response->usage(), $unserialized->usage());
        $this->assertEquals($response->raw(), $unserialized->raw());
    }

    /** @test */
    public function it_immutably_stores_data(): void
    {
        $usage = ['tokens' => 50];
        $raw = ['id' => '123'];

        $response = new AIResponse('Test', 'gpt-4', $usage, $raw);

        // Modify original arrays
        $usage['tokens'] = 100;
        $raw['id'] = '456';

        // Response should still have original values
        $this->assertEquals(50, $response->usage()['tokens']);
        $this->assertEquals('123', $response->raw()['id']);
    }
}
