<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Drops\ApproveWhitelistAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DropRequest;
use App\Mail\WhitelistStatusMail;
use App\Models\Category;
use App\Models\Drop;
use App\Models\OrderItem;
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
            ->orderByDesc('start_date')
            ->get();

        if (in_array($status, ['active', 'upcoming', 'ended'], true)) {
            $drops = $drops
                ->filter(fn (Drop $drop) => match ($status) {
                    'active' => $drop->isActive(),
                    'upcoming' => $drop->isUpcoming(),
                    'ended' => $drop->isEnded(),
                })
                ->values();
        }

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
            ->orderBy('start_date')
            ->get();

        /*
         * --------------------------------------------------------
         * Drops archivés
         * --------------------------------------------------------
         */
        $archivedDrops = Drop::query()
            ->get()
            ->filter(
                fn (Drop $drop) => $drop->isEnded()
            )
            ->sortByDesc('end_date')
            ->values();

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
        $activeDropRevenue = 0;
        $activeDropSold = 0;
        $activeDropSoldPercentage = 0;
        $activeDropQuota = 0;
        $activeDropSalesRate = null;

        if ($activeDrop) {
            $activeDrop->loadMissing('products');

            $productIds = $activeDrop->products
                ->pluck('id')
                ->unique()
                ->values();

            /*
             * Quota total du Drop.
             */
            $activeDropQuota = $activeDrop->products->sum(
                fn ($product) => (int) (
                    $product->pivot->quota ?? 0
                )
            );

            if ($productIds->isNotEmpty()) {
                /*
                 * ------------------------------------------------
                 * Commandes considérées comme confirmées
                 * ------------------------------------------------
                 *
                 * paid      = payée
                 * shipped   = expédiée
                 * delivered = livrée
                 */
                $activeDropSalesQuery = OrderItem::query()
                    ->whereHas(
                        'variant',
                        fn ($query) => $query->whereIn(
                            'product_id',
                            $productIds
                        )
                    )
                    ->whereHas(
                        'order',
                        fn ($query) => $query->whereIn(
                            'status',
                            [
                                'paid',
                                'shipped',
                                'delivered',
                            ]
                        )
                    );

                /*
                 * Quantité vendue.
                 */
                $activeDropSold = (int) (
                    (clone $activeDropSalesQuery)
                        ->sum('quantity')
                );

                /*
                 * Chiffre d'affaires.
                 *
                 * On utilise le prix enregistré dans OrderItem
                 * afin de conserver le prix réellement payé.
                 */
                $activeDropRevenue = (float) (
                    (clone $activeDropSalesQuery)
                        ->selectRaw(
                            'SUM(quantity * price) as total'
                        )
                        ->value('total') ?? 0
                );
            }

            /*
             * Pourcentage de quota vendu.
             */
            $activeDropSoldPercentage = $activeDropQuota > 0
                ? round(
                    ($activeDropSold / $activeDropQuota) * 100
                )
                : 0;
        }

        /*
         * --------------------------------------------------------
         * CA des Drops archivés
         * --------------------------------------------------------
         */
        $archivedRevenue = 0;

        $archivedProductIds = $archivedDrops
            ->flatMap(
                fn ($drop) => $drop->products
            )
            ->pluck('id')
            ->unique()
            ->values();

        if ($archivedProductIds->isNotEmpty()) {
            $archivedRevenue = (float) (
                OrderItem::query()
                    ->whereHas(
                        'variant',
                        fn ($query) => $query->whereIn(
                            'product_id',
                            $archivedProductIds
                        )
                    )
                    ->whereHas(
                        'order',
                        fn ($query) => $query->whereIn(
                            'status',
                            [
                                'paid',
                                'shipped',
                                'delivered',
                            ]
                        )
                    )
                    ->selectRaw(
                        'SUM(quantity * price) as total'
                    )
                    ->value('total') ?? 0
            );
        }

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
     * CREATE
     * ============================================================
     *
     * Affiche le formulaire de création d'un Drop.
     */
    public function create()
    {
        $products = Product::all();
        $categories = Category::all();

        return view(
            'admin.drops.create',
            compact(
                'products',
                'categories'
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
            . '-'
            . uniqid();

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
                                'quota' =>
                                    $productQuotas[$id] ?? 0,
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
        $dropRevenue = 0;
        $dropSold = 0;

        /*
         * Somme des quotas de tous les produits
         * associés au Drop.
         */
        $dropQuota = $drop->products->sum(
            fn ($product) => (int) (
                $product->pivot->quota ?? 0
            )
        );

        $dropSoldPercentage = 0;

        /*
         * --------------------------------------------------------
         * IDs des produits du Drop
         * --------------------------------------------------------
         */
        $productIds = $drop->products
            ->pluck('id')
            ->unique()
            ->values();

        /*
         * --------------------------------------------------------
         * Calcul des ventes et du CA
         * --------------------------------------------------------
         */
        if ($productIds->isNotEmpty()) {
            /*
             * Commandes confirmées uniquement.
             */
            $dropSalesQuery = OrderItem::query()
                ->whereHas(
                    'variant',
                    fn ($query) => $query->whereIn(
                        'product_id',
                        $productIds
                    )
                )
                ->whereHas(
                    'order',
                    fn ($query) => $query->whereIn(
                        'status',
                        [
                            'paid',
                            'shipped',
                            'delivered',
                        ]
                    )
                );

            /*
             * Quantité vendue.
             */
            $dropSold = (int) (
                (clone $dropSalesQuery)
                    ->sum('quantity')
            );

            /*
             * Chiffre d'affaires.
             *
             * Le prix utilisé est celui de OrderItem,
             * donc le prix réellement enregistré au moment
             * de la commande.
             */
            $dropRevenue = (float) (
                (clone $dropSalesQuery)
                    ->selectRaw(
                        'SUM(quantity * price) as total'
                    )
                    ->value('total') ?? 0
            );
        }

        /*
         * --------------------------------------------------------
         * Pourcentage de quota vendu
         * --------------------------------------------------------
         */
        $dropSoldPercentage = $dropQuota > 0
            ? round(
                ($dropSold / $dropQuota) * 100
            )
            : 0;

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
                                'quota' =>
                                    $productQuotas[$id] ?? 0,
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
                'whitelist' =>
                    $exception->getMessage(),
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

        /*
         * Mise à jour du statut.
         */
        $whitelist->update([
            'status' => 'rejected',
        ]);

        /*
         * On rattache explicitement le Drop à la relation
         * pour que le Mailable puisse l'utiliser si nécessaire.
         */
        $whitelist->setRelation(
            'drop',
            $drop
        );

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
                    'Produit #' .
                    ($index + 1) .
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
                $newProduct['sizes'] ?? []
                as $sizeData
            ) {
                /*
                 * Ligne de variante incomplète :
                 * on l'ignore ici.
                 */
                if (
                    empty($sizeData['size']) ||
                    !isset($sizeData['stock'])
                ) {
                    continue;
                }

                $combination =
                    ($sizeData['size'] ?? '') .
                    '|' .
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
                        'Produit #' .
                        ($index + 1) .
                        ' : la combinaison taille/couleur "' .
                        ($sizeData['size'] ?? '') .
                        ' / ' .
                        ($sizeData['color'] ?? '') .
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
                $newProduct['sizes'] ?? []
                as $sizeData
            ) {
                if (
                    !empty($sizeData['sku'])
                ) {
                    $allSkus[] =
                        $sizeData['sku'];
                }
            }
        }

        if (!empty($allSkus)) {
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
                !empty($duplicateSkus)
            ) {
                $errors['new_products'][] =
                    'Doublon de SKU dans les nouveaux produits : ' .
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
                !empty($existingSkus)
            ) {
                $errors['new_products'][] =
                    'SKU déjà utilisé en base : ' .
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
        if (!empty($errors)) {
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
                'name' =>
                    $newProduct['name'],

                'slug' =>
                    Str::slug(
                        $newProduct['name']
                    ) .
                    '-' .
                    uniqid(),

                'price' =>
                    $newProduct['price'],

                'image' =>
                    $imagePath,

                'category_id' =>
                    $newProduct['category_id']
                    ?? null,
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
                    !empty($sizeData['size']) &&
                    isset($sizeData['stock'])
                ) {
                    $product
                        ->variants()
                        ->create([
                            'size' =>
                                $sizeData['size'],

                            'stock' =>
                                $sizeData['stock'],

                            'sku' =>
                                $sizeData['sku']
                                ?? null,

                            'color' =>
                                $sizeData['color']
                                ?? null,
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
            if (!$hasValidSize) {
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