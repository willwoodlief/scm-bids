<?php
namespace Scm\PluginBid\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Scm\PluginArchive\Requests\BidSaveRequest;
use Scm\PluginBid\Facades\ScmPluginBid;

use Illuminate\Http\Request;
use Scm\PluginBid\Models\ScmPluginBidSingle;


class ScmPluginBidAdminController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;




    public function index()
    {
        return view(ScmPluginBid::getBladeRoot().'::admin/index',[]);
    }

    public function new_bid() {
        $stub = new ScmPluginBidSingle();
        return view(ScmPluginBid::getBladeRoot().'::bids/new-bid',['bid'=>$stub]);
    }

    public function create_bid(BidSaveRequest $request) {

    }

    public function edit_bid(int $bid_id) {
        $bid = ScmPluginBidSingle::getBuilder(me_id: $bid_id);
        return view(ScmPluginBid::getBladeRoot().'::bids/edit-bid',['bid'=>$bid]);
    }

    public function update_bid(int $bid_id,BidSaveRequest $request) {
        $bid = ScmPluginBidSingle::getBuilder(me_id: $bid_id);
    }



}
