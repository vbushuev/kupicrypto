<?php namespace Vsb\Crypto\Components;

use Log;
use Lang;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Vsb\Crypto\Models\Settings;
use Vsb\Crypto\Controllers\CryptoController;


use Backend\Widgets\Lists;

class Crypto extends ComponentBase{
    public function onRun(){
        $this->onRefresh();
    }
    public function componentDetails()
    {
        return [
            'name'        => Lang::get('vsb.crypto::lang.crypto.title'),
            'description' => Lang::get('vsb.crypto::lang.crypto.description')
        ];
    }

    // public function defineProperties()
    // {
    //     return [];
    // }
    public function onRefresh(){
        $controller = new CryptoController();
        $crypto = $controller->crypto();
        $arr = [];
        foreach($crypto as $cr=>$val){
            $arr[]=[
                'to'=>$cr,
                'from'=>'RUB',
                'value'=>$val,
                'spread'=>[
                    'sell'=>Settings::get('markets.0.sell'),
                    'buy'=> Settings::get('markets.0.buy')
                    ]
            ];
        }
        $this->page['currencies'] = $arr;
    }
    public function onGetExchange(){
        $url= Settings::get('markets.0.url').'last_price/BTC/'.post('currency');
        $type = post("type");
        $cryptocur = strtolower(post("wallet"));
        $spread = Settings::get('markets.0.'.$type);
        $amount = floatval(post("amount"));

        $controller = new CryptoController();
        $crypto = $controller->crypto();
        $val = isset($crypto[$cryptocur])?$crypto[$cryptocur]:0;

        $this->page["value"] = ($val==0)?0: (1-$spread)*(floor(($amount/$val)*10000)/10000) ;
        return [
            "request"=>post(),
            "currencies" =>$crypto
        ];
    }

}
