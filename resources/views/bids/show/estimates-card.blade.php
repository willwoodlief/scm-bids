@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     */

    use Plugins\Estimates\Models\Estimate;if (!\Scm\PluginBid\Facades\ScmPluginBid::isEstimatePluginInstalled()) {
        return;
    }

    $estimates  = Estimate::where('owner_type',Estimate::OWNER_TYPE_BID)
            ->where('owner_id',$bid->id)
            ->get();
@endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            Estimates
        </h4>
    </div>
    <div class="card-body">
        @include(\Plugins\Estimates\Facades\ScmPluginEstimate::getBladeRoot() .'::estimates.index.table',compact('estimates'))
    </div>
</div>


