<?php
namespace Scm\PluginBid;


use Illuminate\Support\Facades\Route;
use Scm\PluginBid\Models\ScmPluginBidFile;
use Scm\PluginBid\Models\ScmPluginBidSingle;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;


class ScmPluginBidProvider extends PackageServiceProvider
{

    const PLUGIN_NAME = 'scm-plugin-bid';
    const HUMAN_NAME = 'Bids';
    const VIEW_BLADE_ROOT = 'ScmPluginBid';


    public function configurePackage(Package $package): void
    {

        $package
            ->name(static::PLUGIN_NAME)
            ->hasConfigFile()

            ->hasViews(static::VIEW_BLADE_ROOT)
            ->hasRoute('web')
            ->hasAssets()
            ->discoversMigrations()

            ->hasInstallCommand(function(InstallCommand $command) {

                $command
                    ->publishAssets()
                ;
            })
            ->runsMigrations()
        ;

    }



    protected PluginLogic $plugin_logic;


    public function packageBooted()
    {

        Route::model('single_bid', ScmPluginBidSingle::class);
        Route::model('bid_file', ScmPluginBidFile::class);
        $this->plugin_logic = new PluginLogic();
        $this->plugin_logic->initialize();

        return $this;
    }

}
