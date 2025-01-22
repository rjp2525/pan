<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Pan\Adapters\Laravel\Repositories\DatabaseAnalyticsRepository;
use Pan\Enums\EventType;

it('creates a new analytic record when none exists', function (): void {
    $repository = app(DatabaseAnalyticsRepository::class);

    $repository->increment('test-event', EventType::CLICK);

    $analytics = DB::table('pan_analytics')->where('name', 'test-event')->first();

    expect($analytics)->not->toBeNull();
    expect($analytics->clicks)->toBe(1);
    expect($analytics->hovers)->toBe(0);
    expect($analytics->impressions)->toBe(0);
});

it('increments an existing analytic record', function (): void {
    DB::table('pan_analytics')->insert(['name' => 'test-event', 'clicks' => 1]);

    $repository = app(DatabaseAnalyticsRepository::class);

    $repository->increment('test-event', EventType::CLICK);

    $analytics = DB::table('pan_analytics')->where('name', 'test-event')->first();

    expect($analytics->clicks)->toBe(2);
});

it('does not exceed the max analytics limit', function (): void {
    DB::table('pan_analytics')->insert(array_map(fn (int $i): array => [
        'name' => "event-$i",
        'clicks' => 0,
        'hovers' => 0,
        'impressions' => 0,
    ], range(1, 50)));

    expect(DB::table('pan_analytics')->count())->toBe(50);

    $repository = app(DatabaseAnalyticsRepository::class);

    $repository->increment('new-event', EventType::CLICK);

    expect(DB::table('pan_analytics')->count())->toBe(50);
    $newEvent = DB::table('pan_analytics')->where('name', 'new-event')->first();
    expect($newEvent)->toBeNull();
});

it('handles concurrent transactions gracefully', function (): void {
    DB::table('pan_analytics')->insert(['name' => 'test-event', 'clicks' => 1]);

    DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

    $repository = app(DatabaseAnalyticsRepository::class);

    $repository->increment('test-event', EventType::CLICK);

    $analytics = DB::table('pan_analytics')->where('name', 'test-event')->first();

    expect($analytics->clicks)->toBe(2);
});
