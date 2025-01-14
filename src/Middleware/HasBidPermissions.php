<?php

namespace Scm\PluginBid\Middleware;

use App\Helpers\Utilities;
use App\Models\Enums\TypeOfPermissionLogic;
use Closure;
use Illuminate\Http\Request;
use Scm\PluginBid\Helpers\PluginPermissions;
use Symfony\Component\HttpFoundation\Response;

class HasBidPermissions
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
        $user = Utilities::get_logged_user();
        if ($user) {

            $cat_array = [];
            foreach (array_keys(PluginPermissions::BID_CATEGORIES) as $cat_name ) {
                $cat_array[] = $cat_name;
            }
            if ($user->has_role(category_names: $cat_array,logic: TypeOfPermissionLogic::ONE_OF_THESE_PERMISSIONS)) {
                return $next($request);
            }

        }

        abort('403',__('No Bid Permissions'));
    }
}
