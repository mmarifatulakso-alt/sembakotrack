<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('fetch:sembako')
    ->hourly()
    ->withoutOverlapping();