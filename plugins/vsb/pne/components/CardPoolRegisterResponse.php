<?php namespace Vsb\Pne\Components;

use Lang;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\Transaction;
use Vsb\Pne\Models\Setting;
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\SaleRequest;
use Vsb\Pne\Classes\Pne\CreateCardRefRequest;
use Vsb\Pne\Classes\Pne\ReturnRequest;
use Vsb\Pne\Classes\Pne\CallbackResponse;

class CardPoolRegisterResponse extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => Lang::get('vsb.pne::lang.cardpool.response.title'),
            'description' => Lang::get('vsb.pne::lang.cardpool.response.description')
        ];
    }
    public function onRun(){
        // $params = [
        //     'card_ref' => "72983762938",
        //     'pan' => "testpan****5215",
        // ];
        // Card::create($params);
        $data = file_get_contents('php://input');
        $dataArr = [];
        parse_str($data,$dataArr);
        $res=[];
        $res['response'] = '['.join($dataArr,'],[').']';
        $r = [
            "url" => isset($_SERVER["HTTP_ORIGIN"])?$_SERVER["HTTP_ORIGIN"]:$_SERVER["HTTP_HOST"],
            "data" => $dataArr
        ];
        $redirect_url = "";

        try{
            $obj = new CallbackResponse($r,function($d){});
            if($obj->accept()){
                // $trx = Transaction::find($dataArr['merchant_order']);
                $res['success']=true;
                // $res['amount']=floatval($trx->amount)+floatval($trx->fee);
                $res['pan'] = $dataArr['bin']."******".$dataArr['last-four-digits'];
                // $res['panto'] = $dataArr['dest-bin']."******".$dataArr['dest-last-four-digits'];
                $res['date'] = date('d.m.Y H:i');
                $res['order_id'] = $dataArr['orderid'];
                $connector = new Connector();
                $request = new ReturnRequest(array_merge($this->pneConfig[$this->pneEnv]["default"],["data"=>[
                        "client_orderid"=>$obj->client_orderid,
                        "orderid"=>$obj->orderid,
                        "amount"=>$this->pneAmount,"currency"=>$this->pneCurrency,"comment"=>"Checking card only, no sale need"
                    ]]));

                $connector->setRequest($request);
                $connector->call();
                $response = $connector->getResponse();
                $res = array_merge($res,["return"=>$response->toArray()]);
                $request = new CreateCardRefRequest( array_merge($this->pneConfig[$this->pneEnv]["default"],["data"=>[
                        'client_orderid' => $obj->client_orderid,
                        'orderid' => $obj->orderid
                    ]]));
                $connector->setRequest($request);
                $connector->call();
                $response = $connector->getResponse();
                print_r($response);
                $key = "card-ref-id";
                $res["cardref"] =  $response->$key;
                Card::create([
                    'card_ref'=>$res["cardref"],
                    'pan' => $res["pan"]
                ]);
            }

        }catch(PneException $e){
            $res['error'] = isset($dataArr['error_code'])?$dataArr['error_code']:'1005';
            $res['message'] = isset($dataArr['error_message'])?$dataArr['error_message']:'error message';
        }

        return $res;
        // return Redirect::to('/manager/cardpool');
    }
    // public function defineProperties(){}

}
