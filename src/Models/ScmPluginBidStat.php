<?php
namespace Scm\PluginBid\Models;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 *
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property int stats_bid_id
 * @property int stats_project_id
 * @property float budget
 * @property string bid_created_at
 * @property string bid_success_at
 * @property string bid_failed_at

 * @property Project stat_project
 * @property ScmPluginBidSingle stat_bid
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




    public static function getBuilderForBidStat(
        ?int $me_id = null,  ?int $project_id = null,?int $bid_id = null
    )
    : Builder
    {

        $build = static::select('scm_plugin_bid_stats.*')
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_stats.created_at) as created_at_ts")
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_stats.updated_at) as updated_at_ts")

            /** @uses static::stat_project() */
            ->with('stat_project')

            /** @uses static::stat_bid() */
            ->with('stat_bid')
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


        return $build;
    }



}
