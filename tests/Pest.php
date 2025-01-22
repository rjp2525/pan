<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pan\PanConfiguration;

pest()
    ->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => PanConfiguration::reset())
    ->in('Feature', 'Unit');

if (! function_exists('tenant')) {
    /**
     * Mock the tenant helper function
     */
    function tenant(?string $key = null): ?string
    {
        return match ($key) {
            'id' => 'abc',
            default => null,
        };
    }
}

if (! function_exists('tenancy')) {
    /**
     * Mock the tenancy helper function
     */
    function tenancy(): bool
    {
        return true;
    }
}
