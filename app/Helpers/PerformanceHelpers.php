<?php

use Illuminate\Database\Eloquent\Builder;

if (!function_exists('optimizeQuery')) {
    /**
     * Add common eager loads and optimizations to a query builder
     */
    function optimizeQuery(Builder $query, array $includes = []): Builder
    {
        $defaultIncludes = ['user', 'team'];
        $includes = array_merge($defaultIncludes, $includes);

        return $query->with(array_unique($includes));
    }
}

if (!function_exists('isOnline')) {
    /**
     * Check if application is in online mode (not offline mode)
     */
    function isOnline(): bool
    {
        return config('offline.enabled', true);
    }
}

if (!function_exists('isPWAEnabled')) {
    /**
     * Check if PWA support is enabled
     */
    function isPWAEnabled(): bool
    {
        return config('pwa') && config('pwa.enabled', true);
    }
}
