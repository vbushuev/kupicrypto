<?php namespace vsb\Crypto\Controllers;

use Log;
use Request;
use Input;
use Backend\Classes\Controller;
use BackendMenu;

use vsb\Crypto\Models\Settings;
use vsb\Crypto\Models\Rate;
use vsb\Crypto\Classes\Transfer;

class CryptoController extends Controller
{
    // public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'];

    // public $listConfig = 'config_list.yaml';
    // public $formConfig = 'config_form.yaml';
    // public $reorderConfig = 'config_reorder.yaml';
    // public $bodyClass = 'compact-container';
    public $requiredPermissions = [
        'manager'
    ];
    protected $_crypto=[];
    public function __construct(){
        parent::__construct();

    }
    public function crypto($curr="RUB"){
        $market_id = 1; // default market_id
        $btc = Rate::where('market_id',$market_id)->where('from','BTC')->where('to',$curr)->first();
        $eth = Rate::where('market_id',$market_id)->where('from','ETH')->where('to',$curr)->first();
        $this->_crypto["btc"]= $btc->price;
        $this->_crypto["eth"] = $eth->price;
        return $this->_crypto;
    }
    public function onGetExchange(){
        $url= Settings::get('markets.0.url');
        $url.= 'last_price/'.post('wallet').'/'.post('currency');
        $res = json_decode(file_get_contents($url));
        return $res;
    }
    public function onSend(){
        $bc = new Transfer();
        $address = post("wallet_number");
        $amount = floatval(post("amount"));
        $bc->send($address,$amount);
    }
    public function CoinbaseNotification(){
        $data = post();
        Log::debug('CoinbaseNotification',$data);
        return Response::json(['ok']);
    }
}
