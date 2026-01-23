<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Cache;

class ContextManager
{
    private string $prefix;
    private int $ttl;

    public function __construct()
    {
        $this->prefix = config('chat.cache.prefix', 'chat_context_');
        $this->ttl = config('chat.cache.ttl', 1800);
    }

    /**
     * Get the cache key for a user
     */
    private function getCacheKey(int $userId): string
    {
        return $this->prefix . $userId;
    }

    /**
     * Get context for a user
     */
    public function getContext(int $userId): array
    {
        return Cache::get($this->getCacheKey($userId), $this->getDefaultContext($userId));
    }

    /**
     * Set context for a user
     */
    public function setContext(int $userId, array $context): void
    {
        $context['updated_at'] = now()->timestamp;
        Cache::put($this->getCacheKey($userId), $context, $this->ttl);
    }

    /**
     * Get default empty context
     */
    private function getDefaultContext(int $userId): array
    {
        return [
            'user_id' => $userId,
            'flow' => null,
            'step' => null,
            'data' => [],
            'search_context' => [
                'last_keyword' => null,
                'last_results' => [],
            ],
            'updated_at' => now()->timestamp,
        ];
    }

    /**
     * Update the current flow
     */
    public function updateFlow(int $userId, ?string $flow, ?string $step, array $data = []): void
    {
        $context = $this->getContext($userId);
        $context['flow'] = $flow;
        $context['step'] = $step;
        $context['data'] = array_merge($context['data'], $data);
        $this->setContext($userId, $context);
    }

    /**
     * Update only the step within current flow
     */
    public function updateStep(int $userId, string $step, array $data = []): void
    {
        $context = $this->getContext($userId);
        $context['step'] = $step;
        $context['data'] = array_merge($context['data'], $data);
        $this->setContext($userId, $context);
    }

    /**
     * Update flow data without changing flow/step
     */
    public function updateData(int $userId, array $data): void
    {
        $context = $this->getContext($userId);
        $context['data'] = array_merge($context['data'], $data);
        $this->setContext($userId, $context);
    }

    /**
     * Get current flow data
     */
    public function getFlowData(int $userId): array
    {
        $context = $this->getContext($userId);
        return $context['data'] ?? [];
    }

    /**
     * Clear the current flow (return to menu state)
     */
    public function clearFlow(int $userId): void
    {
        $context = $this->getContext($userId);
        $context['flow'] = null;
        $context['step'] = null;
        $context['data'] = [];
        $this->setContext($userId, $context);
    }

    /**
     * Check if user is currently in a flow
     */
    public function isInFlow(int $userId): bool
    {
        $context = $this->getContext($userId);
        return !empty($context['flow']);
    }

    /**
     * Get current flow name
     */
    public function getCurrentFlow(int $userId): ?string
    {
        $context = $this->getContext($userId);
        return $context['flow'] ?? null;
    }

    /**
     * Get current step
     */
    public function getCurrentStep(int $userId): ?string
    {
        $context = $this->getContext($userId);
        return $context['step'] ?? null;
    }

    /**
     * Update search context
     */
    public function updateSearchContext(int $userId, string $keyword, array $results = []): void
    {
        $context = $this->getContext($userId);
        $context['search_context'] = [
            'last_keyword' => $keyword,
            'last_results' => $results,
        ];
        $this->setContext($userId, $context);
    }

    /**
     * Get search context
     */
    public function getSearchContext(int $userId): array
    {
        $context = $this->getContext($userId);
        return $context['search_context'] ?? [
            'last_keyword' => null,
            'last_results' => [],
        ];
    }

    /**
     * Clear all context for a user
     */
    public function clearContext(int $userId): void
    {
        Cache::forget($this->getCacheKey($userId));
    }

    /**
     * Check if context has expired (for display purposes)
     */
    public function isContextExpired(int $userId): bool
    {
        $context = $this->getContext($userId);
        if (empty($context['updated_at'])) {
            return true;
        }
        return (now()->timestamp - $context['updated_at']) > $this->ttl;
    }
}
