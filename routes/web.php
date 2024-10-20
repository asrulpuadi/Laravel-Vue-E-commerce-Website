<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileCustomController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['guestOrVerified'])->group(function(){
    Route::get('/',[ProductController::class,'index'])->name('home');
    Route::get('/product/{product:slug}',[ProductController::class,'show'])->name('product.view');

    Route::prefix('/cart')->name('cart.')->group(function(){
        Route::get('/',[CartController::class,'index'])->name('index');
        Route::post('/add/{product:slug}',[CartController::class,'add'])->name('add');
        Route::post('/remove/{product:slug}',[CartController::class,'remove'])->name('remove');
        Route::post('/update-quantity/{product:slug}',[CartController::class,'updateQuantity'])->name('update-quantity');

    });
});



// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth','verified'])->group(function(){
    Route::get('/profile-information',[ProfileCustomController::class,'profile'])->name('profile-information.profile');
    Route::post('/profile-information',[ProfileCustomController::class,'update'])->name('profile-information.update');
    Route::post('/profile-information/password-update',[ProfileCustomController::class,'passwordUpdate'])->name('profile-information.password-update');

    Route::post('/checkout',[CheckoutController::class,'checkout'])->name('checkout');
    Route::post('/checkout/{order}',[CheckoutController::class,'checkoutOrder'])->name('checkout.order');
    Route::get('/checkout/success',[CheckoutController::class,'success'])->name('checkout.success');
    Route::get('/checkout/failure',[CheckoutController::class,'failure'])->name('checkout.failure');

    Route::get('/order',[OrderController::class,'index'])->name('order.index');
    Route::get('/order/view/{order}',[OrderController::class,'view'])->name('order.view');
});

Route::post('/webhook/stripe',[CheckoutController::class,'webhook']);

require __DIR__.'/auth.php';
