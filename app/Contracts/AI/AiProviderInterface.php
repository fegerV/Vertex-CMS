<?php

namespace App\Contracts\AI;

/**
 * Interface for AI providers
 */
interface AiProviderInterface
{
    /**
     * Get the provider name
     */
    public function getName(): string;

    /**
     * Check if the provider is available/configured
     */
    public function isAvailable(): bool;

    /**
     * Generate text completion
     *
     * @param string $prompt The input prompt
     * @param array $options Additional options (model, temperature, max_tokens, etc.)
     */
    public function generateText(string $prompt, array $options = []): array;

    /**
     * Chat completion with conversation history
     *
     * @param array $messages Array of message objects with role and content
     * @param array $options Additional options
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Generate image from text prompt
     *
     * @param string $prompt The image description
     * @param array $options Image generation options
     */
    public function generateImage(string $prompt, array $options = []): array;

    /**
     * Get available models for this provider
     */
    public function getModels(): array;
}
