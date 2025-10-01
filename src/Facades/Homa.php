<?php

namespace Homa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Homa\Manager\HomaManager provider(string $provider)
 * @method static \Homa\Manager\HomaManager model(string $model)
 * @method static \Homa\Manager\HomaManager temperature(float $temperature)
 * @method static \Homa\Manager\HomaManager maxTokens(int $maxTokens)
 * @method static \Homa\Manager\HomaManager systemPrompt(string $prompt)
 * @method static \Homa\Response\AIResponse chat(string|array $messages)
 * @method static \Homa\Response\AIResponse ask(string $question)
 * @method static \Homa\Conversation\Conversation startConversation()
 *
 * @see \Homa\Manager\HomaManager
 */
class Homa extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'homa';
    }
}
