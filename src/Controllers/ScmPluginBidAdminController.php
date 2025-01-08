<?php
namespace Scm\PluginBid\Controllers;



use App\Helpers\Utilities;
use App\Models\Contractor;

use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Scm\PluginBid\Exceptions\ScmPluginBidException;
use Scm\PluginBid\Models\ScmPluginBidFile;
use Scm\PluginBid\Requests\BidSaveRequest;

use Scm\PluginBid\Facades\ScmPluginBid;

use Scm\PluginBid\Models\ScmPluginBidSingle;
use Scm\PluginBid\Models\ScmPluginBidStat;
use Illuminate\Http\Request;


class ScmPluginBidAdminController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;




    public function index()
    {
        return view(ScmPluginBid::getBladeRoot().'::admin/index',[]);
    }

    public function bid_list()
    {
        $bids = ScmPluginBidSingle::getBuilderForBid()->orderBy('created_at','desc')->get();
        return view(ScmPluginBid::getBladeRoot().'::admin/bid-list',['bids'=>$bids]);
    }

    public function new_bid() {
        $stub = new ScmPluginBidSingle();
        $contractors = Contractor::getAllContractors();
        return view(ScmPluginBid::getBladeRoot().'::bids/new-bid',['bid'=>$stub,'contractors'=> $contractors]);
    }

    /**
     * @param BidSaveRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function create_bid(BidSaveRequest $request) {

        try {
            DB::beginTransaction();
            $bid = new ScmPluginBidSingle();
            $bid->fill($request->validated());
            $bid->bid_created_by_user_id = Utilities::get_logged_user()?->id;
            $bid->save();
            $stat = ScmPluginBidStat::addNewBid(bid: $bid);
            $refreshed_bid = ScmPluginBidSingle::getBid(me_id: $bid->id);

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'bid' => $refreshed_bid,'stat'=> $stat]);
            } else {
                return redirect()->route('scm-bid.admin.bids.edit',['bid_id'=>$refreshed_bid->id]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

    }

    public function edit_bid(int $bid_id) {
        try {
            $bid = ScmPluginBidSingle::getBid(me_id: $bid_id);
        } catch (ScmPluginBidException $e) {
            abort(404,$e->getMessage());
        }

        $contractors = Contractor::getAllContractors();
        return view(ScmPluginBid::getBladeRoot().'::bids/edit-bid',['bid'=>$bid,'contractors'=> $contractors]);
    }

    /**
     * @throws \Exception
     */
    public function add_files(int $bid_id, Request $request) {
        $paths = [];
        $bid = ScmPluginBidSingle::getBid(me_id: $bid_id);
        try {
            DB::beginTransaction();
            foreach ($request->allFiles() as $file) {
                $paths[] = $bid->process_uploaded_file($file);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            foreach ($paths as $path) {
                unlink($path);
            }
            throw $e;
        }

    }

    public function remove_file(int $bid_id,int $file_id,Request $request) {
        /** @var ScmPluginBidFile $bid_file */
        $bid_file = ScmPluginBidFile::getBuilderForBidFile(me_id: $file_id,bid_id: $bid_id)->first();
        if (!$bid_file) {
            throw new ScmPluginBidException("Bid file not found for id#$file_id that belongs to bid #$bid_id");
        }
        $bid_file->delete();
        if ($request->ajax()) {
            return response()->json(['success' => true, 'file' => $bid_file]);
        } else {
            return redirect()->route('scm-bid.admin.bids.edit',['bid_id'=>$bid_file->file_bid->id]);
        }
    }

    public function download_file(int $bid_id,int $file_id) {
        /** @var ScmPluginBidFile $bid_file */
        $bid_file = ScmPluginBidFile::getBuilderForBidFile(me_id: $file_id,bid_id: $bid_id)->first();
        if (!$bid_file) {
            throw new ScmPluginBidException("Bid file not found for id#$file_id that belongs to bid #$bid_id");
        }
        $path = $bid_file->getAbsolutePath();
        if (!$path) {
            abort(404,"Cannot find the absolute path for file ". $bid_file->getName());
        }
        return response()->download($path,$bid_file->bid_file_human_name);
    }
    /**
     * @throws \Exception
     */
    public function update_bid(int $bid_id, BidSaveRequest $request) {
        $bid = ScmPluginBidSingle::getBid(me_id: $bid_id);

        try {
            DB::beginTransaction();
            $bid->fill($request->validated());
            $bid->save();



            $refreshed_bid = ScmPluginBidSingle::getBid(me_id: $bid->id);

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'bid' => $refreshed_bid]);
            } else {
                return redirect()->route('scm-bid.admin.bids.show',['bid_id'=>$refreshed_bid->id]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function show_bid(int $bid_id) {
        try {
            $bid = ScmPluginBidSingle::getBid(me_id: $bid_id);
        } catch (ScmPluginBidException $e) {
            abort(404,$e->getMessage());
        }
        return view(ScmPluginBid::getBladeRoot().'::bids/show-bid',['bid'=>$bid]);
    }

    /**
     * @throws \Exception
     */
    public function bid_successful(int $bid_id,Request $request) {
        $bid = ScmPluginBidSingle::getBid(me_id: $bid_id);
        //create project, delete bid, update stats, redirect to project edit page

        /** @var ScmPluginBidFile[] $old_bid_files */
        $old_bid_files = [];
        try {
            DB::beginTransaction();
            $project_id = Project::insertGetId([
                'contractor' => $bid->bid_contractor_id,
                'project_name' => $bid->bid_name,
                'address' => $bid->address,
                'city' => $bid->city,
                'state' => $bid->state,
                'zip' => $bid->zip,
                'budget' => $bid->budget,
                'scratch_pad' => $bid->scratch_pad,
                'start_date' => DB::raw("Date(NOW())"),
                'end_date' => DB::raw("Date(DATE_ADD(NOW(), INTERVAL 1 MONTH))"),
                'super_name' => 'Main Contact',
                'super_phone' => '123-555-6789',
                'pm_name' => 'Secondary Contact',
                'pm_phone' => '123-555-6789',
                'status' => Project::STATUS_NOT_STARTED
            ]);

            $stat = ScmPluginBidStat::markSuccessfulBid(bid: $bid,project_id: $project_id);
            $project_files = $bid->moveFilesToProject(project_id: $project_id,old_bid_files: $old_bid_files );
            $bid->delete();
            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'bid' => $bid,'stat'=> $stat,
                    'project_id' => $project_id, 'project_files' => $project_files,
                    'project_edit_url'=>route('project.edit',['project_id'=>$project_id])]);
            } else {
                return redirect()->route('project.edit',['project_id'=>$project_id]);
            }
        } catch (\Exception $e) {
            //if error, move the bid files back so those still work (don't loose files)
            foreach ($old_bid_files as $older_bid) {
                $project_file = $older_bid->getProjectFile();
                if ($project_file) {
                    Storage::disk()->move($project_file->getRelativePath(), $older_bid->getRelativePath());
                }

            }
            DB::rollback();
            throw $e;
        }

    }

    /**
     * @throws \Exception
     */
    public function bid_failed(int $bid_id,Request $request) {
        $bid = ScmPluginBidSingle::getBid(me_id: $bid_id);
        try {
            DB::beginTransaction();

            $stat = ScmPluginBidStat::markFailedBid(bid: $bid);
            $bid->delete();

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'bid' => $bid,'stat'=> $stat,'bid_list_url'=>route('scm-bid.admin.bids.list')]);
            } else {
                return redirect()->route('scm-bid.admin.bids.list',['bid_id'=>$bid->id]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }



}
