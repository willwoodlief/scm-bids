<?php
namespace Scm\PluginBid\Models;
use App\Helpers\General\UnitStat;
use App\Helpers\Utilities;
use App\Models\Contractor;
use App\Models\Enums\UnitOfStat;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Scm\PluginBid\Models\Enums\TypeOfStat;


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
 * @property string bid_name
 *
 * @property Project stat_project
 * @property User stat_user
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

    /**
     * @return UnitStat[]
     */
    public static function getUnitsForStats(TypeOfStat $stat_type,UnitOfStat $unit_type,
                                            null|string|int $after_date = null,
                                            null|string|int $before_date = null)
    :array
    {
        if ($after_date) {
            $after_date_string = Carbon::parse($after_date,config('app.timezone'))->toDateString();
        } else {
            $min_datetime = ScmPluginBidStat::min('bid_created_at');
            if ($min_datetime) {
                $after_date_string = Carbon::parse($min_datetime,config('app.timezone'))->toDateString() ;
            } else {
                //select one month before
                $after_date_string = Carbon::now()->timezone(config('app.timezone'))->subMonths()->toDateString();
            }

        }

        if ($before_date) {
            $before_date_string = Carbon::parse($before_date,config('app.timezone'))->toDateString();
        } else {
            $before_date_string = Carbon::now()->timezone(config('app.timezone'))->toDateString();
        }


        $where_clause = match ($stat_type) {
           TypeOfStat::ACTIVE => "scm_plugin_bid_stats.bid_success_at is null and scm_plugin_bid_stats.bid_failed_at is null",
           TypeOfStat::SUCCESSFUL => "scm_plugin_bid_stats.bid_success_at is not null and scm_plugin_bid_stats.bid_failed_at is null",
           TypeOfStat::FAILED => "scm_plugin_bid_stats.bid_success_at is null and scm_plugin_bid_stats.bid_failed_at is not null",
        };

        $column = match ($stat_type) {
           TypeOfStat::ACTIVE => "bid_created_at",
           TypeOfStat::SUCCESSFUL => "bid_success_at",
           TypeOfStat::FAILED => "bid_failed_at",
        };

        $unit = match ($unit_type) {
            UnitOfStat::DAY => "day",
            UnitOfStat::MONTH => "month"
        };

        $sql = "
        WITH RECURSIVE all_dates(dt) AS (
            SELECT '$after_date_string' dt
            UNION ALL
            SELECT dt + interval 1 $unit FROM all_dates WHERE dt <= '$before_date_string'
        )
        SELECT d.dt date, t.number_of, t.sum_budget,t.ids
        FROM all_dates d
         LEFT JOIN (
            Select $unit($column)              AS unit_at,
                   DATE(MAX($column))        AS max_date,
                   COUNT(id)                 AS number_of,
                   SUM(budget)               AS sum_budget,
                   GROUP_CONCAT(id)          AS ids
            FROM `scm_plugin_bid_stats`
            WHERE $where_clause
            GROUP BY $unit($column)
        ) t ON t.max_date = d.dt
        ORDER BY d.dt
        ";

       $res =  DB::select($sql);

       $ret = [];
       foreach ($res as $row) {
           $ids = Utilities::to_int_array(given:explode(',',$row->ids),b_allow_zero: false);
           $ret[] = new UnitStat(date: $row->date,number: $row->number_of??0, sum: $row->sum_budget??0,covered_ids: $ids);
       }

       return $ret;
    }


    public static function getBuilderForBidStat(
        ?int $me_id = null,  ?int $project_id = null,?int $bid_id = null,
        array $only_bid_ids = [],
        ?bool $b_only_resolved = null,
        bool $b_only_success = false,
        bool $b_only_fail = false,
        null|string|int $after_date = null,
        null|string|int $before_date = null,
        null|string|int $processed_after_date = null,
        null|string|int $processed_before_date = null
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

        if($b_only_resolved !== null) {
            if ($b_only_resolved) {
                $build->where(function (Builder $q)  {
                    $q->whereNotNull('scm_plugin_bid_stats.bid_success_at')
                        ->orWhereNotNull('scm_plugin_bid_stats.bid_failed_at');
                });
            } else {
                $build->whereNull('scm_plugin_bid_stats.bid_success_at')
                      ->whereNull('scm_plugin_bid_stats.bid_failed_at');
            }
        }

        if ($b_only_success) {
            $build->whereNotNull('scm_plugin_bid_stats.bid_success_at')
                ->whereNull('scm_plugin_bid_stats.bid_failed_at');
        }

        if ($b_only_fail) {
            $build->whereNull('scm_plugin_bid_stats.bid_success_at')
                ->whereNotNull('scm_plugin_bid_stats.bid_failed_at');
        }

        if ($after_date) {
            $date_string = Carbon::parse($after_date)->toDateString();
            $build->where('scm_plugin_bid_stats.bid_created_at','=>',$date_string);
        }

        if ($before_date) {
            $before_date_string = Carbon::parse($before_date)->toDateString();
            $build->where('scm_plugin_bid_stats.bid_created_at','<=',$before_date_string);
        }

        if ($processed_after_date) {
            $after_date_string = Carbon::parse($processed_after_date)->toDateString();
            $build->where(function (Builder $q) use($after_date_string) {
                $q->where('scm_plugin_bid_stats.bid_success_at','>=',$after_date_string)
                    ->orWhere('scm_plugin_bid_stats.bid_failed_at','>=',$after_date_string);
            });
        }

        if ($processed_before_date) {
            $before_date_string = Carbon::parse($processed_before_date)->toDateString();
            $build->where(function (Builder $q) use($before_date_string) {
                $q->where('scm_plugin_bid_stats.bid_success_at','<=',$before_date_string)
                    ->orWhere('scm_plugin_bid_stats.bid_failed_at','<=',$before_date_string);
            });
        }

        return $build;
    }

    public static function addNewBid(ScmPluginBidSingle $bid) : ScmPluginBidStat {
        $node = new ScmPluginBidStat();
        $node->stats_bid_id = $bid->id;
        $node->stats_contractor_id = $bid->bid_contractor_id;
        $node->budget = $bid->budget;
        $node->bid_name = $bid->bid_name;
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
            $node->bid_name = $bid->bid_name;
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
            $node->bid_name = $bid->bid_name;
            $node->stats_user_id = $bid->bid_created_by_user_id;
            $node->save();
        }

        DB::statement("Update scm_plugin_bid_stats set bid_success_at = NOW(),stats_project_id = ? WHERE id = ?",[$project_id,$node->id]);
        $node->refresh();
        return $node;
    }

    public function getName() :?string {
        return $this->bid_name;
    }



}
