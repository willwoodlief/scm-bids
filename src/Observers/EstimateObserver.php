<?php

namespace Scm\PluginBid\Observers;




use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

use Plugins\Estimates\Models\Estimate;
use Scm\PluginBid\Models\ScmPluginBidFile;
use Scm\PluginBid\Models\ScmPluginBidSingle;
use Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class EstimateObserver
{

    public static function  recalcBudgetByEstimates(Estimate $estimate)
    : ?ScmPluginBidSingle
    {
        if($estimate->owner_type === Estimate::OWNER_TYPE_BID && $estimate->owner_id)
        {
            $bid = ScmPluginBidSingle::getBid(me_id: $estimate->owner_id,b_throw_exception_if_missing: false);
            if ($bid)
            {
                $total = Estimate::where('owner_type',Estimate::OWNER_TYPE_BID)
                    ->where('owner_id',$bid->id)
                    ->sum('total')
                ;
                if (!$total) { $total = 0; }
                $bid->budget = $total;
                $bid->save();
                return $bid;
            }

        }
        return null;
    }

    /**
     * @throws PathAlreadyExists
     * @throws \Exception|\Throwable
     */
    public static function  makeOrUpdateFileFromEstimate(Estimate $estimate) {

        if($estimate->owner_type === Estimate::OWNER_TYPE_BID && $estimate->owner_id)
        {
            $bid = ScmPluginBidSingle::getBid(me_id: $estimate->owner_id,b_throw_exception_if_missing: false);
            if ($bid)
            {
                $new_bid_file = null;
                try {
                    DB::beginTransaction();
                    /** @var ScmPluginBidFile $existing_bid_file */
                    $existing_bid_file = ScmPluginBidFile::getBuilderForBidFile(bid_id: $bid->id)
                        ->where('link_type', ScmPluginBidFile::LINK_TYPE_ESTIMATE)
                        ->where('link_id', $estimate->id)
                        ->first();

                    $file_name = null;
                    $pdf = $estimate->createPdf($file_name);
                    $tmp_dir = (new TemporaryDirectory())
                        ->deleteWhenDestroyed()
                        ->create();

                    $tmp_path = $tmp_dir->path($file_name);
                    file_put_contents($tmp_path, $pdf->output());
                    $uploaded_file = new UploadedFile($tmp_path, $file_name, 'application/pdf');
                    $new_bid_file = ScmPluginBidFile::createBidFile(bid: $bid, file: $uploaded_file, human_name: $file_name);
                    //pdf is not async file saving, so below will happen same thread
                    if ($existing_bid_file) {

                        $new_file_name = $new_bid_file->bid_file_name;
                        $new_size = $new_bid_file->bid_file_size_bytes;

                        $new_bid_file->bid_file_name = $existing_bid_file->bid_file_name;
                        $new_bid_file->bid_file_size_bytes = $existing_bid_file->bid_file_size_bytes;
                        $new_bid_file->save();

                        $existing_bid_file->bid_file_name = $new_file_name;
                        $existing_bid_file->bid_file_size_bytes = $new_size;
                        $existing_bid_file->save(); //update the version of the pdf with existing file

                        $new_bid_file->delete(); //remove older pdf version saved
                    } else {
                        $new_bid_file->link_type = ScmPluginBidFile::LINK_TYPE_ESTIMATE;
                        $new_bid_file->link_id = $estimate->id;
                        $new_bid_file->save();
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    $new_bid_file?->delete();
                    DB::rollBack();
                    throw $e;
                }
            }

        }
    }

    public static function  maybeDeleteFileFromEstimate(Estimate $estimate) {
        if($estimate->owner_type === Estimate::OWNER_TYPE_BID && $estimate->owner_id) {
            $bid = ScmPluginBidSingle::getBid(me_id: $estimate->owner_id, b_throw_exception_if_missing: false);
            if ($bid) {

                /** @var ScmPluginBidFile|null $existing_bid_file */
                $existing_bid_file = ScmPluginBidFile::getBuilderForBidFile(bid_id: $bid->id)
                    ->where('link_type', ScmPluginBidFile::LINK_TYPE_ESTIMATE)
                    ->where('link_id', $estimate->id)
                    ->first();

                $existing_bid_file?->delete();
            }
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
     * @throws \Throwable
     */
    public function created(Estimate $estimate): void
    {
        $estimate->refresh();
        static::recalcBudgetByEstimates(estimate: $estimate);
        static::makeOrUpdateFileFromEstimate(estimate: $estimate);

    }

    /**
     * Handle the Estimate "updated" event.
     * @throws \Throwable
     */
    public function updated(Estimate $estimate): void
    {
        $estimate->refresh();
        static::recalcBudgetByEstimates(estimate: $estimate);
        static::makeOrUpdateFileFromEstimate(estimate: $estimate);

    }

    /**
     * Handle the Estimate "deleted" event.
     */
    public function deleted(Estimate $estimate): void
    {
        static::recalcBudgetByEstimates(estimate: $estimate);
        static::maybeDeleteFileFromEstimate(estimate: $estimate);
    }

}
