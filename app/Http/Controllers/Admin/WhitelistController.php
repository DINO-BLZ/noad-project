<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhitelistApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhitelistController extends Controller
{
    /**
     * Affiche la liste des candidatures whitelist.
     */
    public function index(Request $request)
    {
        $query = WhitelistApplication::query()
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

        $totalCount = WhitelistApplication::count();

        $approvedCount = WhitelistApplication::where(
            'status',
            'approved'
        )->count();

        $pendingCount = WhitelistApplication::where(
            'status',
            'pending'
        )->count();

        $rejectedCount = WhitelistApplication::where(
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


    /**
     * Approuver une candidature.
     */
    public function approve($id)
    {
        $application = WhitelistApplication::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Éviter de régénérer un code si déjà approuvé
        |--------------------------------------------------------------------------
        */

        if ($application->status === 'approved') {
            return back()->with(
                'warning',
                'Cette candidature est déjà approuvée.'
            );
        }

        $application->update([
            'status' => 'approved',
            'access_code' => 'ND-RESIST-' . strtoupper(Str::random(4)),
            'approved_at' => now(),
        ]);

        return back()->with(
            'success',
            'Candidature approuvée avec succès.'
        );
    }


    /**
     * Rejeter une candidature.
     */
    public function reject($id)
    {
        $application = WhitelistApplication::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Éviter de rejeter deux fois
        |--------------------------------------------------------------------------
        */

        if ($application->status === 'rejected') {
            return back()->with(
                'warning',
                'Cette candidature est déjà rejetée.'
            );
        }

        $application->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return back()->with(
            'warning',
            'Candidature rejetée.'
        );
    }


    /**
     * Approve plusieurs candidatures.
     */
    public function bulk(Request $request)
    {
        $ids = $request->input(
            'selected_candidates',
            []
        );

        if (empty($ids)) {
            return back()->with(
                'warning',
                'Aucune candidature sélectionnée.'
            );
        }

        $applications = WhitelistApplication::whereIn(
            'id',
            $ids
        )->get();

        foreach ($applications as $application) {

            if ($application->status === 'approved') {
                continue;
            }

            $application->update([
                'status' => 'approved',
                'access_code' => 'ND-RESIST-' . strtoupper(Str::random(4)),
                'approved_at' => now(),
            ]);
        }

        return back()->with(
            'success',
            count($ids) . ' candidature(s) approuvée(s).'
        );
    }
}