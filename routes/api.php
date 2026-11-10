<?php

use App\Http\Controllers\MarketplaceWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/marketplace/xendit/webhook', MarketplaceWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('marketplace.xendit.webhook');
