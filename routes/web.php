<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Session\{LoginController, LogoutController, ProfileController, ManagerController, PartnerController, AdminController, MasterController, StMasterController, ClientController};
use App\Http\Controllers\Market\{MarketController, PositionController, SubmitOrder};

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


Route::get('/', function () {
    return redirect()->route('action.search');
})->name('index');

Route::name('page.')->group(function() {
    Route::get('login', function() {
        return view('session.login');
    })->middleware('guest')->name('login');
    Route::get('signup', function() {
        return view('session.signup');
    })->middleware('guest')->name('signup');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('search/{id}', [PositionController::class, 'index'])->name('position');
    Route::get('storage', [ManagerController::class, 'storagePage'])->name('storage');
    Route::get('supply', [PartnerController::class, 'index'])->name('supply');
    Route::get('regpos', [AdminController::class, 'regPos'])->name('regpos');
    Route::get('supply/initinvoice', [PartnerController::class, 'newInvoicePage'])->name('init_supply_invoice');
    // Route::get('invoices', [MasterController::class, 'getInvoices'])->name('invoices');
    Route::get('client/claims', [ClientController::class, 'servicePage'])->name('claims');
    Route::get('client/new_claim', [ClientController::class, 'newInvoicePage'])->name('new_user_ticket');
    Route::get('client/claims/{claim}', [ClientController::class, 'claimDetails'])->name('claim_details');
});

Route::name('action.')->group(function() {
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::get('logout', [LogoutController::class, 'index'])->name('logout');
    Route::get('search', [MarketController::class, 'filter'])->name('search');
    Route::post('search/{id}', [PositionController::class, 'toBasket'])->name('addtobasket');
    Route::get('profile/rmitem/{sku}', [PositionController::class, 'rmFromBasket'])->name('rmfrombasket');
    Route::get('payment/order', [ProfileController::class, 'submitOrder'])->name('submit_order');
    Route::get('payment/service/{claim}', [ClientController::class, 'payClaim'])->name('pay_claim');
    Route::post('supply/initinvoice', [PartnerController::class, 'initInvoice'])->name('init_supply_invoice');
    Route::get('storage/{suid}', [ManagerController::class, 'submitSGInvoice'])->name('submit_sg_invoice');
    Route::post('client/new_claim', [ClientController::class, 'initClaim'])->name('init_claim');
    Route::get('master/claims/definemaster/{claimid}', [StMasterController::class, 'defineMasterToClaim'])->name('define_master');
    Route::get('master/claim/supply_claim/{claimid}/{option}', [MasterController::class, 'supplyClaim'])->name('supply_claim');
    Route::get('master/claim/expunge_from_claim/{claimid}/{option}/{data}', [MasterController::class, 'expungeFromClaim'])->name('expunge_from_claim');
    Route::get('master/claim/set_status/{claimid}', [StMasterController::class, 'setClaimStatus'])->name('set_claim_status');
});