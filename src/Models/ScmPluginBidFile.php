<?php
namespace Scm\PluginBid\Models;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 *
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property int owning_bid_id
 * @property int owning_project_id
 * @property int uploaded_by_user_id
 * @property int bid_file_size_bytes
 * @property int bid_file_is_image


 * @property string bid_file_extension
 * @property string bid_file_name
 * @property string bid_file_human_name
 * @property string bid_file_mime_type
 *
 *
 * @property string created_at
 * @property string updated_at
 *
 * @property int created_at_ts
 * @property int updated_at_ts

 * @property User file_user
 * @property Project file_project
 * @property ScmPluginBidSingle file_bid
 */
class ScmPluginBidFile extends Model

{
    use HasFactory;

    protected $table = 'scm_plugin_bid_files';
    public $timestamps = false;

    public function file_bid() : BelongsTo {
        return $this->belongsTo(ScmPluginBidSingle::class,'owning_bid_id');
    }

    public function file_project() : BelongsTo {
        return $this->belongsTo(Project::class,'owning_project_id');
    }

    public function file_user() : BelongsTo {
        return $this->belongsTo(User::class,'uploaded_by_user_id');
    }



    public static function getBuilderForBidFile(
        ?int $me_id = null,  ?int $project_id = null,?int $bid_id = null
    )
    : Builder
    {

        $build = static::select('scm_plugin_bid_files.*')
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_files.created_at) as created_at_ts")
            ->selectRaw("UNIX_TIMESTAMP(scm_plugin_bid_files.updated_at) as updated_at_ts")

            /** @uses static::file_project() */
            ->with('file_project')

            /** @uses static::file_user() */
            ->with('file_user')

            /** @uses static::file_bid() */
            ->with('file_bid')
            ;

        if ($me_id) {
            $build->where('scm_plugin_bid_files.id',$me_id);
        }

        if ($project_id) {
            $build->where('scm_plugin_bid_files.owning_project_id',$project_id);
        }

        if ($bid_id) {
            $build->where('scm_plugin_bid_files.owning_bid_id',$bid_id);
        }


        return $build;
    }



}
