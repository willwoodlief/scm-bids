<?php
namespace Scm\PluginBid;


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
            ->hasMigrations(
'2024_12_18_181437_create_scm_plugin_bid_singles',
                '2024_12_18_182343_create_scm_plugin_bid_stats',
                '2024_12_18_183326_create_scm_plugin_bid_files',
                '2025_01_08_105408_add_permissions_for_bids'
            )

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

        $this->plugin_logic = new PluginLogic();
        $this->plugin_logic->initialize();

        return $this;
    }

}
