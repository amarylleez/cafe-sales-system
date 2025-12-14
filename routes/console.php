<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the expired stock processing to run daily at 3 PM (shop closing time: 6 AM - 3 PM)
Schedule::command('stock:process-expired')->dailyAt('15:00');
