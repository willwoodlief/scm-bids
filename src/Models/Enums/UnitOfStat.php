<?php
namespace Scm\PluginBid\Models\Enums;


enum UnitOfStat : string {


    case DAY = 'day';
    case MONTH = 'month';

    public static function tryFromInput(string|int|bool|null $test ) : UnitOfStat {
        $maybe  = UnitOfStat::tryFrom($test);
        if (!$maybe ) {
            $delimited_values = implode('|',array_column(UnitOfStat::cases(),'value'));
            throw new \InvalidArgumentException(sprintf("Invalid enum for UnitOfStat. Got %s but need %s",$test,$delimited_values));
        }
        return $maybe;
    }

}


