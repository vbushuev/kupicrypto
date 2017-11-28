<?php namespace Vsb\Crypto\Components;

use Log;
use Lang;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Vsb\Crypto\Models\Settings;
use Vsb\Crypto\Models\Rate;
use Vsb\Crypto\Models\Market;
use Vsb\Crypto\Controllers\CryptoController;

use Vsb\Crypto\Classes\Coinbase;

use Backend\Widgets\Lists;

class Crypto extends ComponentBase{
    public function onRun(){
        $this->onRefresh();
        $this->page['markets'] = Market::get();
        $this->page['rates'] = Rate::with(['market'])->get();
        $this->page['accounts'] = [];
        $this->page['balance'] = [];
        $this->page['wallet_trxs'] = [];
        try{
            $t = new Coinbase(Settings::get('markets.0.wallet_api'),Settings::get('markets.0.wallet_secret'));
            $accounts = $t->accounts();
            $this->page['accounts'] = $accounts;
            $bals=[];
            foreach($accounts as $acc ) $bals[strtolower($acc['currency'])]=$acc["balance"];
            $this->page['balance'] = $bals;
            $this->page['wallet_trxs'] = $t->transactions();
        }
        catch(\Exception $e){
            Log::debug($e);
        }
        $this->page['currency'] = [
            'btc'=>Rate::where('from','BTC')->where('isdefault','1')->first()->price,
            'eth'=>Rate::where('from','ETH')->where('isdefault','1')->first()->price
        ];

    }
    public function onMakeRequest(){
        $this->page["account"]="";
        $this->page["request"]=[];
        try{
            $t = new Coinbase(Settings::get('markets.0.wallet_api'),Settings::get('markets.0.wallet_secret'));
            $this->page["account"] = $t->account(post('account_id'));
            // $this->page["request"] = '';
            $this->page["request"] = $t->request(post('account_id'),post('amount'));
        }
        catch(\Exception $e){
            Log::debug($e);
        }
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
        $this->page['spread'] = [
            'sell'=>Settings::get('markets.0.sell'),
            'buy'=> Settings::get('markets.0.buy')
            ];
        $this->page['rates'] = Rate::with(['market'])->get();
    }
    public function onSetDefault(){
        $id = Rate::find(post('item_id'));
        $val = intval(post('default'));
        if($val==1) {
            Rate::where('from',$id->from)->update(['isdefault'=>0]);
            $id->update(['isdefault'=>1]);
        }
        else if ($id->isdefault == 1 ){
            $id->update(['isdefault'=>0]);
            Rate::where('from',$id->from)->where('id','<>',$id->id)->first()->update(['isdefault'=>1]);
        }
        $this->page['spread'] = [
            'sell'=>Settings::get('markets.0.sell'),
            'buy'=> Settings::get('markets.0.buy')
            ];
        $this->page['rates'] = Rate::with(['market'])->get();
        return $id;
    }
    public function onFund(){
        /*
        API Key: gmWkAaXVi1ImmBDu
        API Secret: 2boLOndVO6ccmjleAozDaIZrYZXOu8V3
        */
        $address = post('wallet_number');
        $currency = post('wallet');
        $amount = post('amount');
        $accountTo=null;
        try{
            $t = new Coinbase(Settings::get('markets.0.wallet_api'),Settings::get('markets.0.wallet_secret'));
            $res = $t->fund($address,$amount,$currency);
            print_r($res);
        }
        catch(\Exception $e){Log::debug($e);}

        // foreach($t->accounts() as $account){
        //     if($account->getType()==$wallet){$accountTo = $account;break;}
        // }
        // if(!is_null($accountTo)){
        //
        // }
    }
    public function onSend(){
        $controller = new CryptoController();
        $controller->onSend();
    }
    public function onGetExchange(){
        // $url= Settings::get('markets.0.url').'last_price/BTC/'.post('currency');
        $type = post("type");
        $cryptocur = strtolower(post("wallet"));
        $spread = Settings::get('markets.0.'.$type);
        $amount = floatval(post("amount"));
        $wbal=true;
        try{
            $t = new Coinbase(Settings::get('markets.0.wallet_api'),Settings::get('markets.0.wallet_secret'));
            $wbal = $t->checkBalance($this->page["value"],strtoupper(post("wallet")));
        }
        catch(\Exception $e){Log::debug($e);}

        $rate = Rate::where('from',$cryptocur)->where('to',post('currency'))->where('isdefault','1')->first();
        $val = isset($rate->price)?$rate->price:0;

        $this->page["value"] = ($val==0)?0: (1-$spread)*(floor(($amount/$val)*10000)/10000) ;
        return [
            "request"=>post(),
            "currencies" =>$rate,
            "cansale" => $wbal
        ];
    }
    public function onSpreadEdit(){
        $this->onRefresh();
    }
    public function onSpreadSave(){
        Settings::set([
            'markets.0.sell'=>post('sell',Settings::get('markets.0.sell')),
            'markets.0.buy'=>post('buy',Settings::get('markets.0.buy'))
        ]);
        $this->onRefresh();
    }
    public function onWallet(){
        $this->page['wallet'] = [
            "api"=>Settings::get('markets.0.wallet_api'),
            "secret"=>Settings::get('markets.0.wallet_secret')
        ];
    }
    public function onWalletEdit(){
        Settings::set([
            'markets.0.wallet_api'=>post('api',Settings::get('markets.0.wallet_api')),
            'markets.0.wallet_secret'=>post('secret',Settings::get('markets.0.wallet_secret'))
        ]);
        $this->onRun();
    }
}
