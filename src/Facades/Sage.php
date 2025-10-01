<?php

namespace LaravelSage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelSage\Manager\SageManager provider(string $provider)
 * @method static \LaravelSage\Manager\SageManager model(string $model)
 * @method static \LaravelSage\Manager\SageManager temperature(float $temperature)
 * @method static \LaravelSage\Manager\SageManager maxTokens(int $maxTokens)
 * @method static \LaravelSage\Manager\SageManager systemPrompt(string $prompt)
 * @method static \LaravelSage\Response\AIResponse chat(string|array $messages)
 * @method static \LaravelSage\Response\AIResponse ask(string $question)
 * @method static \LaravelSage\Conversation\Conversation startConversation()
 *
 * @see \LaravelSage\Manager\SageManager
 */
class Sage extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sage';
    }
}

