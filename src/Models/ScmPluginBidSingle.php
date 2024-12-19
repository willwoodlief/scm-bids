<?php
namespace Scm\PluginBid\Models;
use App\Models\Contractor;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Scm\PluginBid\Exceptions\ScmPluginBidException;


/**
 *
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property int bid_contractor_id
 * @property int bid_created_by_user_id
 * @property float latitude
 * @property float longitude
 * @property float budget

 * @property string start_date
 * @property string end_date
 * @property string bid_name
 * @property string address
 * @property string city
 * @property string state
 * @property string zip
 *
 *
 * @property string created_at
 * @property string updated_at
 *
 * @property int created_at_ts
 * @property int updated_at_ts

 * @property Contractor bid_contractor
 * @property User bid_created_by_user
 * @property ScmPluginBidFile[] bid_files
 *
 */
class ScmPluginBidSingle extends Model

{
    use HasFactory;

    protected $table = 'scm_plugin_bid_singles';
    public $timestamps = false;

    public function bid_contractor() : BelongsTo {
        return $this->belongsTo(Contractor::class,'bid_contractor_id');
    }

    public function bid_created_by_user() : BelongsTo {
        return $this->belongsTo(User::class,'bid_created_by_user_id');
    }



    public function bid_files() : HasMany {
        return $this->hasMany(ScmPluginBidFile::class,'owning_bid_id','id');
    }

    public static function getBuilder(
        ?int $me_id = null,  ?int $contractor_id = null
    )
    : ScmPluginBidSingle
    {
        $what =  static::getBuilderForBid(me_id: $me_id,contractor_id: $contractor_id)->first();
        if (!$what) {
            $reason = '';
            if ($me_id) { $reason .= "Using id of $me_id.";}
            if ($contractor_id) { $reason .= " Using contractor id of $contractor_id.";}
            throw new ScmPluginBidException("Cannot find the bid $reason");
        }
    }
    public static function getBuilderForBid(
        ?int $me_id = null,  ?int $contractor_id = null
    )
    : Builder
    {

        $build = static::select('scm_plugin_bid_singles.*')
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_singles.created_at) as created_at_ts")
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_singles.updated_at) as updated_at_ts")

            /** @uses static::bid_contractor() */
            ->with('bid_contractor')

            /** @uses static::bid_created_by_user() */
            ->with('bid_created_by_user')

            /** @uses static::bid_files() */
            ->with('bid_files')
            ;

        if ($me_id) {
            $build->where('scm_plugin_bid_singles.id',$me_id);
        }

        if ($contractor_id) {
            $build->where('scm_plugin_bid_singles.archived_project_id',$contractor_id);
        }


        return $build;
    }



}
