<?php namespace Vsb\Crypto\Components;

use Log;
use Lang;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Vsb\Crypto\Models\Settings;


use Backend\Widgets\Lists;

class Crypto extends ComponentBase{
    public function onRun(){
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

    public function onGetExchange(){
        $url= Settings::get('markets.0.url').'last_price/BTC/'.post('currency');
        $type = post("type");
        $spread = Settings::get('markets.0.'.$type);
        $amount = floatval(post("amount"));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_REFERER, $this->request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        $res = json_decode($result);
        $btc = (!isset($res->error))?($amount)/floatval($res->lprice):0;
        curl_setopt($ch, CURLOPT_URL,  Settings::get('markets.0.url').'last_price/ETH/BTC');
        $result = curl_exec($ch);
        $res = json_decode($result);
        $eth = (!isset($res->error))?($btc)/floatval($res->lprice):0;
        curl_close($ch);
        $this->page["btc"] = (1-$spread)*(floor($btc*10000)/10000);
        $this->page["eth"] = (1-$spread)*(floor($eth*10000)/10000);
        return [
            // '#cryptoRates' => $this->renderPartial('_rates.htm'),
            "url" => $url,
            "result" => $result,
            "amount" => $amount,
            "btc" =>$btc,
            "eth" =>$eth,
        ];
    }

}
