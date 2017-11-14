<?php namespace vsb\Crypto\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

use vsb\Crypto\Classes\Fetcher;
use vsb\Crypto\Classes\VSB;
use vsb\Crypto\Models\Currency;
use vsb\Crypto\Models\Market;
use vsb\Crypto\Models\Rate;

class MarketContoller extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct(){
        parent::__construct();
        BackendMenu::setContext('vsb.Crypto', 'main-menu-item', 'side-menu-item');
    }
    public function LoadData(){
        $markets = Market::where('enabled','1')->get();
        $currencies = Currency::all();
        // $url= Settings::get('markets.0.url').'last_price/BTC/'.$curr;
        foreach ($markets as $market) {
            // print_r($market);
            switch($market->name){
                case "cex.io":{
                    $url= $market->url.'last_price/BTC/RUB';
                    $res = json_decode(Fetcher::get($url));
                    $btc = (is_object($res)&&!isset($res->error))?floatval($res->lprice):0;
                    $rate = Rate::where('from','BTC')->where('to','RUB')->where('market_id',$market->id)->first();
                    if(is_null($rate)){
                        Rate::create([
                            "market_id"=>$market->id,
                            "from"=>"BTC",
                            "to"=>"RUB",
                            "price"=>$btc,
                            "volate"=>"0"
                        ]);
                    }else if($rate->price!=$btc){
                        $rate->update([
                            "price"=>$btc,
                            "volate"=>VSB::CompareTreeState($rate->price,$btc)
                        ]);
                    }

                    $url= $market->url.'last_price/ETH/BTC';
                    $res = json_decode(Fetcher::get($url));
                    $eth = (is_object($res)&&!isset($res->error))?floatval($res->lprice)*$btc:0;

                    $rate = Rate::where('from','ETH')->where('to','RUB')->where('market_id',$market->id)->first();
                    if(is_null($rate)){
                        Rate::create([
                            "market_id"=>$market->id,
                            "from"=>"ETH",
                            "to"=>"RUB",
                            "price"=>$eth,
                            "volate"=>"0"
                        ]);
                    }else if($rate->price!=$eth){
                        $rate->update([
                            "price"=>$eth,
                            "volate"=>VSB::CompareTreeState($rate->price,$eth)
                        ]);
                    }

                }break;
                case "cryptonator":{
                    foreach ($currencies as $currency) {
                        $url= $market->url.'ticker/'.strtolower($currency->code).'-rub';
                        $res = json_decode(Fetcher::get($url));
                        if(!isset($res->ticker) || !isset($res->ticker->price)){
                            print_r($res);
                            continue;
                        }
                        $val = $res->ticker->price;
                        $rate = Rate::where('from',$currency->code)->where('to','RUB')->where('market_id',$market->id)->first();
                        if(is_null($rate)){
                            Rate::create([
                                "market_id"=>$market->id,
                                "from"=>$currency->code,
                                "to"=>"RUB",
                                "price"=>$val,
                                "volate"=>"0"
                            ]);
                        }else if($rate->price!=$val){
                            $rate->update(["price"=>$val,"volate"=>VSB::CompareTreeState($rate->price,$val)]);
                        }
                    }
                }break;
                case "coinbase":{
                    foreach ($currencies as $currency) {
                        $url= $market->url.'exchange-rates?currency='.strtolower($currency->code);
                        $res = json_decode(Fetcher::get($url));
                        if(!isset($res->data) || !isset($res->data->rates)){
                            print_r($res);
                            continue;
                        }
                        $val = $res->data->rates->RUB;
                        $rate = Rate::where('from',$currency->code)->where('to','RUB')->where('market_id',$market->id)->first();
                        if(is_null($rate)){
                            Rate::create([
                                "market_id"=>$market->id,
                                "from"=>$currency->code,
                                "to"=>"RUB",
                                "price"=>$val,
                                "volate"=>"0"
                            ]);
                        }else if($rate->price!=$val){
                            $rate->update(["price"=>$val,"volate"=>VSB::CompareTreeState($rate->price,$val)]);
                        }
                    }
                }
            }
        }
    }
}
