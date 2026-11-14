<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = DB::table('notifications')
            ->where('user_id', $request->user()->getKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, int $notification): RedirectResponse
    {
        $record = DB::table('notifications')
            ->where('id', $notification)
            ->where('user_id', $request->user()->getKey())
            ->first();
        abort_unless($record !== null, 404);

        DB::table('notifications')->where('id', $record->id)
            ->where('user_id', $request->user()->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $target = match ($record->reference_type) {
            'letter_request' => route('correspondence.show', $record->reference_id),
            'installation_request', 'internet_subscription', 'subscription_invoice', 'service_request', 'payment' => route('services.index'),
            'assisted_service_session' => route('bumdes.assistance.show', $record->reference_id),
            'checkout_group' => route('marketplace.orders.group', $record->reference_id),
            'product_category' => $request->user()->hasRole('BUMDes Manager')
                ? route('marketplace.management.categories')
                : route('marketplace.index'),
            'seller' => $request->user()->hasRole('BUMDes Manager')
                ? route('marketplace.management.sellers')
                : route('marketplace.seller.application'),
            'product' => $request->user()->hasRole('BUMDes Manager')
                ? route('marketplace.management.products')
                : route('marketplace.seller.products'),
            'order' => route('marketplace.orders.show', $record->reference_id),
            default => route('notifications.index'),
        };

        if ($record->reference_type === 'payment') {
            $orderId = DB::table('payments')->where('id', $record->reference_id)->value('order_id');
            if ($orderId !== null) {
                $target = route('marketplace.orders.show', $orderId);
            }
        }

        return redirect()->to($target);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        DB::table('notifications')
            ->where('user_id', $request->user()->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return to_route('notifications.index')->with('status', 'Semua pemberitahuan ditandai sudah dibaca.');
    }
}
