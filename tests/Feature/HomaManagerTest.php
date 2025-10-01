<?php

namespace Homa\Tests\Feature;

use Homa\Manager\HomaManager;
use Homa\Tests\TestCase;

class HomaManagerTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated(): void
    {
        $manager = app('homa');

        $this->assertInstanceOf(HomaManager::class, $manager);
    }

    /** @test */
    public function it_can_set_model(): void
    {
        $manager = app('homa');
        $result = $manager->model('gpt-3.5-turbo');

        $this->assertInstanceOf(HomaManager::class, $result);
    }

    /** @test */
    public function it_can_set_temperature(): void
    {
        $manager = app('homa');
        $result = $manager->temperature(0.5);

        $this->assertInstanceOf(HomaManager::class, $result);
    }

    /** @test */
    public function it_can_chain_methods(): void
    {
        $manager = app('homa');
        $result = $manager
            ->model('gpt-3.5-turbo')
            ->temperature(0.5)
            ->maxTokens(500);

        $this->assertInstanceOf(HomaManager::class, $result);
    }
}
