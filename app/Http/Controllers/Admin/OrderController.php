<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items', 'user');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $newStatus = $request->validated()['status'];
        $wasAlreadyCancelled = $order->status === 'cancelled';

        DB::transaction(function () use ($order, $newStatus, $wasAlreadyCancelled) {
            // On ne recrédite le stock que si on PASSE à "cancelled"
            // (et pas si la commande était déjà annulée avant).
            if ($newStatus === 'cancelled' && ! $wasAlreadyCancelled) {
                $order->load('items');

                foreach ($order->items as $item) {
                    if (! $item->variant_id) {
                        continue;
                    }

                    // Verrou pour éviter une course avec un achat concurrent
                    // sur la même variante pendant le réapprovisionnement.
                    Variant::where('id', $item->variant_id)
                        ->lockForUpdate()
                        ->increment('stock', $item->quantity);
                }
            }

            $order->update(['status' => $newStatus]);
        });

        $order->loadMissing('user');

        if ($order->user) {
            Mail::to($order->user->email)->send(new OrderStatusUpdatedMail($order));
        }

        return back()->with('success', 'Statut de la commande mis à jour.');
    }
}
