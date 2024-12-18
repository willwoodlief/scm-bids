<?php
namespace Scm\PluginBid;


use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * This plugin uses the https://github.com/spatie/laravel-package-tools library to make the boilerplate laravel for a new service. It is optional, but makes it simpler. But regardless, the plugin needs to extend the Illuminate\Support\ServiceProvider class
 *
 * This class is found by the framework in the composer.json extra.providers field
 *
 * In this example class, I only need to override two methods configurePackage(), and packageBooted().
 *
 * In the configurePackage() I use the laravel-package-tools to register new blade views, new routes,commands, facades, assets, migrations.
 * The routes will load in the controllers used for the routes
 *
 * In a laravel package that uses its own views, these views are always prefixed. See the constant I made called VIEW_BLADE_ROOT and how its used in the other files
 *
 * Note that we can organize the resources however we need in resources/dist and the blades however we see fit at resources/views
 */
class ScmPluginBidProvider extends PackageServiceProvider
{

    /**
     * This holds the prefix for all the blades defined in the service package here. It can have any unique value not shared by other plugins
     */
    const VIEW_BLADE_ROOT = 'ScmPluginBid';

    /**
     * This is inherited from the base class, and allows me to register the name,views, route, assets, migrations, commands
     *
     * This function is called each time the laravel code runs
     *
     * @param Package $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */


        $package
            ->name('scm-plugin-bid')
            ->hasConfigFile()

            ->hasViews(static::VIEW_BLADE_ROOT)
            ->hasRoute('web')
            ->hasAssets()
            ->hasMigrations(

            )
            /*

             */
            ->hasInstallCommand(function(InstallCommand $command) {

                $command
                    ->publishAssets()
                ;
            })
            ->runsMigrations()
        ;

    }


    /**
     * I encapsulate all the plugin logic in this class, which inherits from the plugin class
     *
     * @var PluginLogic
     */
    protected PluginLogic $plugin_logic;

    /**
     * This method overrides the base class empty method, that is called when the package is fully ready for use. Its called each time the laravel code runs
     *
     * Here we just create the new PluginLogic
     *
     * @return $this
     */
    public function packageBooted()
    {
        $this->plugin_logic = new PluginLogic();
        $this->plugin_logic->initialize();

        return $this;
    }

}
