<?php
namespace Scm\PluginBid\Controllers;



use App\Helpers\Utilities;
use App\Http\Controllers\ContractorsController;
use App\Http\Requests\ContractorSaveRequest;
use App\Models\Contractor;

use App\Models\Enums\UnitOfStat;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Scm\PluginBid\Exceptions\ScmPluginBidException;
use Scm\PluginBid\Helpers\PluginPermissions;
use Scm\PluginBid\Models\Enums\TypeOfStat;
use Scm\PluginBid\Models\ScmPluginBidFile;
use Scm\PluginBid\Requests\BidSaveRequest;
use Scm\PluginBid\Facades\ScmPluginBid;
use Scm\PluginBid\Models\ScmPluginBidSingle;
use Scm\PluginBid\Models\ScmPluginBidStat;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class ScmPluginBidAdminController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;




    public function index(Request $request)
    {
        $bids = ScmPluginBidSingle::getBuilderForBid(only_bid_ids: PluginPermissions::getBidIdsUserCanView(Utilities::get_logged_user()))
            ->orderBy('created_at','desc')->get();

        $stats = ScmPluginBidStat::getBuilderForBidStat(only_bid_ids: PluginPermissions::getBidIdsUserCanView(Utilities::get_logged_user()))
            ->orderBy('bid_created_at','desc')->get();


        $after_date = $request->query->getString('after_date');
        $before_date = $request->query->getString('before_date');
        $unit_type = UnitOfStat::tryFromInput($request->query->getString('unit',UnitOfStat::DAY->value));


        $stats_success = ScmPluginBidStat::getUnitsForStats(stat_type: TypeOfStat::SUCCESSFUL,unit_type: $unit_type,after_date: $after_date,before_date: $before_date);
        $stats_fail = ScmPluginBidStat::getUnitsForStats(stat_type: TypeOfStat::FAILED,unit_type: $unit_type,after_date: $after_date,before_date: $before_date);
        $stats_active = ScmPluginBidStat::getUnitsForStats(stat_type: TypeOfStat::ACTIVE,unit_type: $unit_type,after_date: $after_date,before_date: $before_date);



        return view(ScmPluginBid::getBladeRoot().'::bids.index',['bids'=>$bids,'stats'=>$stats,
            'stats_success'=>$stats_success,'stats_fail'=>$stats_fail,'stats_active'=>$stats_active,'unit_type'=>$unit_type]);
    }

    public function bid_list()
    {
        $bids = ScmPluginBidSingle::getBuilderForBid(only_bid_ids: PluginPermissions::getBidIdsUserCanView(Utilities::get_logged_user()))
            ->orderBy('created_at','desc')->get();
        return view(ScmPluginBid::getBladeRoot().'::bids.bid-list',['bids'=>$bids]);
    }


    public function get_new_contactor_form()
    {
        if (!Utilities::get_logged_user()->canCreateContractor()) {
            abort(Response::HTTP_FORBIDDEN,'No Permission');
        }
        $contractor = new Contractor();
        $html = view('contractors.parts.contractor_form',compact('contractor'))->render();
        $wrapped_html = view(ScmPluginBid::getBladeRoot().'::bids.shared.wrapped-contractor-form',['html'=>$html])->render();
        return response()->json(['success'=>true,'message'=>"Made contractor form ",'html'=>$wrapped_html]);
    }

    /**
     * @throws \Exception
     */
    public function create_contractor(ContractorsController $con,ContractorSaveRequest $request) {
        try {
            DB::beginTransaction();
            $inner_ret = $con->create_contractor($request);
            $data = $inner_ret->getData(assoc: true);
            /** @var Contractor $new_contractor */
            $data['contractor_id'] = $data['contractor']['id']??null;
            DB::commit();
            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function new_bid() {
        $stub = new ScmPluginBidSingle();
        $contractors = Contractor::getAllContractors(Utilities::get_logged_user()->getContractorIdsCanSee());
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
                return redirect()->route('scm-bid.bid.edit',['single_bid'=>$refreshed_bid->id]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

    }

    public function edit_bid(ScmPluginBidSingle $bid) {
        $contractors = Contractor::getAllContractors(Utilities::get_logged_user()->getContractorIdsCanSee());
        return view(ScmPluginBid::getBladeRoot().'::bids/edit-bid',['bid'=>$bid,'contractors'=> $contractors]);
    }

    public function show_processed() {
        $resolved = ScmPluginBidStat::getBuilderForBidStat(b_only_resolved: true)
            ->orderBy('bid_success_at')->orderBy('bid_failed_at')->get();
        return view(ScmPluginBid::getBladeRoot().'::bids/processed',['resolved'=>$resolved]);
    }



    /**
     * @throws \Exception
     */
    public function add_files(ScmPluginBidSingle $bid, Request $request) {
        $paths = [];
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

    public function remove_file(ScmPluginBidSingle $bid,ScmPluginBidFile $bid_file,Request $request) {

        if ($bid_file->owning_bid_id !== $bid->id) {
            throw new ScmPluginBidException("Bid file not found for id#$bid_file->id that belongs to bid #$bid->id");
        }
        $bid_file->delete();
        if ($request->ajax()) {
            return response()->json(['success' => true, 'file' => $bid_file]);
        } else {
            return redirect()->route('scm-bid.bid.edit',['single_bid'=>$bid_file->file_bid->id]);
        }
    }

    public function download_file(ScmPluginBidSingle $bid,ScmPluginBidFile $bid_file) {

        if ($bid_file->owning_bid_id !== $bid->id) {
            throw new ScmPluginBidException("Bid file not found for id#$bid_file->id that belongs to bid #$bid->id");
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
    public function update_bid(ScmPluginBidSingle $bid, BidSaveRequest $request) {

        try {
            DB::beginTransaction();
            $bid->fill($request->validated());
            $bid->save();



            $refreshed_bid = ScmPluginBidSingle::getBid(me_id: $bid->id);

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'bid' => $refreshed_bid]);
            } else {
                return redirect()->route('scm-bid.bid.show',['single_bid'=>$refreshed_bid->id]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function show_bid(ScmPluginBidSingle $bid) {
        return view(ScmPluginBid::getBladeRoot().'::bids/show-bid',['bid'=>$bid]);
    }

    /**
     * @throws \Exception
     */
    public function bid_successful(ScmPluginBidSingle $bid,Request $request) {

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
                'super_name' => 'Secondary Contact',
                'super_phone' => '123-555-6789',
                'pm_name' => 'Main Contact',
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
                    'project_edit_url'=>route('project.edit',['project'=>$project_id])]);
            } else {
                return redirect()->route('project.edit',['project'=>$project_id]);
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
    public function bid_failed(ScmPluginBidSingle $bid,Request $request) {

        try {
            DB::beginTransaction();

            $stat = ScmPluginBidStat::markFailedBid(bid: $bid);
            $bid->delete();

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'bid' => $bid,'stat'=> $stat,'bid_list_url'=>route('scm-bid.list')]);
            } else {
                return redirect()->route('scm-bid.list',['bid_id'=>$bid->id]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }



}
