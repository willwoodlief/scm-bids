<?php
namespace Scm\PluginBid\Helpers;

use App\Helpers\Roles\GenericCategory;
use App\Helpers\Roles\GenericPermission;
use App\Helpers\Utilities;
use App\Models\User;
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
    const CUSTOMIZABLE_CATEGORY_NAME = 'bid-customize';

    const BID_CATEGORIES = [
        self::BID_EDITOR_CATEGORY_NAME => 'Manage bids',
        self::BID_VIEWER_CATEGORY_NAME => 'View bids',
        self::BID_HELPER_CATEGORY_NAME => 'View or edit some bids',
        self::CUSTOMIZABLE_CATEGORY_NAME => 'Customizable Bids',
    ];
    const PERMISSION_BID_UNIT_NAME = 'bid';
    const PERMISSION_BID_UNIT_HUMAN_NAME = 'Bid';

    const PERMISSION_BID_CREATE = 'bid_create';
    const PERMISSION_BID_EDIT = 'bid_edit';
    const PERMISSION_BID_RESOLVE = 'bid_resolve';
    const PERMISSION_BID_VIEW_STATS = 'bid_view_stats';
    const PERMISSION_VIEW_ALL_BIDS = 'bids_view_all';
    const PERMISSION_BID_PER_VIEW = 'bid_per_view';
    const PERMISSION_BID_PER_EDIT = 'bid_per_edit';



    const BID_PERMISSIONS = [

        self::PERMISSION_BID_CREATE => ['human_name'=>'Create bids','permission_level'=>6],
        self::PERMISSION_BID_EDIT => ['human_name'=>'Edit bids','permission_level'=>5],
        self::PERMISSION_BID_RESOLVE => ['human_name'=>'Mark bids as successful or not :  delete them','permission_level'=>5],
        self::PERMISSION_VIEW_ALL_BIDS => ['human_name'=>'View bids','permission_level'=>4],
        self::PERMISSION_BID_VIEW_STATS => ['human_name'=>'View stats and history','permission_level'=>4],
        self::PERMISSION_BID_PER_VIEW => ['human_name'=>'View a bid','per_unit_of'=>self::PERMISSION_BID_UNIT_NAME,'permission_level'=>3],
        self::PERMISSION_BID_PER_EDIT => ['human_name'=>'Edit a bid','per_unit_of'=>self::PERMISSION_BID_UNIT_NAME,'permission_level'=>4],
    ];


    const BID_PERMISSION_RULES = [

        self::BID_EDITOR_CATEGORY_NAME =>
            [
                self::PERMISSION_BID_CREATE ,
                self::PERMISSION_BID_EDIT ,
                self::PERMISSION_BID_RESOLVE ,
                self::PERMISSION_VIEW_ALL_BIDS,
                self::PERMISSION_BID_VIEW_STATS

            ],
        self::BID_VIEWER_CATEGORY_NAME =>
            [
                self::PERMISSION_VIEW_ALL_BIDS,
                self::PERMISSION_BID_VIEW_STATS
            ],

        self::BID_HELPER_CATEGORY_NAME =>
            [
                self::PERMISSION_BID_PER_VIEW ,
                self::PERMISSION_BID_PER_EDIT
            ],
        self::CUSTOMIZABLE_CATEGORY_NAME => []
    ];

    public static function doInit() {
        UserRoleCategory::seedHardCodedCategories(seeder_array: static::BID_CATEGORIES,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
        UserRolePermission::initOrRefreshPermissions(permission_array: static::BID_PERMISSIONS,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
        UserRoleRule::setupRules(setup: static::BID_PERMISSION_RULES,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
        UserRoleApply::updateAppliedPermissions(for_plugin: ScmPluginBidProvider::PLUGIN_NAME);

        //assign role to all admins
        UserRole::getUsersOfCategory(category_name: UserRoleCategory::USER_CATEGORY_ADMIN)->each(/**
         * @throws \Exception
         */ function (User $user) {
            $user->addRole(category_name: static::BID_EDITOR_CATEGORY_NAME, by_user: null);
        });
    }

    public static function doRemove() {
        //delete the categories and permissions and the rules, roles, and applied permissions will go away for them
        foreach (UserRolePermission::where('managing_plugin_name',ScmPluginBidProvider::PLUGIN_NAME)->get() as $doomed_permission) {
            $doomed_permission->delete();
        }

        foreach (UserRoleCategory::where('managing_plugin_name',ScmPluginBidProvider::PLUGIN_NAME)->get() as $doomed_permission) {
            $doomed_permission->delete();
        }
    }

    /** @return GenericCategory[] */
    public static function  getAllCategories() {
        return UserRoleCategory::getHardCodedCategories(seeder_array: static::BID_CATEGORIES,for_plugin:ScmPluginBidProvider::PLUGIN_NAME);
    }

    /** @return GenericPermission[] */
    public static function  getPermissions(?string $category_name = null,?string $only_permission_name = null) {
        if ($category_name && !in_array($category_name,array_keys(static::BID_CATEGORIES) )) {return [];}
        if ($category_name) {
            return UserRoleRule::getPermissionsForCategory(category_name: $category_name,for_plugin:ScmPluginBidProvider::PLUGIN_NAME);
        }
        return UserRolePermission::getPermissions(only_permission_name: $only_permission_name,for_plugin:ScmPluginBidProvider::PLUGIN_NAME);
    }

    /** @return GenericCategory[] */
    public static function  getUserCategories(\App\Models\User $user) {
        return UserRole::getUserCategories(user: $user,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
    }

    public static function  getAssignableUsers(?GenericPermission $permission = null) {
        return UserRoleApply::getAssignableUsers(permission: $permission,for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
    }


    /**
     * @throws \Exception
     */
    public static function  saveRoles(array $category_names, \App\Models\User $user) :void {
        $user->save_roles(category_names: $category_names,by_user: Utilities::get_logged_user(b_throw_on_unlogged: false),for_plugin: ScmPluginBidProvider::PLUGIN_NAME);
    }

    public static function  addPermissionToCategory(string $category_name,string $permission_name) :void {
        UserRoleRule::addPermissionToCategory(category_name: $category_name,permission_name: $permission_name,for_plugin: ScmPluginBidProvider::PLUGIN_NAME,b_call_action: false);
    }

    public static function  removePermissionFromCategory(string $category_name,string $permission_name) :void {
        UserRoleRule::removePermissionFromCategory(category_name: $category_name,permission_name: $permission_name,for_plugin: ScmPluginBidProvider::PLUGIN_NAME,b_call_action: false);
    }

    public static function  removeAppliedResource(?\App\Models\User $user = null,?string $permission_name = null,?string $per_unit_of = null, ?int $per_unit_id = null)
    :void
    {
        UserRoleApply::removeAppliedResource(user: $user,permission_name: $permission_name,per_unit_of: $per_unit_of,per_unit_id: $per_unit_id,b_call_action: false);
    }

    public static function  updateAppliedPermissions(
        ?\App\Models\User $user = null,?string $category_name = null,?string $permission_name = null,?string $per_unit_of = null, ?int $per_unit_id = null)
    :void
    {
        UserRoleApply::updateAppliedPermissions(
            user: $user,category_name: $category_name,permission_name: $permission_name,
            per_unit_of: $per_unit_of,per_unit_id: $per_unit_id,for_plugin: ScmPluginBidProvider::PLUGIN_NAME,b_call_action: false);
    }



    /**
     * empty array means can see all
     * if cannot set any will have -1 by itself in the array
     * otherwise will have array of employee ids can see
     * @return int[]
     */
    public static function getBidIdsUserCanView(User $user) : array {
        if ($user->has_permission(permission_names: static::PERMISSION_VIEW_ALL_BIDS) ) {
            return []; //can see all
        }
        $ret = [];
        //get the per view applied rules and build list
        $applied_list = \App\Models\UserRole::getAssignmentsToResources(user: $user,permission_names: [static::PERMISSION_BID_PER_VIEW]);
        foreach ($applied_list as $perm) {
            if ($perm->getPerUnitOf() !== static::PERMISSION_BID_UNIT_NAME) {continue;}
            $ret[] = $perm->getUnitId();
        }
        if (count($ret)) {return $ret;}
        //if empty list
        return [UserRolePermission::NO_ID_AVAILABLE]; //will not match any employee
    }


}
