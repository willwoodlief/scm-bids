<?php
/**
 * This route file was registered in @see \Scm\PluginBid\ScmPluginBidProvider
 *
 * It will call the controller at the @see \Scm\PluginBid\Controllers\ScmPluginBidAdminController
 */
use Illuminate\Support\Facades\Route;


Route::prefix('scm-bid')->group(function () {


    Route::middleware(['web','auth','admin'])->group(function () {
        Route::prefix('admin')->group(function () {

            Route::get('/', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'index'])
                ->name('scm-bid.admin.index');

            Route::prefix('bids')->group(function () {

                Route::get('/new', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'new_bid'])
                    ->name('scm-bid.admin.bids.new');

                Route::get('/edit/{bid_id}', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'edit_bid'])
                    ->where('bid_id', '[0-9]+')
                    ->name('scm-bid.admin.bids.edit');

                Route::post('/create', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'create_bid'])
                    ->name('scm-bid.admin.bids.create');

                Route::put('/update/{bid_id}', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'update_bid'])
                    ->where('bid_id', '[0-9]+')
                    ->name('scm-bid.admin.bids.update');

            });
        });
    });
});


