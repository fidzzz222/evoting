<?php

namespace App\Providers;

use App\Models\Event;
use App\Policies\EventPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // registrasi service aplikasi
    public function register(): void
    {
        //
    }

    // bootstrap service aplikasi, pagination, dan policy
    public function boot(): void
    {
        Paginator::useTailwind();
        Gate::policy(Event::class, EventPolicy::class);
    }
}
