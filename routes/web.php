<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DropController;
use App\Http\Controllers\DropRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\DropController as AdminDropController;

Route::view('/', 'home')->name('home');

Route::get('/boutique', [ProductController::class, 'index'])->name('shop.index');

// Authentication routes
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::get('/produit/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::post('/panier/ajouter/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/panier/{variantId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{variantId}', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('auth')->group(function () {
    Route::get('/commande', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/commande', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/commande/succes/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
});

Route::get('/drops', [DropController::class, 'index'])->name('drops.index');
Route::get('/drops/{drop:slug}', [DropController::class, 'show'])->name('drops.show');
Route::middleware('auth')->post('/drops/{drop:slug}/request-whitelist', [DropRequestController::class, 'store'])->name('drops.request-whitelist');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/produits', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/produits/creer', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/produits', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/produits/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/produits/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/produits/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

Route::get('/drops', [AdminDropController::class, 'index'])->name('drops.index');
Route::get('/drops/creer', [AdminDropController::class, 'create'])->name('drops.create');
Route::post('/drops', [AdminDropController::class, 'store'])->name('drops.store');
Route::get('/drops/{drop}/edit', [AdminDropController::class, 'edit'])->name('drops.edit');
Route::put('/drops/{drop}', [AdminDropController::class, 'update'])->name('drops.update');
Route::delete('/drops/{drop}', [AdminDropController::class, 'destroy'])->name('drops.destroy');
Route::post('/drops/{drop}/whitelist/{whitelistId}/approve', [AdminDropController::class, 'approveWhitelist'])->name('drops.whitelist.approve');
Route::post('/drops/{drop}/whitelist/{whitelistId}/reject', [AdminDropController::class, 'rejectWhitelist'])->name('drops.whitelist.reject');
});