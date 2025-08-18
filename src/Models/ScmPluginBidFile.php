<?php
namespace Scm\PluginBid\Models;

use App\Models\Enums\TypeOfImageProcessingPolicy;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Traits\IScmFileHandling;
use App\Models\Traits\ScmFileHandling;
use App\Models\User;

use App\Providers\ScmServiceProvider;
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
 * @property string bid_thumbnail_name
 * @property string original_file_name
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
class ScmPluginBidFile extends Model implements IScmFileHandling

{
    use HasFactory,ScmFileHandling;

    protected $table = 'scm_plugin_bid_files';
    public $timestamps = false;

    protected $casts = [
        'bid_file_category' => TypeOfAcceptedFile::class,
    ];


    protected static function booted(): void
    {
        static::deleted(function (ScmPluginBidFile $bid_file) {
            $bid_file->cleanupFileResources();
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
    public static function createBidFile(ScmPluginBidSingle $bid, UploadedFile $file) : ScmPluginBidFile {

        $bid_file = null;
        try {
            $bid_file = new ScmPluginBidFile();
            $bid_file->owning_bid_id = $bid->id;
            $bid_file->uploaded_by_user_id = Auth::id();
            $bid_file->bid_file_category = TypeOfAcceptedFile::UNKNOWN;
            $bid_file->save();
            $bid_file->processUploadedFile(file: $file);
            return $bid_file;

        } catch (\Exception $what) {
            $bid_file?->cleanupFileResources();
            throw $what;
        }
    }



    public function getName() : string {
        return $this->file_bid->getName().": ".$this->bid_file_human_name;
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

        $old_path = $this->getFileRelativePath();
        $project_directory = Project::calcuate_document_directory($project_id);
        Project::create_document_directory($project_id);

        $new_path = $project_directory . DIRECTORY_SEPARATOR . $this->bid_file_name;

        if (Storage::exists($old_path)) {
            Storage::disk()->move($old_path, $new_path);
            Storage::setVisibility($new_path, 'public');
        }


        $project_file = new ProjectFile();
        $project_file->project_id = $project_id;
        $project_file->uploaded_by_user_id = $this->uploaded_by_user_id;
        $project_file->is_secure = ProjectFile::SECURITY_STATUS_IS_NORMAL;


        $project_file->file_extension = $this->bid_file_extension;
        $project_file->file_name = $this->bid_file_name;
        $project_file->thumbnail_name = $this->bid_thumbnail_name;
        $project_file->file_human_name = $this->bid_file_human_name;
        $project_file->file_size_bytes = $remember_disk_space_bytes;
        $project_file->file_mime_type = $this->bid_file_mime_type;
        $project_file->save();
        $project_file->refresh();
        if ($project_file->isImage()) {
            $old_path = $this->getFileRelativePath(b_thumbnail: true,b_use_reg_if_no_thumb: false);
            if ($old_path) {
                $new_path = $project_file->getFileRelativePath(b_thumbnail: true);
                if (Storage::exists($old_path)) {
                    Storage::disk()->move($old_path, $new_path);
                    Storage::setVisibility($new_path, 'public');
                }
            }


        }
        $this->project_file = $project_file;
        return $project_file;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $ret = null;
        try {
            if ($field) {
                $ret = static::getBuilderForBidFile()->where($field, $value);
            } else {
                if (ctype_digit($value)) {
                    $ret = static::getBuilderForBidFile(me_id: $value);
                }
            }
            $ret = $ret?->first();
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

    public static function getImageProcessPolicy() : TypeOfImageProcessingPolicy { return TypeOfImageProcessingPolicy::JOB;}

    public function isImage() : bool {
        return str_contains($this->bid_file_mime_type,'image');
    }

    protected function set_thumb_file_name(?string $file_name) { $this->bid_thumbnail_name = $file_name;}
    protected function get_thumb_file_name(): ?string {return  $this->bid_thumbnail_name; }

    protected function set_file_name(?string $file_name) {$this->bid_file_name = $file_name; }
    protected function get_file_name(): ?string {return $this->bid_file_name;}

    protected function get_file_bytes(): ?int {return $this->bid_file_size_bytes;}


    protected function get_file_created_ts(): ?int {
        $carbon = \Carbon\Carbon::parse($this->created_at,'UTC')->timezone(config('app.timezone'));
        return $carbon->getTimestamp();
    }

    protected function set_file_extension(?string $file_name) {
        $this->bid_file_extension =  static::calculateFileExtension($file_name);
    }
    protected function get_file_extension(): ?string {
        return $this->bid_file_extension;
    }

    protected function get_file_human_name(): ?string {
        return $this->bid_file_human_name;
    }

    protected function get_file_mime_type(): ?string {
        return $this->bid_file_mime_type;
    }

    protected function set_file_human_name(?string $human_name)  {
        $this->bid_file_human_name = $human_name;
    }

    protected static function get_default_image_url(): string {
        return ScmServiceProvider::getMissingFileImage();
    }

    protected function get_file_directory_root(bool $b_thumbnail = false): string {
        $what =  $this->file_bid->get_document_directory();
        if ($b_thumbnail) {
            $what  .= DIRECTORY_SEPARATOR . 'thumbnails';
        }
        return $what;
    }

    protected function fillFileAttributes(
        string $mime_type,string $byte_size,string $human_name,?TypeOfAcceptedFile $file_type = null)
    :void
    {
        $this->bid_file_human_name = $human_name;
        $this->bid_file_size_bytes = $byte_size;
        $this->bid_file_mime_type = $mime_type;
        $this->bid_file_category = $file_type;
    }


    protected function get_ref_uuid(): ?string {
        return null;
    }

    /**
     * override to allow plugin to work in earlier cores, can remove this later
     * @param string $file_name
     * @return string|null
     */
    public static function calculateFileExtension(string $file_name) :?string
    {
        if ( $file_name) {
            $file_parts = pathinfo($file_name);
            if (($file_parts['extension']??null)) {
                return $file_parts['extension'];
            }
        }
        return null;
    }
}
