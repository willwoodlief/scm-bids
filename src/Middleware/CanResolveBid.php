<?php

namespace Scm\PluginBid\Middleware;

use App\Helpers\Utilities;
use Closure;
use Illuminate\Http\Request;
use Scm\PluginBid\Helpers\PluginPermissions;
use Scm\PluginBid\Models\ScmPluginBidSingle;
use Symfony\Component\HttpFoundation\Response;

class CanResolveBid
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

            if ($user->has_permission(permission_names: PluginPermissions::PERMISSION_BID_RESOLVE))
            {
                return $next($request);
            }

        }

        abort('403',__('No permission to resolve bid '. $bid?->getName()));
    }
}
