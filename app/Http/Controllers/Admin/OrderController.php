<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\OrderStatusTransitionService;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use LogicException;

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

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        OrderStatusTransitionService $transitionService,
        CancelOrderAction $cancelOrder
    )
    {
        $newStatus = OrderStatus::from($request->validated()['status']);

        try {
            $order = $newStatus === OrderStatus::Cancelled
                ? $cancelOrder->execute($order)
                : $transitionService->transition($order, $newStatus);
        } catch (LogicException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()])->withInput();
        }

        $order->loadMissing('user');

        if ($order->user) {
            Mail::to($order->user->email)->send(new OrderStatusUpdatedMail($order));
        }

        return back()->with('success', 'Statut de la commande mis à jour.');
    }
}
