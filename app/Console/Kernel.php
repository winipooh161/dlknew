<?php
namespace App\Console;

use App\Http\Controllers\DealsController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Определите глобальные зависимости для команды.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Определите расписание команд.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Настройка задачи, которая будет удалять просроченные сделки ежедневно
        $schedule->call(function () {
            (new DealsController)->removeExpiredDeals(); // Вызов функции удаления просроченных сделок
        })->daily();  // Периодичность: ежедневно
    }

    /**
     * Определите команды, которые должны быть выполнены вручную.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
