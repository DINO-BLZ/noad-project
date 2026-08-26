<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DropController as AdminDropController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\WhitelistController as AdminWhitelistController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DropController;
use App\Http\Controllers\DropRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WhitelistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/boutique', [ProductController::class, 'index'])
    ->name('shop.index');

Route::get('/produit/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show');

Route::get('/recherche', [SearchController::class, 'index'])
    ->name('search.index');

Route::get('/recherche/suggestions', [SearchController::class, 'suggestions'])
    ->name('search.suggestions');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'register']);

Route::get('/mot-de-passe-oublie', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');

Route::post('/mot-de-passe-oublie', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('/reinitialiser-mot-de-passe/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reinitialiser-mot-de-passe', [ResetPasswordController::class, 'reset'])
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

Route::get('/panier', [CartController::class, 'index'])
    ->name('cart.index');

Route::post('/panier/ajouter/{product}', [CartController::class, 'add'])
    ->name('cart.add');

Route::patch('/panier/{variantId}', [CartController::class, 'update'])
    ->name('cart.update');

Route::delete('/panier/{variantId}', [CartController::class, 'remove'])
    ->name('cart.remove');

/*
|--------------------------------------------------------------------------
| Checkout & compte (auth requis)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/commande', [CheckoutController::class, 'index'])
        ->name('checkout.index');

    Route::post('/commande', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    Route::get('/commande/succes/{order}', [CheckoutController::class, 'success'])
        ->name('checkout.success');

    Route::get('/mes-demandes-whitelist', [WhitelistController::class, 'index'])
        ->name('whitelist.index');
});

/*
|--------------------------------------------------------------------------
| Drops
|--------------------------------------------------------------------------
*/

Route::get('/drops', [DropController::class, 'index'])
    ->name('drops.index');

Route::get('/drops/{drop:slug}', [DropController::class, 'show'])
    ->name('drops.show');

Route::middleware('auth')->group(function () {

    Route::post('/drops/{drop:slug}/request-whitelist',
        [DropRequestController::class, 'store']
    )->name('drops.request-whitelist');

});

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        /*
        | Products
        */

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/produits', [AdminProductController::class, 'index'])
            ->name('products.index');

        Route::get('/produits/creer', [AdminProductController::class, 'create'])
            ->name('products.create');

        Route::post('/produits', [AdminProductController::class, 'store'])
            ->name('products.store');

        Route::get('/produits/{product}/edit', [AdminProductController::class, 'edit'])
            ->name('products.edit');

        Route::put('/produits/{product}', [AdminProductController::class, 'update'])
            ->name('products.update');

        Route::delete('/produits/{product}', [AdminProductController::class, 'destroy'])
            ->name('products.destroy');

        /*
        | Categories
        */

        Route::get('/categories', [AdminCategoryController::class, 'index'])
            ->name('categories.index');

        Route::post('/categories', [AdminCategoryController::class, 'store'])
            ->name('categories.store');

        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])
            ->name('categories.update');

        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])
            ->name('categories.destroy');

        /*
        | Drops
        */

        Route::get('/drops', [AdminDropController::class, 'index'])
            ->name('drops.index');

        Route::get('/drops/creer', [AdminDropController::class, 'create'])
            ->name('drops.create');

        Route::post('/drops', [AdminDropController::class, 'store'])
            ->name('drops.store');

        Route::get('/drops/{drop}/edit', [AdminDropController::class, 'edit'])
            ->name('drops.edit');

        Route::put('/drops/{drop}', [AdminDropController::class, 'update'])
            ->name('drops.update');

        Route::delete('/drops/{drop}', [AdminDropController::class, 'destroy'])
            ->name('drops.destroy');

        /*
        | Whitelist
        */

        Route::post(
            '/drops/{drop}/whitelist/{whitelistId}/approve',
            [AdminDropController::class, 'approveWhitelist']
        )->name('drops.whitelist.approve');

        Route::post(
            '/drops/{drop}/whitelist/{whitelistId}/reject',
            [AdminDropController::class, 'rejectWhitelist']
        )->name('drops.whitelist.reject');

        Route::get('/whitelist', [AdminWhitelistController::class, 'index'])
            ->name('whitelist.index');

        /*
        | Orders
        */

        Route::get('/commandes', [AdminOrderController::class, 'index'])
            ->name('orders.index');

        Route::get('/commandes/{order}', [AdminOrderController::class, 'show'])
            ->name('orders.show');

        Route::patch('/commandes/{order}/statut', [AdminOrderController::class, 'updateStatus'])
            ->name('orders.updateStatus');
    });
