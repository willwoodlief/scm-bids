<?php
/**
 * This route file was registered in @see \Scm\PluginBid\ScmPluginBidProvider
 *
 * It will call the controller at the @see \Scm\PluginBid\Controllers\ScmPluginBidAdminController
 */
use Illuminate\Support\Facades\Route;
use Scm\PluginBid\Controllers\ScmPluginBidAdminController;
use Scm\PluginBid\Middleware\CanCreateBid;
use Scm\PluginBid\Middleware\CanEditBid;
use Scm\PluginBid\Middleware\CanResolveBid;
use Scm\PluginBid\Middleware\CanViewBid;
use Scm\PluginBid\Middleware\CanViewStats;
use Scm\PluginBid\Middleware\HasBidPermissions;


Route::prefix('scm-bid')->group(function () {


    Route::middleware(['web','auth',HasBidPermissions::class])->group(function () {


        Route::get('/', [ScmPluginBidAdminController::class, 'index'])->name('scm-bid.index');
        Route::get('list', [ScmPluginBidAdminController::class, 'bid_list'])->name('scm-bid.list');
        Route::get('get_new_contactor_form', [ScmPluginBidAdminController::class, 'get_new_contactor_form'])->name('scm-bid.get_new_contactor_form');
        Route::post('create_contractor', [ScmPluginBidAdminController::class, 'create_contractor'])->name('scm-bid.create_contractor');

        Route::middleware([CanCreateBid::class])->group(function () {
            Route::get('new', [ScmPluginBidAdminController::class, 'new_bid'])->name('scm-bid.new');
            Route::post('create', [ScmPluginBidAdminController::class, 'create_bid'])->name('scm-bid.create');
        }); //end create bid

        Route::middleware([CanViewStats::class])->group(function () {
            Route::get('show_processed', [ScmPluginBidAdminController::class, 'show_processed'])->name('scm-bid.show_processed');
        }); //end stats



        Route::middleware([CanViewBid::class])->prefix('bid/{single_bid}')->group(function () {

            Route::get('show', [ScmPluginBidAdminController::class, 'show_bid'])->name('scm-bid.bid.show');


            Route::middleware([CanEditBid::class])->group(function () {
                Route::get('edit', [ScmPluginBidAdminController::class, 'edit_bid'])->name('scm-bid.bid.edit');
                Route::put('update', [ScmPluginBidAdminController::class, 'update_bid'])->name('scm-bid.bid.update');
            }); //end updating


            Route::middleware([CanResolveBid::class])->group(function () {
                Route::post('success', [ScmPluginBidAdminController::class, 'bid_successful'])->name('scm-bid.bid.success');
                Route::delete('failed', [ScmPluginBidAdminController::class, 'bid_failed'])->name('scm-bid.bid.fail');
            }); //end resolving


            Route::group(['prefix' => 'files'],function () {
                Route::get('download/{bid_file}', [ScmPluginBidAdminController::class, 'download_file'])->name('scm-bid.files.download');

                Route::middleware([CanEditBid::class])->group(function () {
                    Route::post('add', [ScmPluginBidAdminController::class, 'add_files'])->name('scm-bid.files.add');
                    Route::delete('remove/{bid_file}', [ScmPluginBidAdminController::class, 'remove_file'])->name('scm-bid.files.remove');
                });
            }); //end files

        }); //end view bid permission actions for single bid

    }); //end any bid permissions
}); //end bid routes


