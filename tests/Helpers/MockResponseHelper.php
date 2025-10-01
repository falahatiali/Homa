<?php

namespace Homa\Tests\Helpers;

/**
 * Helper class for creating mock API responses in tests.
 */
class MockResponseHelper
{
    /**
     * Get a mock OpenAI chat completion response.
     */
    public static function openAiChatResponse(
        string $content = 'This is a test response',
        string $model = 'gpt-4',
        int $promptTokens = 10,
        int $completionTokens = 20
    ): array {
        return [
            'id' => 'chatcmpl-'.bin2hex(random_bytes(12)),
            'object' => 'chat.completion',
            'created' => time(),
            'model' => $model,
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => $content,
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $promptTokens + $completionTokens,
            ],
        ];
    }

    /**
     * Get a mock Anthropic messages response.
     */
    public static function anthropicMessagesResponse(
        string $content = 'This is a test response from Claude',
        string $model = 'claude-3-5-sonnet-20241022',
        int $inputTokens = 10,
        int $outputTokens = 25
    ): array {
        return [
            'id' => 'msg_'.bin2hex(random_bytes(12)),
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $content,
                ],
            ],
            'model' => $model,
            'stop_reason' => 'end_turn',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
            ],
        ];
    }

    /**
     * Get a mock error response.
     */
    public static function errorResponse(
        string $message = 'API Error',
        string $type = 'invalid_request_error',
        int $statusCode = 400
    ): array {
        return [
            'error' => [
                'message' => $message,
                'type' => $type,
                'code' => $statusCode,
            ],
        ];
    }
}
