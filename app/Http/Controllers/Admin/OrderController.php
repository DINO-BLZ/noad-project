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
    /**
     * Affiche la liste des commandes.
     */
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('items')
            ->when(OrderStatus::tryFrom((string) $request->status), fn ($query, $status) => $query->where('status', $status->value))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Affiche le détail d'une commande.
     */
    public function show(Order $order)
    {
        $order->load('items', 'user');

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Met à jour le statut d'une commande.
     */
    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        OrderStatusTransitionService $transitionService,
        CancelOrderAction $cancelOrder
    ) {
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
            Mail::to($order->user->email)->queue(new OrderStatusUpdatedMail($order));
        }

        return back()->with('success', 'Statut de la commande mis à jour.');
    }

    /**
     * Exporte les commandes prêtes à expédier au format CSV pour Yalidine / ZR Express.
     */
    public function exportManifest(Request $request)
    {
        $orders = Order::query()
            ->with('items')
            ->whereIn('status', [OrderStatus::Pending, OrderStatus::Paid])
            ->latest()
            ->get();

        $filename = 'yalidine_zr_manifest_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Reference_Commande',
                'Nom_Destinataire',
                'Telephone',
                'Wilaya',
                'Commune',
                'Adresse',
                'Type_Livraison',
                'Montant_COD',
                'Designation_Articles',
                'Remarque',
            ], ';');

            foreach ($orders as $order) {
                $articles = $order->items->map(function ($item) {
                    return ($item->product_name ?? 'Article').' ('.($item->variant_size ?? '').' x'.$item->quantity.')';
                })->implode(' + ');

                $wilayaClean = preg_replace('/^[0-9]+\s*-\s*/', '', (string) $order->wilaya);

                fputcsv($handle, [
                    $order->formatted_reference,
                    $order->full_name,
                    $order->phone,
                    $wilayaClean,
                    $order->commune ?? $wilayaClean,
                    $order->address,
                    $order->delivery_type === 'desk' ? 'Stopdesk' : 'Domicile',
                    (int) $order->total + (int) $order->shipping_cost,
                    $articles,
                    'Paiement à la livraison (COD)',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
