<?php

namespace App\Http\Controllers;

use App\Services\MarketplacePayoutWebhookProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MarketplacePayoutWebhookController
{
    public function __invoke(Request $request, MarketplacePayoutWebhookProcessor $processor): Response
    {
        $processor->process($request->all(), (string) $request->header('x-callback-token', ''));

        return response()->noContent();
    }
}
