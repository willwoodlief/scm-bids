<?php
namespace Scm\PluginBid\Helpers;

use Scm\PluginBid\Models\ScmPluginBidStat;

class OverallStats
{
    public static function getTotalBids()
    :int
    {
        return ScmPluginBidStat::getBuilderForBidStat()->count();
    }

    public static function getTotalSuccess()
    :int
    {
        return ScmPluginBidStat::getBuilderForBidStat(b_only_success: true)->count();
    }

    public static function getTotalSuccessBudget()
    :int
    {
        return ScmPluginBidStat::getBuilderForBidStat(b_only_success: true)->sum('budget');
    }

    public static function getTotalFailed()
    :int
    {
        return ScmPluginBidStat::getBuilderForBidStat(b_only_fail: true)->count();
    }
}
