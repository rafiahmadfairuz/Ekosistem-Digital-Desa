<?php

namespace App\Http\Controllers;

use App\Services\MarketplaceWebhookProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MarketplaceWebhookController
{
    public function __invoke(Request $request, MarketplaceWebhookProcessor $processor): Response
    {
        $processor->process($request->all(), [
            'x-callback-token' => (string) $request->header('x-callback-token', ''),
        ]);

        return response()->noContent();
    }
}
