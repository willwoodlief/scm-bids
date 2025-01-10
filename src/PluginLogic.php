<?php
namespace Scm\PluginBid;


use App\Plugins\Plugin;

use Scm\PluginBid\Helpers\PluginPermissions;
use Scm\PluginBid\Models\ScmPluginBidSingle;
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


        /*
           Permissions below
          ❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚❚█══█❚
       */


        Eventy::addFilter(Plugin::FILTER_ALL_ROLE_CATEGORIES, function( array $role_categories )
        : array
        {
            foreach (PluginPermissions::getAllCategories() as $cat) {$role_categories[] = $cat;}
            return $role_categories;
        }, 20, 1);


        Eventy::addFilter(Plugin::FILTER_ALL_ROLE_CATEGORIES_FOR_USER, function( array $role_categories,\App\Models\User $user )

        : array
        {
             foreach (PluginPermissions::getUserCategories($user) as $cat) {$role_categories[] = $cat;}
             return $role_categories;
        }, 20, 2);


         Eventy::addFilter(Plugin::FILTER_CATEGORY_PERMISSIONS, function(array $role_permissions, string $category_name )
         : array
         {
             foreach (PluginPermissions::getPermissions($category_name) as $perm) {$role_permissions[] = $perm;}
             return $role_permissions;
         }, 20, 2);


        Eventy::addFilter(Plugin::FILTER_PERMISSIONS, function(array $role_permissions,?string $only_permission_name )
        : array
        {
            foreach (PluginPermissions::getPermissions(only_permission_name:$only_permission_name ) as $perm) {$role_permissions[] = $perm;}
            return $role_permissions;
        }, 20, 2);




         Eventy::addAction(Plugin::ACTION_UPDATE_USER_ROLES, function( \App\Models\User $user ,array $category_names ): void {
              //update any roles (the permissions will be applied when the ACTION_UPDATE_APPLIED_PERMISSIONS is called )
             PluginPermissions::saveRoles($category_names,$user);
         }, 20, 2);



         Eventy::addFilter(Plugin::FILTER_ROLE_HUMAN_UNIT_TYPE,
            function( ?string $human_name, string $machine_unit_type )
            : string
            {
                if ($machine_unit_type === PluginPermissions::PERMISSION_BID_UNIT_NAME) {
                    return PluginPermissions::PERMISSION_BID_UNIT_HUMAN_NAME;
                }
                return $human_name;
            }, 20, 2);


         Eventy::addFilter(Plugin::FILTER_ROLE_HUMAN_UNIT_ID,
            function( ?string $human_name, string $machine_unit_type,int $unit_id )
            : string
            {
                  if ($machine_unit_type === PluginPermissions::PERMISSION_BID_UNIT_NAME) {
                      /** @var ScmPluginBidSingle $bid */
                      $bid =   ScmPluginBidSingle::find($unit_id);
                      return $bid?->getName();
                  }
                  return $human_name;
               },
            20, 3);

         Eventy::addFilter(Plugin::FILTER_ROLE_PERMISSION_MODEL,
            function( ?\Illuminate\Database\Eloquent\Model $found, string $machine_unit_type,int $unit_id )
            : string
            {
                if ($machine_unit_type === PluginPermissions::PERMISSION_BID_UNIT_NAME) { return ScmPluginBidSingle::find($unit_id);}
                return $found;
            }, 20, 3);


         Eventy::addAction(Plugin::ACTION_REMOVE_PERMISSION_FROM_CATEGORY,
            function( string $category_name, string $permission_name )
            : void
            {
                PluginPermissions::removePermissionFromCategory(category_name: $category_name,permission_name: $permission_name);
            }, 20, 2);


         Eventy::addAction(Plugin::ACTION_ADD_PERMISSION_TO_CATEGORY,
            function( string $category_name, string $permission_name )
            : void
            {
                PluginPermissions::addPermissionToCategory(category_name: $category_name,permission_name: $permission_name);
            }, 20, 2);


         Eventy::addAction(Plugin::ACTION_REMOVE_APPLIED_RESOURCE,
            function(   ?\App\Models\User $user = null,?string $permission_name = null,
                        ?string $per_unit_of = null, ?int $per_unit_id = null )
            : void
            {
                PluginPermissions::removeAppliedResource(user: $user,permission_name: $permission_name,per_unit_of: $per_unit_of,per_unit_id: $per_unit_id);
            }, 20, 4);


         Eventy::addFilter(Plugin::FILTER_PLUGIN_HUMAN_PLUGIN_NAME,
            function( string $human_name, string $plugin_name )
            : string
            {
                if ($plugin_name === ScmPluginBidProvider::PLUGIN_NAME) {
                      return ScmPluginBidProvider::HUMAN_NAME;
                  }
                return $human_name;
            }, 20, 2);



    }


}
