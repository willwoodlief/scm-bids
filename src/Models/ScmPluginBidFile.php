<?php
namespace Scm\PluginBid\Models;
use App\Helpers\Utilities;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Scm\PluginBid\Exceptions\ScmPluginBidException;
use App\Models\Enums\TypeOfAcceptedFile;


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
 * @property TypeOfAcceptedFile bid_file_category


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

    protected $casts = [
        'bid_file_category' => TypeOfAcceptedFile::class,
    ];


    protected static function booted(): void
    {
        static::deleted(function (ScmPluginBidFile $bid_file) {
            $bid_file->cleanup_resources();
        });
    }

    protected ?ProjectFile $project_file = null;

    public function getProjectFile() : ?ProjectFile { return $this->project_file;}

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
            $file_category = TypeOfAcceptedFile::calculateFileCategory($file);
            if ($file_category === TypeOfAcceptedFile::UNKNOWN) {
                throw new ScmPluginBidException("Unknown file type: ".
                    $file->getMimeType() . " for ". $file->getClientOriginalName());
            }
            $dir = $bid->get_document_directory();
            if ($file_category === TypeOfAcceptedFile::IMAGE) {
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
            $bid_file->bid_file_category = $file_category;
            $bid_file->save();


            return $bid_file->getAbsolutePath();

        } catch (\Exception $what) {
            $bid_file?->cleanup_resources();
            throw $what;
        }
    }



    public function getRelativePath() : ?string {
        if (!$this->bid_file_name) {return null;}
        if ($this->isImage()) {
            return $this->file_bid->get_image_directory(). DIRECTORY_SEPARATOR . $this->bid_file_name;
        } else {
            return $this->file_bid->get_document_directory(). DIRECTORY_SEPARATOR . $this->bid_file_name;
        }

    }

    public function getAbsolutePath() : ?string {
        $relative = $this->getRelativePath();
        if (!$relative) {return null;}
        return realpath(storage_path('app'. DIRECTORY_SEPARATOR .$relative));
    }

    public function get_url() : ?string {
        $relative = $this->getRelativePath();
        if (!$relative) {return null;}
        return asset($relative);
    }

    public function cleanup_resources() {
        if ($this->getRelativePath()) {
            if(Storage::exists($this->getRelativePath())) {
                Storage::delete($this->getRelativePath());
            }
        }

    }

    public function getName() : string {
        return $this->file_bid->getName().": ".$this->bid_file_human_name;
    }

    public function isImage() {
        return $this->bid_file_category === TypeOfAcceptedFile::IMAGE;
    }

    /**
     * Set disk space to 0 before we copy it over so do not hit storage quotas when both exist
     * @param int $project_id
     * @return ProjectFile
     */
    public function copyToProjectFile(int $project_id) : ProjectFile {
        $remember_disk_space_bytes = $this->bid_file_size_bytes;
        $this->bid_file_size_bytes = 0;
        $this->save();

        $old_path = $this->getRelativePath();
        $project_directory = Project::calcuate_document_directory($project_id);
        $abs_project_doc_path = Project::create_document_directory($project_id);
        Utilities::markUnusedVar($abs_project_doc_path);
        $new_path = $project_directory . DIRECTORY_SEPARATOR . $this->bid_file_name;

        Storage::disk()->move($old_path, $new_path);
        chmod($project_directory, 0755);
        Storage::setVisibility($new_path, 'public');


        $project_file = new ProjectFile();
        $project_file->project_id = $project_id;
        $project_file->uploaded_by_user_id = $this->uploaded_by_user_id;
        $project_file->is_secure = ProjectFile::SECURITY_STATUS_IS_NORMAL;


        $project_file->file_extension = $this->bid_file_extension;
        $project_file->file_name = $this->bid_file_name;
        $project_file->file_human_name = $this->bid_file_human_name;
        $project_file->file_size_bytes = $remember_disk_space_bytes;
        $project_file->file_mime_type = $this->bid_file_mime_type;
        $project_file->save();
        $this->project_file = $project_file;
        return $project_file;
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
                $ret = static::getBuilderForBidFile(me_id: $ret->id)->first();
            }
        } finally {
            if (empty($ret)) {
                if (request()->ajax()) {
                    throw new ScmPluginBidException("Could not find bid file from $field $value");
                } else {
                    abort(404,"Could not find bid file from $field $value");
                }
            }
        }
        return $ret;
    }


}
