<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:handle-price-above-alerts')->everyMinute();
Schedule::command('app:handle-percent-change-alerts')->everyMinute();

