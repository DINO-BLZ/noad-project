<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DropWhitelist;
use Illuminate\Http\Request;

class WhitelistController extends Controller
{
    /**
     * Affiche la liste des candidatures whitelist.
     */
    public function index(Request $request)
    {
        $query = DropWhitelist::with(['user', 'drop.products'])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Filtre statut
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | Recherche
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $term = trim($request->search);

            $query->where(function ($q) use ($term) {

                $q->where('fullname', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%")
                    ->orWhere('wilaya', 'LIKE', "%{$term}%");

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $candidates = $query
            ->paginate(25)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | KPI
        |--------------------------------------------------------------------------
        */

        $totalCount = DropWhitelist::with(['user', 'drop.products'])->count();

        $approvedCount = DropWhitelist::with(['user', 'drop.products'])->where(
            'status',
            'approved'
        )->count();

        $pendingCount = DropWhitelist::with(['user', 'drop.products'])->where(
            'status',
            'pending'
        )->count();

        $rejectedCount = DropWhitelist::with(['user', 'drop.products'])->where(
            'status',
            'rejected'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Vue
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.whitelist.index',
            compact(
                'candidates',
                'totalCount',
                'approvedCount',
                'pendingCount',
                'rejectedCount'
            )
        );
    }
}