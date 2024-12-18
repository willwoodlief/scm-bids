<?php
namespace Scm\PluginBid\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Scm\PluginBid\Facades\ScmPluginBid;

use Illuminate\Http\Request;


class ScmPluginBidAdminController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;




    public function index()
    {

        return view(ScmPluginBid::getBladeRoot().'::admin/index',[

        ]);
    }



}
