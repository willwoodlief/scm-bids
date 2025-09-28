<?php

namespace Scm\PluginBid\Observers;



use Illuminate\Support\Facades\Log;
use Plugins\Estimates\Models\Estimate;
use Scm\PluginBid\Models\ScmPluginBidSingle;

class EstimateObserver
{

    public static function  recalcBudgetByEstimates(Estimate $estimate) {
        if($estimate->owner_type === Estimate::OWNER_TYPE_BID && $estimate->owner_id)
        {
            $bid = ScmPluginBidSingle::getBid(me_id: $estimate->owner_id);
            $total = Estimate::where('owner_type',Estimate::OWNER_TYPE_BID)
                ->where('owner_id',$bid->id)
                ->sum('total')
            ;
            if (!$total) { $total = 0; }
            $bid->budget = $total;
            $bid->save();
        }
    }

    /** in here to centralize estimate plugin logic */
    public static function  shiftEstimatesToProject(ScmPluginBidSingle $bid, int $project_id) {

        /** @var Estimate[] $my_estimates */
        $my_estimates = Estimate::where('owner_type',Estimate::OWNER_TYPE_BID)
            ->where('owner_id',$bid->id)
            ->get();

        foreach ($my_estimates as $estimate) {
            $estimate->owner_type = Estimate::OWNER_TYPE_PROJECT;
            $estimate->owner_id = $project_id;
            $estimate->saveQuietly(); //avoid recursion
        }
    }

    public static function  disownEstimates(ScmPluginBidSingle $bid) {

        /** @var Estimate[] $my_estimates */
        $my_estimates = Estimate::where('owner_type',Estimate::OWNER_TYPE_BID)
            ->where('owner_id',$bid->id)
            ->get();

        foreach ($my_estimates as $estimate) {
            //remove polymorphic association from bids (cannot use fk)
            $estimate->owner_type = null;
            $estimate->owner_id = null;
            $estimate->saveQuietly(); //avoid recursion
        }
    }


    /**
     * Handle the Estimate "created" event.
     */
    public function created(Estimate $estimate): void
    {
        static::recalcBudgetByEstimates(estimate: $estimate);
        Log::notice("estimate created ". $estimate->id);
    }

    /**
     * Handle the Estimate "updated" event.
     */
    public function updated(Estimate $estimate): void
    {

        static::recalcBudgetByEstimates(estimate: $estimate);
        Log::notice("estimate updated ". $estimate->id);
    }

    /**
     * Handle the Estimate "deleted" event.
     */
    public function deleted(Estimate $estimate): void
    {
        static::recalcBudgetByEstimates(estimate: $estimate);
        Log::notice("estimate deleted ". $estimate->id);
    }

}
