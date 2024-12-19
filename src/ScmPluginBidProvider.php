<?php
namespace Scm\PluginBid;


use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;


class ScmPluginBidProvider extends PackageServiceProvider
{


    const VIEW_BLADE_ROOT = 'ScmPluginBid';


    public function configurePackage(Package $package): void
    {

        $package
            ->name('scm-plugin-bid')
            ->hasConfigFile()

            ->hasViews(static::VIEW_BLADE_ROOT)
            ->hasRoute('web')
            ->hasAssets()
            ->hasMigrations(
'2024_12_18_181437_create_scm_plugin_bid_singles',
                '2024_12_18_182343_create_scm_plugin_bid_stats',
                '2024_12_18_183326_create_scm_plugin_bid_files'
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
