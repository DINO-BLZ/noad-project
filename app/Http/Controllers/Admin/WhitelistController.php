<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WhitelistStatus;
use App\Http\Controllers\Controller;
use App\Models\DropWhitelist;
use Illuminate\Http\Request;

class WhitelistController extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     *
     * Affiche la liste des candidatures whitelist avec :
     * - filtre par statut
     * - recherche utilisateur
     * - pagination
     * - KPI globaux
     */
    public function index(Request $request)
    {
        /*
         * --------------------------------------------------------
         * REQUÊTE PRINCIPALE
         * --------------------------------------------------------
         *
         * Chargement anticipé des relations utilisées par la vue.
         */
        $query = DropWhitelist::with([
            'user',
            'drop.products',
        ])->latest();

        /*
         * --------------------------------------------------------
         * FILTRE PAR STATUT
         * --------------------------------------------------------
         *
         * Exemples :
         * - pending
         * - approved
         * - rejected
         */
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
         * --------------------------------------------------------
         * RECHERCHE
         * --------------------------------------------------------
         *
         * La recherche porte sur :
         * - nom de l'utilisateur
         * - email de l'utilisateur
         *
         * Les informations utilisateur sont stockées
         * dans la table users.
         */
        if ($request->filled('search')) {
            $term = trim(
                $request->search
            );

            if ($term !== '') {
                $query->whereHas(
                    'user',
                    function ($userQuery) use ($term) {
                        $userQuery->where(
                            function ($q) use ($term) {
                                $q->where(
                                    'name',
                                    'LIKE',
                                    "%{$term}%"
                                )->orWhere(
                                    'email',
                                    'LIKE',
                                    "%{$term}%"
                                );
                            }
                        );
                    }
                );
            }
        }

        /*
         * --------------------------------------------------------
         * PAGINATION
         * --------------------------------------------------------
         *
         * 25 candidatures par page.
         *
         * withQueryString() permet de conserver les filtres
         * et la recherche lorsqu'on change de page.
         */
        $candidates = $query
            ->paginate(25)
            ->withQueryString();

        /*
         * --------------------------------------------------------
         * KPI
         * --------------------------------------------------------
         */

        /*
         * Nombre total de candidatures.
         */
        $totalCount = DropWhitelist::count();

        /*
         * Nombre de candidatures approuvées.
         */
        $approvedCount = DropWhitelist::where(
            'status',
            WhitelistStatus::Approved->value
        )->count();

        /*
         * Nombre de candidatures en attente.
         */
        $pendingCount = DropWhitelist::where(
            'status',
            WhitelistStatus::Pending->value
        )->count();

        /*
         * Nombre de candidatures refusées.
         */
        $rejectedCount = DropWhitelist::where(
            'status',
            WhitelistStatus::Rejected->value
        )->count();

        /*
         * --------------------------------------------------------
         * VUE
         * --------------------------------------------------------
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
