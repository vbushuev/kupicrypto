<?php namespace vsb\Crypto\Classes;
class VSB{
    public static function CompareTreeState($v1,$v2){
        $ret = 0;
        if( floatval($v1) > floatval($v2) ) return -1;
        if( floatval($v1) < floatval($v2) ) return 1;
        return 0;
    }
};
?>
