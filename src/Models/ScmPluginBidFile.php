<?php
namespace Scm\PluginBid\Models;
use App\Helpers\Utilities;
use App\Models\Project;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Scm\PluginBid\Exceptions\ScmPluginBidException;


/**
 *
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @property int id
 * @property int owning_bid_id
 * @property int owning_project_id
 * @property int uploaded_by_user_id
 *
 *
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

    protected static function booted(): void
    {
        static::deleted(function (ScmPluginBidFile $bid_file) {
            $bid_file->cleanup_resources();
        });
    }

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


    /**
     * @throws \Exception
     */
    public static function process_uploaded_file(ScmPluginBidSingle $bid, UploadedFile $file) : string {

        $bid_file = null;
        try {
            Utilities::checkMaxLimitExceeded($file->getSize());

            $is_image = str_contains('image',$file->getMimeType());

            $dir = $bid->get_document_directory();
            if ($is_image) {
                $dir = $bid->get_image_directory();
            }

            $string_or_false = $file->storePublicly($dir,[
                'visibility' => 'public',
                'directory_visibility' => 'public'
            ]);

            if ($string_or_false === false) {
                throw new ScmPluginBidException("Cannot save to $dir");
            }


            $bid_file = new ScmPluginBidFile();
            $bid_file->owning_bid_id = $bid->id;
            $bid_file->uploaded_by_user_id = Auth::id();
            $bid_file->bid_file_extension = $file->getExtension();
            $bid_file->bid_file_name = $file->hashName();
            $bid_file->bid_file_human_name = $file->getClientOriginalName();
            $bid_file->bid_file_size_bytes = $file->getSize();
            $bid_file->bid_file_mime_type = $file->getMimeType();
            $bid_file->bid_file_is_image = $is_image;
            $bid_file->save();

            return $bid_file->getAbsolutePath();

        } catch (\Exception $what) {
            if($bid_file) {unlink($bid_file->getAbsolutePath());}
            throw $what;
        }
    }

    public function getRelativePath() : ?string {
        if (!$this->bid_file_name) {return null;}
        if ($this->bid_file_is_image) {
            return $this->file_bid->get_image_directory(). DIRECTORY_SEPARATOR . $this->bid_file_name;
        } else {
            return $this->file_bid->get_document_directory(). DIRECTORY_SEPARATOR . $this->bid_file_name;
        }

    }

    public function getAbsolutePath() : ?string {
        $relative = $this->getRelativePath();
        if (!$relative) {return null;}
        if ($this->bid_file_is_image) {
            return realpath(app_path('app'. DIRECTORY_SEPARATOR .$relative));
        } else {
            return realpath(storage_path('app'. DIRECTORY_SEPARATOR .$relative));
        }

    }

    public function get_url() : ?string {
        $relative = $this->getRelativePath();
        if (!$relative) {return null;}
        return asset($relative);
    }

    public function cleanup_resources() {
        $absolute_path = $this->getAbsolutePath();
        if (!$absolute_path) {return;}

        if (file_exists($absolute_path)) {
            unlink($absolute_path);
        }
    }

    public function getName() : string {
        return $this->file_bid->getName().": ".$this->bid_file_human_name;
    }


}
