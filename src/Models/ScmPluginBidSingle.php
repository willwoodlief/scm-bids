<?php
namespace Scm\PluginBid\Models;
use App\Models\Contractor;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Scm\PluginBid\Exceptions\ScmPluginBidException;
use Scm\PluginBid\Facades\ScmPluginBid;


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

 * @property string bid_name
 * @property string address
 * @property string city
 * @property string state
 * @property string zip
 * @property string scratch_pad
 *
 *
 * @property string created_at
 * @property string updated_at
 *
 * @property int created_at_ts
 * @property int updated_at_ts

 * @property ScmPluginBidStat bid_stat
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

    protected $fillable = [
        'bid_contractor_id',
        'bid_name',
        'address',
        'city',
        'state',
        'zip',
        'latitude',
        'longitude',
        'budget',
        'scratch_pad'
    ];

    protected static function booted(): void
    {
        static::deleting(function (ScmPluginBidSingle $bid) {

            if (!$bid->id) {
                return false;
            }

            /**
             * @var ScmPluginBidFile[] $bid_files
             */
            $bid_files = $bid->bid_files()->get();
            foreach ($bid_files as $file) {
                $file->delete();
            }

            return true; //allow deletion
        });

        static::deleted(function (ScmPluginBidSingle $bid) {
            $bid->cleanup_resources();
        });

        static::updated(function (ScmPluginBidSingle $bid) {
            if ( $bid->bid_stat ) {
                $b_save = false;
                if ( $bid->bid_stat->bid_name !== $bid->bid_name) {
                    $bid->bid_stat->bid_name = $bid->bid_name;
                    $b_save = true;
                }

                if ( $bid->bid_stat->budget !== $bid->budget) {
                    $bid->bid_stat->budget = $bid->budget;
                    $b_save = true;
                }

                if ($b_save) {
                    $bid->bid_stat->save();
                }
            }

        });
    }

    public function bid_contractor() : BelongsTo {
        return $this->belongsTo(Contractor::class,'bid_contractor_id');
    }

    public function bid_stat() : HasOne {
        return $this->hasOne(ScmPluginBidStat::class,'stats_bid_id','id');
    }

    public function bid_created_by_user() : BelongsTo {
        return $this->belongsTo(User::class,'bid_created_by_user_id');
    }



    public function bid_files() : HasMany {
        return $this->hasMany(ScmPluginBidFile::class,'owning_bid_id','id');
    }

    public function getName() : string {
        return $this->bid_name?: "Bid #$this->id";
    }

    public static function getBid(
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
        return $what;
    }

    public static function getBuilderForBid(
        ?int $me_id = null,  ?int $contractor_id = null,array $only_bid_ids = []
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

            /** @uses static::bid_stat() */
            ->with('bid_stat')
            ;

        if ($me_id) {
            $build->where('scm_plugin_bid_singles.id',$me_id);
        }

        if (count($only_bid_ids)) {
            $build->whereIn('scm_plugin_bid_singles.id',$only_bid_ids);
        }

        if ($contractor_id) {
            $build->where('scm_plugin_bid_singles.archived_project_id',$contractor_id);
        }


        return $build;
    }

    const DOCUMENTS_FOLDER = 'documents';
    const BIDS_FOLDER = 'bids';

    public function cleanup_resources() {
        $relative_path = $this->get_document_directory();
        $absolute_path = realpath(storage_path('app'. DIRECTORY_SEPARATOR .$relative_path)); //todo remove abs path
        if (!$absolute_path) {return;}

        $it = new RecursiveDirectoryIterator($absolute_path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($absolute_path);
    }

    public function get_document_directory() : string  {
        if (!$this->id) { throw new ScmPluginBidException("Trying get bid document directory with no bid id");}
        return ScmPluginBid::getPluginStorageRoot().DIRECTORY_SEPARATOR.static::BIDS_FOLDER.
            DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . static::DOCUMENTS_FOLDER;
    }

    public function get_image_directory() : string  {
        if (!$this->id) { throw new ScmPluginBidException("Trying get bid image directory with no bid id");}
        return ScmPluginBid::getPluginUploadRoot().DIRECTORY_SEPARATOR.static::BIDS_FOLDER.
            DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . static::DOCUMENTS_FOLDER;
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws \Exception
     */
    public function process_uploaded_file(UploadedFile $file) : string {
        if (!$this->id) { throw new \LogicException("Trying to save a file to an unsaved bid");}
        if($file->getSize() > ProjectFile::getMaxFileSize()) {
            $human_max_filesize = \App\Helpers\Utilities::human_filesize(\App\Models\ProjectFile::getMaxFileSize());
            $human_my_filesize = \App\Helpers\Utilities::human_filesize($file->getSize());
            throw new ScmPluginBidException("Bid file too big. The max is $human_max_filesize but the file is $human_my_filesize");
        }
        return ScmPluginBidFile::process_uploaded_file($this,$file);
    }

    /**
     * @return ScmPluginBidFile[]
     */
    public function get_images() : array  {
        $ret = [];
        foreach ($this->bid_files as $file) {
            if ($file->isImage()) {$ret[] = $file;}
        }
        return $ret;
    }

    /**
     * @param int $project_id
     * @param ScmPluginBidFile[] $old_bid_files
     * @return ProjectFile[]
     */
    public function moveFilesToProject(int $project_id,array &$old_bid_files ) : array {
        $ret = [];
        foreach ($this->bid_files as $file) {
            $old_bid_files[] = $file;
            $ret[] = $file->copyToProjectFile($project_id);
            $file->delete();
        }
        return $ret;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $ret = null;
        try {
            if ($field) {
                $ret = $this->where($field, $value)->first();
            } else {
                if (ctype_digit($value)) {
                    $ret = $this->where('id', $value)->first();
                }
            }
            if ($ret) {
                $ret = static::getBuilderForBid(me_id: $ret->id)->first();
            }
        } finally {
            if (empty($ret)) {
                if (request()->ajax()) {
                    throw new ScmPluginBidException("Could not find bid from $field $value");
                } else {
                    abort(404,"Could not find bid from $field $value");
                }
            }
        }
        return $ret;
    }

}
