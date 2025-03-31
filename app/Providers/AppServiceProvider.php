<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Добавьте этот импорт
use Illuminate\Support\Facades\Schema;
use App\Services\ChatService;
use App\Services\MessageService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
     
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Устанавливаем длину строки по умолчанию для совместимости с MySQL < 5.7.7
        Schema::defaultStringLength(191);
    }
}
