<?php

namespace Homa\Tests\Unit;

use Homa\Response\AIResponse;
use PHPUnit\Framework\TestCase;

class AIResponseTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated(): void
    {
        $response = new AIResponse('Hello, world!', 'gpt-4', ['tokens' => 10]);

        $this->assertInstanceOf(AIResponse::class, $response);
    }

    /** @test */
    public function it_can_get_content(): void
    {
        $response = new AIResponse('Hello, world!');

        $this->assertEquals('Hello, world!', $response->content());
    }

    /** @test */
    public function it_can_get_model(): void
    {
        $response = new AIResponse('Hello', 'gpt-4');

        $this->assertEquals('gpt-4', $response->model());
    }

    /** @test */
    public function it_can_get_usage(): void
    {
        $usage = ['tokens' => 10, 'cost' => 0.001];
        $response = new AIResponse('Hello', 'gpt-4', $usage);

        $this->assertEquals($usage, $response->usage());
    }

    /** @test */
    public function it_can_be_converted_to_string(): void
    {
        $response = new AIResponse('Hello, world!');

        $this->assertEquals('Hello, world!', (string) $response);
    }

    /** @test */
    public function it_can_be_converted_to_array(): void
    {
        $response = new AIResponse('Hello', 'gpt-4', ['tokens' => 10]);

        $array = $response->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Hello', $array['content']);
        $this->assertEquals('gpt-4', $array['model']);
        $this->assertEquals(['tokens' => 10], $array['usage']);
    }

    /** @test */
    public function it_can_be_converted_to_json(): void
    {
        $response = new AIResponse('Hello', 'gpt-4', ['tokens' => 10]);

        $json = $response->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('Hello', $decoded['content']);
    }
}

