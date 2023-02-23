<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

  // Laravel 8 & 9
  Route::get('/getform', [PaymentController::class, 'show'])->name('form');
  // Laravel 8 & 9
  Route::post('/pay', [PaymentController::class, 'redirectToGateway'])->name('pay');
  // Laravel 8 & 9
  Route::get('/payment/callback', [PaymentController::class, 'handleGatewayCallback']);

  Route::get('payment/paystack/callback', [PaymentController::class, 'callback'])->middleware('cache.no');
// Laravel 8 & 9
    Route::post('/make-payment', [PaymentController::class, 'makePayment']);
