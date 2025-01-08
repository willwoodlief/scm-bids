<?php
namespace Scm\PluginBid\Helpers;

use App\Helpers\Roles\GenericCategory;
use App\Helpers\Roles\GenericPermission;
use App\Helpers\Utilities;
use App\Models\UserRole;
use App\Models\UserRoleApply;
use App\Models\UserRoleCategory;
use App\Models\UserRolePermission;
use App\Models\UserRoleRule;
use Scm\PluginBid\ScmPluginBidProvider;

class PluginPermissions
{

    const BID_EDITOR_CATEGORY_NAME = 'bid-editor';
    const BID_VIEWER_CATEGORY_NAME = 'bid-viewer';
    const BID_HELPER_CATEGORY_NAME = 'bid-helper';

    const BID_CATEGORIES = [
        self::BID_EDITOR_CATEGORY_NAME => 'Create, edit, process bids',
        self::BID_VIEWER_CATEGORY_NAME => 'View and maybe edit bids',
        self::BID_HELPER_CATEGORY_NAME => 'View or edit bids on a case by case basis',
    ];
    const PERMISSION_BID_UNIT_NAME = 'bid';
    const PERMISSION_BID_UNIT_HUMAN_NAME = 'Bid';

    const PERMISSION_BID_CREATE = 'bid_create';
    const PERMISSION_BID_ASSIGN = 'bid_assign';
    const PERMISSION_BID_EDIT = 'bid_edit';
    const PERMISSION_BID_FAIL = 'bid_fail';
    const PERMISSION_BID_PROMOTE = 'bid_promote';
    const PERMISSION_BID_VIEW = 'bid_view';
    const PERMISSION_BID_PER_VIEW = 'bid_per_view';
    const PERMISSION_BID_PER_EDIT = 'bid_per_edit';



    const BID_PERMISSIONS = [

        self::PERMISSION_BID_CREATE => ['human_name'=>'Create bids','is_permission_admin'=>true],
        self::PERMISSION_BID_ASSIGN => ['human_name'=>'Allow user to view and/or edit a bid','is_permission_admin'=>true],
        self::PERMISSION_BID_EDIT => ['human_name'=>'Edit bids','is_permission_admin'=>true],
        self::PERMISSION_BID_FAIL => ['human_name'=>'Fail bids and delete them','is_permission_admin'=>true],
        self::PERMISSION_BID_PROMOTE => ['human_name'=>'Promote bids to projects','is_permission_admin'=>true],
        self::PERMISSION_BID_VIEW => ['human_name'=>'View bids'],
        self::PERMISSION_BID_PER_VIEW => ['human_name'=>'View a bid','per_unit_of'=>self::PERMISSION_BID_UNIT_NAME],
        self::PERMISSION_BID_PER_EDIT => ['human_name'=>'Edit a bid','per_unit_of'=>self::PERMISSION_BID_UNIT_NAME],
    ];


    const BID_PERMISSION_RULES = [

        self::BID_EDITOR_CATEGORY_NAME =>
            [
                self::PERMISSION_BID_CREATE ,
                self::PERMISSION_BID_ASSIGN ,
                self::PERMISSION_BID_EDIT ,
                self::PERMISSION_BID_FAIL ,
                self::PERMISSION_BID_PROMOTE ,
                self::PERMISSION_BID_VIEW

            ],
        self::BID_VIEWER_CATEGORY_NAME =>
            [
                self::PERMISSION_BID_VIEW
            ],

        self::BID_HELPER_CATEGORY_NAME =>
            [
                self::PERMISSION_BID_PER_VIEW ,
                self::PERMISSION_BID_PER_EDIT
            ],
    ];

    public static function doInit() {
        UserRoleCategory::seedHardCodedCategories(seeder_array: static::BID_CATEGORIES,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
        UserRolePermission::initOrRefreshPermissions(permission_array: static::BID_PERMISSIONS,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
        UserRoleRule::setupRules(setup: static::BID_PERMISSION_RULES,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
        UserRoleApply::updateAppliedPermissions(for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
    }

    public static function doRemove() {
        //delete the categories and permissions and the rules, roles, and applied permissions will go away for them
        foreach (UserRolePermission::where('permission_human_name',ScmPluginBidProvider::PLUGIN_NAME) as $doomed_permission) {
            $doomed_permission->delete();
        }

        foreach (UserRoleCategory::where('permission_human_name',ScmPluginBidProvider::PLUGIN_NAME) as $doomed_permission) {
            $doomed_permission->delete();
        }
    }

    /** @return GenericCategory[] */
    public static function  getAllCategories() {
        return UserRoleCategory::getHardCodedCategories(seeder_array: static::BID_CATEGORIES,for_plugin:ScmPluginBidProvider::PLUGIN_NAME);
    }

    /** @return GenericPermission[] */
    public static function  getAllPermissions(?string $category_name = null) {
        return UserRoleRule::getPermissionsForCategory(category_name: $category_name,for_plugin:ScmPluginBidProvider::PLUGIN_NAME);
    }

    /** @return GenericCategory[] */
    public static function  getUserCategories(\App\Models\User $user) {
        return UserRole::getUserCategories(user: $user,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
    }


    /**
     * @throws \Exception
     */
    public static function  saveRoles(array $category_names, \App\Models\User $user) :void {
        $user->save_roles(category_names: $category_names,by_user: Utilities::get_logged_user(),for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
    }

    public static function updateApplied(?\App\Models\User $user = null, ?int $per_unit_id = null) {
        return UserRoleApply::updateAppliedPermissions(user:$user,per_unit_of: static::PERMISSION_BID_UNIT_NAME,
            per_unit_id: $per_unit_id,for_plugin:ScmPluginBidProvider::PLUGIN_NAME );
    }
}
