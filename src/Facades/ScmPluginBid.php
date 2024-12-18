<?php

namespace Scm\PluginBid\Facades;

use App\Plugins\PluginRef;
use Illuminate\Support\Facades\Facade;


/**
 * Plugins do not need any facades, but given this is laravel, this these are convenient to lump some logic together that is needed by the plugin
 *
 * This class is found by the laravel framework  via the composer.json extra field "aliases"
 *
 * All this class does is hook up the Scm\PluginBid\ScmPluginBid class to the framework
 *
 * @uses \Scm\PluginBid\ScmPluginBid::getPluginRef()
 * @uses \Scm\PluginBid\ScmPluginBid::getBladeRoot()
 * @uses \Scm\PluginBid\ScmPluginBid::unserializeContents()
 * @uses \Scm\PluginBid\ScmPluginBid::serializeContents()
 * @method static PluginRef getPluginRef()
 * @method static string getBladeRoot()
 * @method static mixed unserializeContents(string $path_relative_storage)
 * @method static void serializeContents(string $path_relative_storage,mixed $content)
 */
class ScmPluginBid extends Facade
{
    /**
     * This laravel function does the hooking up of our class with the name of this Facade
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Scm\PluginBid\ScmPluginBid::class;
    }
}
