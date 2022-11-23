<?php

use Illuminate\Support\Facades\Route;

Route::get('azure-calendar/callback', '\AnsJabar\LaravelAzureCalendar\Calendar@callbackFromAzure');