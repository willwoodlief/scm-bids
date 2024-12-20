<?php
namespace Scm\PluginBid;


use App\Plugins\Plugin;

use TorMorten\Eventy\Facades\Eventy;
use Scm\PluginBid\Facades\ScmPluginBid;


/**
 * This is where the plugin registers its listeners and hooks up the code to deal with notices sent to those listeners
 *
 * It has some simple logic to demonstrate how to listen to one page being run (the dashboard), and has an initialize() function that is run on plugin startup
 */
class PluginLogic extends Plugin {


    /**
     * Sets the dashboard bool to false
     */
    public function __construct()
    {

    }





    /**
     * Runs once at plugin startup, registers some listeners
     *
     *
     * @return void
     */
    public function initialize()
    {
        Eventy::addFilter(Plugin::FILTER_FRAME_ADMIN_LINKS, function( string $extra_admin_menu_stuff) {
            $item =view(ScmPluginBid::getBladeRoot().'::hooks/admin/entry-for-this',[])->render();
            return $extra_admin_menu_stuff."\n". $item;
        });



        Eventy::addFilter(Plugin::FILTER_FRAME_EXTRA_HEAD, function( string $stuff) {
            $link = ScmPluginBid::getPluginRef()->getResourceUrl('css/scm-plugin-bid.css');
            $link_tag = "<link href='$link' rel='stylesheet'>";
            return $stuff."\n$link_tag\n";

        }, 20, 1);


        Eventy::addFilter(Plugin::FILTER_FRAME_EXTRA_FOOT, function( string $stuff) {
            $script = ScmPluginBid::getPluginRef()->getResourceUrl('js/scm-plugin-bid.js');
            $script_tag = "<script src='$script'></script>";
            return $stuff."\n$script_tag\n";

        }, 20, 1);

        Eventy::addFilter(Plugin::FILTER_TOTAL_SIZE_FILES, function( int $total_file_size) {
            //todo add in the extra size used
            return $total_file_size;

        }, 20, 1);

        Eventy::addFilter(Plugin::FILTER_FRAME_END_TOP_MENU, function( string $extra_menu_stuff) {
            $item =view(ScmPluginBid::getBladeRoot().'::hooks/menu/top-menu-item-for-this',[])->render();
            return $extra_menu_stuff."\n".$item;

        }, 20, 1);


    }
}
