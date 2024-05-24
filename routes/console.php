<?php

use App\Console\Commands;
use Illuminate\Support\Facades\Schedule;

Schedule::command('auth:clear-resets')->everyFifteenMinutes();

Schedule::command(Commands\UpdateOnline::class)->everyFifteenMinutes();
Schedule::command(Commands\UpdateStats::class)->cron('5 */6 * * *');

Schedule::command(Commands\Fleet::class)->everyMinute()->runInBackground();
Schedule::command(Commands\Queue::class)->everyMinute()->runInBackground();