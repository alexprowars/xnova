<?php

use App\Console\Commands;
use Illuminate\Support\Facades\Schedule;

//Schedule::command('auth:clear-resets')->everyFifteenMinutes();

Schedule::command('model:prune')->dailyAt('4:10');

Schedule::command(Commands\UpdateOnline::class)->everyFifteenMinutes();
Schedule::command(Commands\UpdateStats::class)->cron('5 */6 * * *');

Schedule::command(Commands\Fleet::class)->everyFiveSeconds()->runInBackground();
Schedule::command(Commands\Queue::class)->everyFiveSeconds()->runInBackground();
