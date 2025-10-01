<?php

namespace LaravelSage\Tests\Feature;

use LaravelSage\Manager\SageManager;
use LaravelSage\Tests\TestCase;

class SageManagerTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated(): void
    {
        $manager = app('sage');

        $this->assertInstanceOf(SageManager::class, $manager);
    }

    /** @test */
    public function it_can_set_model(): void
    {
        $manager = app('sage');
        $result = $manager->model('gpt-3.5-turbo');

        $this->assertInstanceOf(SageManager::class, $result);
    }

    /** @test */
    public function it_can_set_temperature(): void
    {
        $manager = app('sage');
        $result = $manager->temperature(0.5);

        $this->assertInstanceOf(SageManager::class, $result);
    }

    /** @test */
    public function it_can_chain_methods(): void
    {
        $manager = app('sage');
        $result = $manager
            ->model('gpt-3.5-turbo')
            ->temperature(0.5)
            ->maxTokens(500);

        $this->assertInstanceOf(SageManager::class, $result);
    }
}

