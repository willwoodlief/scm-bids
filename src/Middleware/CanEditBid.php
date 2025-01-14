<?php

namespace Scm\PluginBid\Middleware;

use App\Helpers\Utilities;
use App\Models\Enums\TypeOfPermissionLogic;
use Closure;
use Illuminate\Http\Request;
use Scm\PluginBid\Helpers\PluginPermissions;
use Scm\PluginBid\Models\ScmPluginBidSingle;
use Symfony\Component\HttpFoundation\Response;

class CanEditBid
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response|never-return
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         * @var ScmPluginBidSingle $bid
         */
        $bid = $request->route('single_bid');
        $user = Utilities::get_logged_user();
        if ($user) {

            if ($user->has_permission(
                permission_names: [PluginPermissions::PERMISSION_BID_EDIT,PluginPermissions::PERMISSION_BID_PER_EDIT],
                logic: TypeOfPermissionLogic::ONE_OF_THESE_PERMISSIONS,
                per_unit: PluginPermissions::PERMISSION_BID_UNIT_NAME,per_unit_id: $bid->id)
            )
            {
                return $next($request);
            }

        }

        abort('403',__('No permission to edit bid '. $bid?->getName()));
    }
}
