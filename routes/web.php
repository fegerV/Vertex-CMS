<?php

use App\Seo\Http\Controllers\RobotsController;
use App\Seo\Http\Controllers\SitemapController;
use App\Content\Http\Controllers\FrontendPageController;
use App\System\Http\Controllers\PwaController;
use App\System\Http\Controllers\PwaManifestController;
use App\Taxonomy\Http\Controllers\FrontendTermArchiveController;
use App\Ecommerce\Http\Controllers\PublicProductController;
use App\Ecommerce\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendPageController::class, 'home'])->name('frontend.home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('frontend.sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('frontend.robots');
Route::get('/manifest.webmanifest', [PwaManifestController::class, 'index'])->name('frontend.manifest');
Route::get('/service-worker.js', [PwaController::class, 'serviceWorker'])->name('frontend.service-worker');
Route::get('/offline', [PwaController::class, 'offline'])->name('frontend.offline');
Route::get('/taxonomy/{taxonomy}/{term}', [FrontendTermArchiveController::class, 'show'])->name('frontend.term-archive');

// E-commerce Public Routes
Route::prefix('shop')->name('ecommerce.')->group(function () {
    // Product catalog
    Route::get('/', [PublicProductController::class, 'index'])->name('catalog');
    Route::get('/product/{slug}', [PublicProductController::class, 'show'])->name('product.show');
    
    // Cart
    Route::get('/cart', [PublicProductController::class, 'cart'])->name('cart');
    Route::post('/cart/add', [PublicProductController::class, 'addToCart'])->name('cart.add');
    Route::patch('/cart/{item}', [PublicProductController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{item}', [PublicProductController::class, 'removeFromCart'])->name('cart.remove');
    
    // Checkout flow
    Route::middleware(['auth.optional'])->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/checkout/success/{orderId}', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/payment/stripe/{order}', [CheckoutController::class, 'stripePayment'])->name('checkout.payment.stripe');
        Route::get('/checkout/payment/paypal/{order}', [CheckoutController::class, 'paypalPayment'])->name('checkout.payment.paypal');
    });
});

// Public Form Endpoints (vertex-forms module) - must be BEFORE catch-all route
Route::prefix('forms')->name('public.forms.')->group(function () {
    require base_path('modules/vertex-forms/routes/web.php');
});

// Catch-all page route (must be last)
Route::get('/{uri}', [FrontendPageController::class, 'show'])
    ->where('uri', '.*')
    ->name('frontend.page');

