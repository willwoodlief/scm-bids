<?php

namespace Scm\PluginBid;

use App\Plugins\PluginRef;
use Illuminate\Support\Facades\Storage;
use Scm\PluginBid\Exceptions\ScmPluginBidException;


//this is a facade anywhere in any code can use ScmPluginTest::logMe

/**
 * This is called from the facade class
 * @see \Scm\PluginBid\Facades\ScmPluginBid
 *
 * The methods here are called as the facade static methods, so ScmPluginBid::getPluginRef()
 *
 */
class ScmPluginBid
{
    /**
     * use a plugin ref to resolve the media path via the getResourceUrl() method
     * @var PluginRef
     */
    protected PluginRef $ref;

    const THIS_PLUGIN_STORAGE_ROOT = 'plugins/scm-plugin-bid';
    const PLUGIN_UPLOAD_DIRECTORY = 'uploads/plugins/scm-plugin-bid';

    public function getPluginStorageRoot(): string { return static::THIS_PLUGIN_STORAGE_ROOT;}
    public function getPluginUploadRoot(): string { return static::PLUGIN_UPLOAD_DIRECTORY;}


    public function unserializeContents(string $path_relative_storage) : mixed {
        $path_relative_storage = trim($path_relative_storage,'/');
        $ser = Storage::disk()->get(static::THIS_PLUGIN_STORAGE_ROOT.DIRECTORY_SEPARATOR.$path_relative_storage);
        $ret = unserialize($ser);
        if (!$ret) {return null;}
        return $ret;
    }

    public function serializeContents(string $path_relative_storage,mixed $content) : void {
        $path_relative_storage = trim($path_relative_storage,'/');
        $ret = Storage::disk()->put(static::THIS_PLUGIN_STORAGE_ROOT.DIRECTORY_SEPARATOR.$path_relative_storage, serialize($content));
        if ($ret === false) {
            throw new ScmPluginBidException("could not write file $path_relative_storage");
        }
    }

    /**
     * This plugin only uses a single instance of this class, and that only uses a single instance of the PluginRef, here we create that
     */
    public function __construct()
    {
        $this->ref = new PluginRef(dirname(__FILE__,2));
    }


    /**
     * Public accessor to get the plugin ref, usage done in the blades mostly
     * @return PluginRef
     */
    public function getPluginRef() : PluginRef {
        return $this->ref;
    }

    /**
     * Useful for forming the full plugin blade name
     * @return string
     */
    public function getBladeRoot() : string {
        return ScmPluginBidProvider::VIEW_BLADE_ROOT;
    }


}
