<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Drops\ApproveWhitelistAction;
use App\Enums\WhitelistStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DropRequest;
use App\Mail\WhitelistStatusMail;
use App\Models\Drop;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

class DropController extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     *
     * Affiche la liste des Drops ainsi que les KPI :
     * - Drop actif
     * - Drops à venir
     * - Drops archivés
     * - ventes
     * - chiffre d'affaires
     * - quotas
     * - whitelist
     */
    public function index()
    {
        /*
         * --------------------------------------------------------
         * Filtre de statut
         * --------------------------------------------------------
         */
        $status = request('status');

        $drops = Drop::query()
            ->withStockStatus()
            ->withCount('products')
            ->when($status === 'active', fn ($query) => $query->active())
            ->when($status === 'upcoming', fn ($query) => $query->upcoming())
            ->when($status === 'ended', fn ($query) => $query->ended())
            ->orderByDesc('start_date')
            ->get();

        /*
         * --------------------------------------------------------
         * Drop actif
         * --------------------------------------------------------
         */
        $activeDrop = Drop::current()->first();

        /*
         * --------------------------------------------------------
         * Drops à venir
         * --------------------------------------------------------
         */
        $upcomingDrops = Drop::upcoming()
            ->withStockStatus()
            ->orderBy('start_date')
            ->get();

        /*
         * --------------------------------------------------------
         * Drops archivés
         * --------------------------------------------------------
         */
        $archivedDrops = Drop::query()
            ->ended()
            ->withStockStatus()
            ->orderByDesc('end_date')
            ->get();

        /*
         * --------------------------------------------------------
         * Nombre de demandes whitelist du prochain Drop
         * --------------------------------------------------------
         */
        $whitelistCount = $upcomingDrops
            ->first()
            ?->whitelists()
            ->count() ?? 0;

        /*
         * --------------------------------------------------------
         * KPI du Drop actif
         * --------------------------------------------------------
         */
        $activeDropMonitoring = $activeDrop?->quotaMonitoring();
        $activeDropRevenue = $activeDropMonitoring['revenue'] ?? 0;
        $activeDropSold = $activeDropMonitoring['sold'] ?? 0;
        $activeDropSoldPercentage = $activeDropMonitoring['percentage'] ?? 0;
        $activeDropQuota = $activeDropMonitoring['quota'] ?? 0;
        $activeDropSalesRate = null;

        /*
         * --------------------------------------------------------
         * CA des Drops archivés
         * --------------------------------------------------------
         */
        $archivedMonitoring = Drop::quotaMonitoringFor($archivedDrops);
        $archivedRevenue = $archivedMonitoring->sum('revenue');

        /*
         * --------------------------------------------------------
         * Vue
         * --------------------------------------------------------
         */
        return view(
            'admin.drops.index',
            compact(
                'drops',
                'activeDrop',
                'upcomingDrops',
                'archivedDrops',
                'activeDropRevenue',
                'activeDropSold',
                'activeDropSoldPercentage',
                'activeDropQuota',
                'activeDropSalesRate',
                'whitelistCount',
                'archivedRevenue',
            )
        );
    }

    /**
     * ============================================================
     * STORE
     * ============================================================
     *
     * Crée un nouveau Drop et associe ses produits avec leur quota.
     */
    public function store(DropRequest $request)
    {
        $this->authorize('create', Drop::class);

        $data = $request->validated();

        /*
         * Génération du slug.
         */
        $data['slug'] = Str::slug($data['name'])
            .'-'
            .uniqid();

        /*
         * Validation des nouveaux produits avant
         * d'ouvrir la transaction.
         */
        $validatedNewProducts = $this->prepareNewProducts(
            $request->input('new_products', [])
        );

        DB::transaction(function () use (
            $request,
            $data,
            $validatedNewProducts
        ) {
            /*
             * Création du Drop.
             */
            $drop = Drop::create($data);

            /*
             * Produits déjà existants.
             */
            $productIds = $request->input(
                'products',
                []
            );

            /*
             * Création des nouveaux produits.
             */
            foreach (
                $validatedNewProducts as $index => $newProduct
            ) {
                $productIds[] =
                    $this->createProductFromNewProductData(
                        $request,
                        $index,
                        $newProduct
                    );
            }

            /*
             * Quotas des produits.
             */
            $productQuotas = $request->input(
                'product_quotas',
                []
            );

            /*
             * Prépare la relation pivot :
             *
             * product_id => [
             *     quota => ...
             * ]
             */
            $productsWithQuota = collect($productIds)
                ->mapWithKeys(
                    function ($id) use ($productQuotas) {
                        return [
                            $id => [
                                'quota' => $productQuotas[$id] ?? 0,
                            ],
                        ];
                    }
                )
                ->all();

            /*
             * Synchronisation Drop <-> Produits.
             */
            $drop->products()->sync(
                $productsWithQuota
            );
        });

        return redirect()
            ->route('admin.drops.index')
            ->with(
                'success',
                'Drop créé avec succès.'
            );
    }

    /**
     * ============================================================
     * EDIT
     * ============================================================
     *
     * Affiche la page de modification d'un Drop.
     *
     * La vue attend :
     * - $drop
     * - $whitelistRequests
     * - $dropRevenue
     * - $dropSold
     * - $dropQuota
     * - $dropSoldPercentage
     * - $whitelistCount
     */
    public function edit(Drop $drop)
    {
        $this->authorize('update', $drop);

        /*
         * --------------------------------------------------------
         * Chargement du Drop et de ses produits/variantes
         * --------------------------------------------------------
         *
         * Les variantes sont utilisées par la vue pour afficher
         * les informations disponibles sur chaque produit.
         *
         * Le quota vient du pivot drop_product.
         */
        $drop->loadMissing([
            'products.variants',
        ]);

        /*
         * --------------------------------------------------------
         * Whitelist
         * --------------------------------------------------------
         *
         * Cette page affiche les demandes en lecture seule.
         * Les actions Approuver/Refuser restent disponibles
         * sur la page dédiée à la whitelist.
         */
        $whitelistRequests = $drop
            ->whitelists()
            ->with('user')
            ->latest()
            ->get();

        /*
         * --------------------------------------------------------
         * Initialisation des KPI
         * --------------------------------------------------------
         */
        $dropMonitoring = $drop->quotaMonitoring();
        $dropRevenue = $dropMonitoring['revenue'];
        $dropSold = $dropMonitoring['sold'];
        $dropQuota = $dropMonitoring['quota'];
        $dropSoldPercentage = $dropMonitoring['percentage'];

        /*
         * --------------------------------------------------------
         * Nombre total de demandes whitelist
         * --------------------------------------------------------
         */
        $whitelistCount = $drop
            ->whitelists()
            ->count();

        /*
         * --------------------------------------------------------
         * Vue
         * --------------------------------------------------------
         */
        return view(
            'admin.drops.edit',
            compact(
                'drop',
                'whitelistRequests',
                'dropRevenue',
                'dropSold',
                'dropQuota',
                'dropSoldPercentage',
                'whitelistCount',
            )
        );
    }

    /**
     * ============================================================
     * UPDATE
     * ============================================================
     *
     * Met à jour un Drop et ses quotas produits.
     */
    public function update(
        DropRequest $request,
        Drop $drop
    ) {
        $this->authorize('update', $drop);

        $data = $request->validated();

        /*
         * Validation des nouveaux produits éventuels.
         */
        $validatedNewProducts = $this->prepareNewProducts(
            $request->input('new_products', [])
        );

        DB::transaction(function () use (
            $request,
            $data,
            $drop,
            $validatedNewProducts
        ) {
            /*
             * Mise à jour des informations du Drop.
             */
            $drop->update($data);

            /*
             * IMPORTANT :
             *
             * La vue edit envoie :
             *
             * <input type="hidden"
             *        name="products[]"
             *        value="...">
             *
             * Cela permet de conserver les produits existants
             * lors du sync().
             */
            $productIds = $request->input(
                'products',
                []
            );

            /*
             * Ajout éventuel de nouveaux produits.
             */
            foreach (
                $validatedNewProducts as $index => $newProduct
            ) {
                $productIds[] =
                    $this->createProductFromNewProductData(
                        $request,
                        $index,
                        $newProduct
                    );
            }

            /*
             * Récupération des quotas.
             */
            $productQuotas = $request->input(
                'product_quotas',
                []
            );

            /*
             * Préparation de la relation pivot.
             */
            $productsWithQuota = collect($productIds)
                ->mapWithKeys(
                    function ($id) use ($productQuotas) {
                        return [
                            $id => [
                                'quota' => $productQuotas[$id] ?? 0,
                            ],
                        ];
                    }
                )
                ->all();

            /*
             * Synchronisation des produits et quotas.
             */
            $drop->products()->sync(
                $productsWithQuota
            );
        });

        return redirect()
            ->route('admin.drops.index')
            ->with(
                'success',
                'Drop mis à jour.'
            );
    }

    /**
     * ============================================================
     * DESTROY
     * ============================================================
     *
     * Supprime un Drop.
     */
    public function destroy(Drop $drop)
    {
        $this->authorize('delete', $drop);

        $drop->delete();

        return redirect()
            ->route('admin.drops.index')
            ->with(
                'success',
                'Drop supprimé.'
            );
    }

    /**
     * ============================================================
     * APPROVE WHITELIST
     * ============================================================
     *
     * Approuve une demande de whitelist.
     */
    public function approveWhitelist(
        Drop $drop,
        $whitelistId,
        ApproveWhitelistAction $approveWhitelist
    ) {
        /*
         * Récupération de la demande uniquement pour ce Drop.
         */
        $whitelist = $drop
            ->whitelists()
            ->with('user')
            ->findOrFail($whitelistId);

        /*
         * Autorisation.
         */
        $this->authorize(
            'approve',
            $whitelist
        );

        try {
            /*
             * L'Action gère la logique métier d'approbation.
             */
            $whitelist = $approveWhitelist->execute(
                $drop,
                $whitelist
            );
        } catch (LogicException $exception) {
            return back()->withErrors([
                'whitelist' => $exception->getMessage(),
            ]);
        }

        /*
         * Notification utilisateur.
         */
        Mail::to($whitelist->user->email)
            ->queue(
                new WhitelistStatusMail(
                    $whitelist
                )
            );

        return back()->with(
            'success',
            'Demande approuvée.'
        );
    }

    /**
     * ============================================================
     * REJECT WHITELIST
     * ============================================================
     *
     * Refuse une demande de whitelist.
     */
    public function rejectWhitelist(
        Drop $drop,
        $whitelistId
    ) {
        /*
         * Récupération de la demande uniquement pour ce Drop.
         */
        $whitelist = $drop
            ->whitelists()
            ->with('user')
            ->findOrFail($whitelistId);

        /*
         * Autorisation.
         */
        $this->authorize(
            'reject',
            $whitelist
        );

        try {
            $whitelist = DB::transaction(function () use ($drop, $whitelistId) {
                $lockedDrop = Drop::query()
                    ->whereKey($drop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedWhitelist = $lockedDrop
                    ->whitelists()
                    ->whereKey($whitelistId)
                    ->lockForUpdate()
                    ->with('user')
                    ->firstOrFail();

                if ($lockedWhitelist->status !== WhitelistStatus::Pending->value) {
                    throw new LogicException('Seules les demandes en attente peuvent être refusées.');
                }

                $lockedWhitelist->update([
                    'status' => WhitelistStatus::Rejected->value,
                ]);
                $lockedWhitelist->setRelation('drop', $lockedDrop);

                return $lockedWhitelist;
            });
        } catch (LogicException $exception) {
            return back()->withErrors([
                'whitelist' => $exception->getMessage(),
            ]);
        }

        /*
         * Notification utilisateur.
         */
        Mail::to($whitelist->user->email)
            ->queue(
                new WhitelistStatusMail(
                    $whitelist
                )
            );

        return back()->with(
            'success',
            'Demande refusée.'
        );
    }

    /**
     * ============================================================
     * PREPARE NEW PRODUCTS
     * ============================================================
     *
     * Valide les produits créés directement depuis le formulaire
     * de Drop.
     *
     * Vérifie notamment :
     * - nom/prix
     * - doublons taille/couleur
     * - doublons SKU
     * - SKU déjà existants en base
     */
    private function prepareNewProducts(
        array $newProducts
    ): array {
        $errors = [];
        $validated = [];

        /*
         * --------------------------------------------------------
         * Validation produit par produit
         * --------------------------------------------------------
         */
        foreach (
            $newProducts as $index => $newProduct
        ) {
            $name = $newProduct['name'] ?? null;
            $price = $newProduct['price'] ?? null;

            /*
             * Ligne complètement vide :
             * on l'ignore.
             */
            if (
                empty($name) &&
                empty($price)
            ) {
                continue;
            }

            /*
             * Nom et prix obligatoires ensemble.
             */
            if (
                empty($name) ||
                empty($price)
            ) {
                $errors[
                    "new_products.$index"
                ] = [
                    'Produit #'.
                    ($index + 1).
                    ' : le nom et le prix sont tous les deux requis.',
                ];

                continue;
            }

            /*
             * ----------------------------------------------------
             * Détection des doublons taille/couleur
             * ----------------------------------------------------
             */
            $seenCombinations = [];

            foreach (
                $newProduct['sizes'] ?? [] as $sizeData
            ) {
                /*
                 * Ligne de variante incomplète :
                 * on l'ignore ici.
                 */
                if (
                    empty($sizeData['size']) ||
                    ! isset($sizeData['stock'])
                ) {
                    continue;
                }

                $combination =
                    ($sizeData['size'] ?? '').
                    '|'.
                    ($sizeData['color'] ?? '');

                /*
                 * Même combinaison déjà rencontrée.
                 */
                if (
                    isset(
                        $seenCombinations[$combination]
                    )
                ) {
                    $errors[
                        "new_products.$index.sizes"
                    ] = [
                        'Produit #'.
                        ($index + 1).
                        ' : la combinaison taille/couleur "'.
                        ($sizeData['size'] ?? '').
                        ' / '.
                        ($sizeData['color'] ?? '').
                        '" est en double.',
                    ];
                }

                $seenCombinations[
                    $combination
                ] = true;
            }

            $validated[$index] = $newProduct;
        }

        /*
         * --------------------------------------------------------
         * Vérification globale des SKU
         * --------------------------------------------------------
         */
        $allSkus = [];

        foreach (
            $validated as $newProduct
        ) {
            foreach (
                $newProduct['sizes'] ?? [] as $sizeData
            ) {
                if (
                    ! empty($sizeData['sku'])
                ) {
                    $allSkus[] =
                        $sizeData['sku'];
                }
            }
        }

        if (! empty($allSkus)) {
            /*
             * ----------------------------------------------------
             * Doublons dans la requête
             * ----------------------------------------------------
             */
            $skuCounts = array_count_values(
                $allSkus
            );

            $duplicateSkus = array_keys(
                array_filter(
                    $skuCounts,
                    fn (
                        int $count
                    ): bool => $count > 1
                )
            );

            if (
                ! empty($duplicateSkus)
            ) {
                $errors['new_products'][] =
                    'Doublon de SKU dans les nouveaux produits : '.
                    implode(
                        ', ',
                        $duplicateSkus
                    );
            }

            /*
             * ----------------------------------------------------
             * SKU déjà présents en base
             * ----------------------------------------------------
             */
            $existingSkus = Variant::query()
                ->whereIn(
                    'sku',
                    array_unique($allSkus)
                )
                ->pluck('sku')
                ->all();

            if (
                ! empty($existingSkus)
            ) {
                $errors['new_products'][] =
                    'SKU déjà utilisé en base : '.
                    implode(
                        ', ',
                        $existingSkus
                    );
            }
        }

        /*
         * --------------------------------------------------------
         * Retourne toutes les erreurs
         * --------------------------------------------------------
         */
        if (! empty($errors)) {
            throw ValidationException::withMessages(
                $errors
            );
        }

        return $validated;
    }

    /**
     * ============================================================
     * CREATE PRODUCT FROM NEW PRODUCT DATA
     * ============================================================
     *
     * Crée un Product ainsi que ses Variants depuis les données
     * envoyées par le formulaire.
     */
    private function createProductFromNewProductData(
        DropRequest $request,
        int $index,
        array $newProduct
    ): int {
        $imagePath = null;

        /*
         * --------------------------------------------------------
         * Upload de l'image
         * --------------------------------------------------------
         */
        if (
            $request->hasFile(
                "new_products.$index.image"
            )
        ) {
            $imagePath = $request
                ->file(
                    "new_products.$index.image"
                )
                ->store(
                    'products',
                    'public'
                );
        }

        try {
            /*
             * ----------------------------------------------------
             * Création du produit
             * ----------------------------------------------------
             */
            $product = Product::create([
                'name' => $newProduct['name'],

                'slug' => Str::slug(
                    $newProduct['name']
                ).
                    '-'.
                    uniqid(),

                'price' => $newProduct['price'],

                'image' => $imagePath,

                'category_id' => $newProduct['category_id'],
            ]);

            /*
             * ----------------------------------------------------
             * Création des variantes
             * ----------------------------------------------------
             */
            $sizes =
                $newProduct['sizes'] ?? [];

            $hasValidSize = false;

            foreach (
                $sizes as $sizeData
            ) {
                /*
                 * Une variante est valide si :
                 * - une taille est renseignée
                 * - le stock est renseigné
                 */
                if (
                    ! empty($sizeData['size']) &&
                    isset($sizeData['stock'])
                ) {
                    $product
                        ->variants()
                        ->create([
                            'size' => $sizeData['size'],

                            'stock' => $sizeData['stock'],

                            'sku' => isset($sizeData['sku']) && trim($sizeData['sku']) !== ''
                                ? trim($sizeData['sku'])
                                : null,

                            'color' => $sizeData['color']
                                ?? '',
                        ]);

                    $hasValidSize = true;
                }
            }

            /*
             * ----------------------------------------------------
             * Produit sans variante valide
             * ----------------------------------------------------
             *
             * On crée une variante par défaut.
             */
            if (! $hasValidSize) {
                $product
                    ->variants()
                    ->create([
                        'size' => 'Unique',
                        'stock' => 1,
                    ]);
            }

            return $product->id;
        } catch (\Throwable $exception) {
            /*
             * Si la création échoue après l'upload,
             * on supprime l'image afin d'éviter un fichier
             * orphelin.
             */
            if ($imagePath) {
                Storage::disk('public')
                    ->delete($imagePath);
            }

            throw $exception;
        }
    }
}
