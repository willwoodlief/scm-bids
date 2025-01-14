<?php
namespace Scm\PluginBid\Models;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


/**
 *
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property int stats_bid_id
 * @property int stats_project_id
 * @property int stats_contractor_id
 * @property int stats_user_id
 * @property float budget
 * @property string bid_created_at
 * @property string bid_success_at
 * @property string bid_failed_at
 *
 * @property int bid_created_at_ts
 * @property int bid_success_at_ts
 * @property int bid_failed_at_ts
 *
 * @property Project stat_project
 * @property ScmPluginBidSingle stat_bid
 * @property Contractor stat_contractor
 */
class ScmPluginBidStat extends Model
{
    use HasFactory;

    protected $table = 'scm_plugin_bid_stats';
    public $timestamps = false;

    public function stat_bid() : BelongsTo {
        return $this->belongsTo(ScmPluginBidSingle::class,'stats_bid_id');
    }

    public function stat_project() : BelongsTo {
        return $this->belongsTo(Project::class,'stats_project_id');
    }

    public function stat_contractor() : BelongsTo {
        return $this->belongsTo(Contractor::class,'stats_contractor_id');
    }

    public function stat_user() : BelongsTo {
        return $this->belongsTo(User::class,'stats_user_id');
    }


    public static function getBuilderForBidStat(
        ?int $me_id = null,  ?int $project_id = null,?int $bid_id = null,
        array $only_bid_ids = []
    )
    : Builder
    {

        $build = static::select('scm_plugin_bid_stats.*')
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_stats.bid_created_at) as bid_created_at_ts")
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_stats.bid_success_at) as bid_success_at_ts")
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_stats.bid_failed_at) as bid_failed_at_ts")

            /** @uses static::stat_project() */
            ->with('stat_project')

            /** @uses static::stat_bid() */
            ->with('stat_bid')

            /** @uses static::stat_contractor() */
            ->with('stat_contractor')

            /** @uses static::stat_user() */
            ->with('stat_user')
            ;

        if ($me_id) {
            $build->where('scm_plugin_bid_stats.id',$me_id);
        }

        if ($project_id) {
            $build->where('scm_plugin_bid_stats.stats_project_id',$project_id);
        }

        if ($bid_id) {
            $build->where('scm_plugin_bid_stats.stats_bid_id',$bid_id);
        }

        if (count($only_bid_ids)) {
            $build->whereIn('scm_plugin_bid_stats.stats_bid_id',$only_bid_ids);
        }


        return $build;
    }

    public static function addNewBid(ScmPluginBidSingle $bid) : ScmPluginBidStat {
        $node = new ScmPluginBidStat();
        $node->stats_bid_id = $bid->id;
        $node->stats_contractor_id = $bid->bid_contractor_id;
        $node->budget = $bid->budget;
        $node->stats_user_id = $bid->bid_created_by_user_id;
        $node->save();
        DB::statement("Update scm_plugin_bid_stats set bid_created_at = NOW() WHERE id = ?",[$node->id]);
        $node->refresh();
        return $node;
    }

    public static function markFailedBid(ScmPluginBidSingle $bid) : ScmPluginBidStat {
        /** @var ScmPluginBidStat $node */
        $node = static::getBuilderForBidStat(bid_id: $bid->id)->first();
        if (!$node) {
            $node = new ScmPluginBidStat();
            $node->stats_bid_id = $bid->id;
            $node->stats_contractor_id = $bid->bid_contractor_id;
            $node->budget = $bid->budget;
            $node->stats_user_id = $bid->bid_created_by_user_id;
            $node->save();
        }

        DB::statement("Update scm_plugin_bid_stats set bid_failed_at = NOW() WHERE id = ?",[$node->id]);
        $node->refresh();
        return $node;
    }

    public static function markSuccessfulBid(ScmPluginBidSingle $bid,int $project_id) : ScmPluginBidStat {
        /** @var ScmPluginBidStat $node */
        $node = static::getBuilderForBidStat(bid_id: $bid->id)->first();
        if (!$node) {
            $node = new ScmPluginBidStat();
            $node->stats_bid_id = $bid->id;
            $node->stats_contractor_id = $bid->bid_contractor_id;
            $node->budget = $bid->budget;
            $node->stats_user_id = $bid->bid_created_by_user_id;
            $node->save();
        }

        DB::statement("Update scm_plugin_bid_stats set bid_success_at = NOW(),stats_project_id = ? WHERE id = ?",[$project_id,$node->id]);
        $node->refresh();
        return $node;
    }



}
