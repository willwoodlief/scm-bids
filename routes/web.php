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

                Route::get('/', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'bid_list'])
                    ->name('scm-bid.admin.bids.list');

                Route::get('/new', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'new_bid'])
                    ->name('scm-bid.admin.bids.new');



                Route::post('/create', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'create_bid'])
                    ->name('scm-bid.admin.bids.create');



                Route::group(['prefix' => 'bid/{bid_id}', 'where' => ['bid_id' => '[0-9]+']],function () {

                    Route::get('edit', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'edit_bid'])
                        ->name('scm-bid.admin.bids.edit');

                    Route::get('show', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'show_bid'])
                        ->name('scm-bid.admin.bids.show');

                    Route::put('update', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'update_bid'])
                        ->name('scm-bid.admin.bids.update');

                    Route::post('success', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'bid_successful'])
                        ->name('scm-bid.admin.bids.successful');

                    Route::delete('failed', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'bid_failed'])
                        ->name('scm-bid.admin.bids.failed');


                    Route::group(['prefix' => 'files'],function () {
                        Route::post('add', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'add_files'])
                            ->name('scm-bid.admin.bids.add_files');

                        Route::delete('remove/{file_id}', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'remove_file'])
                            ->where('file_id', '[0-9]+')
                            ->name('scm-bid.admin.bids.remove_file');

                        Route::get('download/{file_id}', [\Scm\PluginBid\Controllers\ScmPluginBidAdminController::class, 'download_file'])
                            ->where('file_id', '[0-9]+')
                            ->name('scm-bid.admin.bids.download_file');

                    });

                });








            });
        });
    });
});


