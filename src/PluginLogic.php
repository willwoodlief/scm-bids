<?php
namespace Scm\PluginBid;


use App\Helpers\Utilities;
use App\Plugins\Plugin;

use App\Plugins\PluginRef;
use Scm\PluginBid\Helpers\PluginPermissions;
use Scm\PluginBid\Models\ScmPluginBidFile;
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


        Eventy::addFilter(Plugin::FILTER_FRAME_END_TOP_MENU, function( string $extra_menu_stuff) {
            if (!PluginPermissions::canSeeBidPlugin(Utilities::get_logged_user())) {
                return $extra_menu_stuff;
            }
            $item =view(ScmPluginBid::getBladeRoot().'::hooks/menu/top-menu-item-for-this',[])->render();
            return $extra_menu_stuff."\n".$item;

        }, 20, 1);

        Eventy::addFilter(Plugin::FILTER_TOTAL_SIZE_FILES, function( int $total_file_size ): int {
            $my_extra_size = ScmPluginBidFile::sum('bid_file_size_bytes');
            if (!$my_extra_size) {$my_extra_size = 0;}
            return $total_file_size + $my_extra_size;
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



         Eventy::addFilter(Plugin::FILTER_PERMISSION_HUMAN_UNIT_TYPE,
            function( ?string $human_name, string $machine_unit_type )
            : string
            {
                if ($machine_unit_type === PluginPermissions::PERMISSION_BID_UNIT_NAME) {
                    return PluginPermissions::PERMISSION_BID_UNIT_HUMAN_NAME;
                }
                return $human_name;
            }, 20, 2);


         Eventy::addFilter(Plugin::FILTER_PERMISSION_HUMAN_UNIT_ID,
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

         Eventy::addFilter(Plugin::FILTER_PERMISSION_MODEL,
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


        Eventy::addFilter(Plugin::FILTER_ASSIGNABLE_USERS_FOR_RESOURCE_PERMISSION,
            function( array $permission_users,?\App\Helpers\Roles\GenericPermission $given_permission = null)
            : array
            {
                $our_assignable_users =  PluginPermissions::getAssignableUsers($given_permission);
                return array_merge($permission_users,$our_assignable_users);
            }, 20, 2);


        Eventy::addFilter(Plugin::FILTER_PERMISSION_TYPE_LIST,
            function( array  $found_type_list, string $machine_unit_type )
            : array
            {
                if ($machine_unit_type === PluginPermissions::PERMISSION_BID_UNIT_NAME) {
                    /** @var ScmPluginBidSingle[] $plugin_model_list */
                   $plugin_model_list = ScmPluginBidSingle::orderBy('bid_name')->get();
                   foreach ($plugin_model_list as $bid) {
                       $found_type_list[$bid->id] = $bid->getName();
                   }
                   return $found_type_list;
                }
                return $found_type_list;
            }, 20, 2);


        Eventy::addFilter(Plugin::FILTER_PERMISSION_UNIT_LINK,
            function( ?string $link, string $machine_unit_type,int $unit_id )
            : string
            {
                if ($machine_unit_type === PluginPermissions::PERMISSION_BID_UNIT_NAME) {
                   return route('scm-bid.bid.show',['single_bid'=>$unit_id]);
                }
                return $link;
            }, 20, 3);


        Eventy::addAction(Plugin::ACTION_UPDATE_APPLIED_PERMISSIONS,
            function( ?\App\Models\User $user = null,?string $category_name = null,?string $permission_name = null,
                   ?string $per_unit_of = null, ?int $per_unit_id = null )
            : void
            {
                PluginPermissions::updateAppliedPermissions(
                    user: $user,category_name: $category_name,permission_name: $permission_name,
                    per_unit_of: $per_unit_of,per_unit_id: $per_unit_id);
            }, 20, 5);


        Eventy::addFilter(Plugin::FILTER_GET_EXPORTABLE_FILES, function( array $all_exports ): array {
            $all_exports['scm-plugin-bid-files'] = [ 'public' => []];
            ScmPluginBidFile::orderBy('id')->chunk(100, function($records) use(&$all_exports) {
                /** @var ScmPluginBidFile $bid_file */
                foreach($records as $bid_file)
                {
                    $relative_file = $bid_file->getRelativePath();
                    if ($relative_file) {
                        $all_exports['scm-plugin-bid-files']['public'][] = $relative_file;
                    }
                }
            });
            return $all_exports;
        }, 20, 1);


        Eventy::addFilter(Plugin::FILTER_GET_STATIC_RESOURCE_EXPORTS,
            function( array $all_exports )
            : array
            {
                $things = [
                    'css/scm-plugin-bid.css',
                    'js/scm-plugin-bid.js'
                ];

                $all_exports['scm-plugin-bids'] = [ ];
                foreach ($things as $relative_path) {
                    $relative_url = PluginRef::PLUGINS_FOLDER. DIRECTORY_SEPARATOR . ScmPluginBid::getPluginRef()->getResourceRelativePath($relative_path);
                    $abs_path = dirname(__DIR__,1) . DIRECTORY_SEPARATOR . 'resources/dist/'.$relative_path;
                    $all_exports['scm-plugin-bids'][] = ['relative_url'=>$relative_url,'absolute_path'=>$abs_path];
                }

                return $all_exports;
            }, 15, 1);

    }


}
