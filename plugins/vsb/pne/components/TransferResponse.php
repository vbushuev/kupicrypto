<?php namespace Vsb\Pne\Components;

use Log;
use Lang;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\Transaction;
use Vsb\Pne\Models\Setting;
use Vsb\Crypto\Models\Settings;
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\SaleRequest;
use Vsb\Pne\Classes\Pne\CreateCardRefRequest;
use Vsb\Pne\Classes\Pne\ReturnRequest;
use Vsb\Pne\Classes\Pne\CallbackResponse;
use Vsb\Pne\Controllers\TransactionController;

use Vsb\Crypto\Classes\Coinbase;

class TransferResponse extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => Lang::get('vsb.pne::lang.transfer.response.title'),
            'description' => Lang::get('vsb.pne::lang.transfer.response.description')
        ];
    }
    public function onRun(){
        $this->addCss('/plugins/vsb/pne/assets/css/transfer.css');
        $this->addJs('/plugins/vsb/pne/assets/js/transfer.js');
        try{
            $cpc = new TransactionController();
            $res = $cpc->transferResponse();

            $trx = $res["trx"];
            if(!isset($res["error"])){
                $req = [];
                parse_str($trx->description,$req);
                $t = new Coinbase(Settings::get('markets.0.wallet_api','gmWkAaXVi1ImmBDu'),Settings::get('markets.0.wallet_secret','2boLOndVO6ccmjleAozDaIZrYZXOu8V3'));
                $tr = $t->fund($req["wallet_number"],$trx->amount,$trx->currency);
                if(isset($tr->status) && $tr->status == "complite"){
                    //success
                }
                else {
                    $res["error"] = "-1";
                    $res["message"] = "Coins send response: ".json_encode($tr);
                    // $cpc->reversalRequest($trx);
                }
            }
            $this->page["success"]=!isset($res["error"]);
            $this->page["error"]=isset($res["error"])?$res["error"]:"0";
            $this->page["message"]=isset($res["message"])?$res["message"]:"0";
            $trx = (isset($res["trx"]))?$res["trx"]:false;
            if($trx!==false){
                $this->page["amount"]=$trx->amount;
                $this->page["currency"]=$trx->currency;
                $this->page["description"]=$trx->description;
            }
        }
        catch(\Exception $e){
            $this->page["success"]=false;
            $this->page["error"]=$e->getCode();
            $this->page["message"]=$e->getMessage();
        }
    }
}
