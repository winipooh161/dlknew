<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use App\Broadcasting\FirebaseBroadcaster;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        // Регистрация firebase-драйвера вещания
        Broadcast::extend('firebase', function ($app, $config) {
            logger()->info('Registering Firebase Broadcaster');
            return new FirebaseBroadcaster();
        });

        require base_path('routes/channels.php');
    }
}
