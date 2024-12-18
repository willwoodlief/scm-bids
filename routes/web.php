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


        });
    });
});


