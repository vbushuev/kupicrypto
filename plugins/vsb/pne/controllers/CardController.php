<?php namespace Vsb\Pnecardregister\Controllers;

use Backend\Classes\Controller;
use Backend;
use Response;
use BackendMenu;
use Redirect;
use Model;
use View;
use Vsb\Pnecardregister\Models\Card;
use Vsb\Pnecardregister\Classes\Pne\Exception as PneException;
use Vsb\Pnecardregister\Classes\Pne\Connector;
use Vsb\Pnecardregister\Classes\Pne\SaleRequest;
use Vsb\Pnecardregister\Classes\Pne\CreateCardRefRequest;
use Vsb\Pnecardregister\Classes\Pne\ReturnRequest;
use Vsb\Pnecardregister\Classes\Pne\CallbackResponse;

class CardController extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    protected $host = "http://kupikriptu.bs2";

    protected $pneConfig = [
        "garan"=>[
            "default" => [
                "url" => "https://sandbox.libill.com/paynet/api/v2/",
                "endpoint" => "204",
                "merchant_key" => "DB3C4FE7-1D1B-4106-8E36-1F5EAC807E34",
                "merchant_login" => "eurolego"
            ],
            "SaleRequest" => [
                "url" => "https://sandbox.ariuspay.ru/paynet/api/v2/",
                "endpoint" => "1144",
                "merchant_key" => "99347351-273F-4D88-84B4-89793AE62D94",
                "merchant_login" => "GARAN24"
            ]
        ],
        "test" => [// testdata
            "default" => [
                "url" => "https://gate.payneteasy.com/paynet/api/v2/",
                "endpoint" => "5990",
                "merchant_key" => "E51B378B-0018-4A7A-B327-C758CC219FE3",
                "merchant_login" => "rentkomplekt"
            ],
            "TransferRequest" => [
                "url" => "https://sandbox.payneteasy.com/paynet/api/v2/",
                "endpoint" => "2593",
                "merchant_key" => "39DF3531-5881-4799-BA24-4415047AB4C4",
                "merchant_login" => "rentkomplekt_test"
            ],
            "SaleRequest" => [
                "url" => "https://sandbox.payneteasy.com/paynet/api/v2/",
                "endpoint" => "2593",
                "merchant_key" => "39DF3531-5881-4799-BA24-4415047AB4C4",
                "merchant_login" => "rentkomplekt_test"
            ],
            "CreateCardRefRequest" => [
                "url" => "https://sandbox.payneteasy.com/paynet/api/v2/",
                "endpoint" => "2593",
                "merchant_key" => "39DF3531-5881-4799-BA24-4415047AB4C4",
                "merchant_login" => "rentkomplekt_test"
            ],
            "ReturnRequest" => [
                "url" => "https://sandbox.payneteasy.com/paynet/api/v2/",
                "endpoint" => "2593",
                "merchant_key" => "39DF3531-5881-4799-BA24-4415047AB4C4",
                "merchant_login" => "rentkomplekt_test"
            ]
        ],
        "prod" => [// testdata
            "TransferRequest" => [
                "url" => "https://gate.payneteasy.com/paynet/api/v2/",
                "endpoint" => "5818",
                "merchant_key" => "E51B378B-0018-4A7A-B327-C758CC219FE3",
                "merchant_login" => "rentkomplekt"
            ]
        ],
    ];
    public $pan = false;
    public $cardref = false;
    protected $pneAmount = 1;
    protected $pneCurrency = "RUB";
    protected $pneEnv = "test";
    protected $pneAction = "SaleRequest";
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Vsb.Pnecardregister', 'card-register-item');
    }
    public function gotrequesttest($context=null){
        $this->cardref = '123';
        $this->pan = '444422******1111';
        $params = [
            'card_ref' => $this->cardref,
            'pan' => $this->pan,
        ];
        Card::create($params);
        return Redirect::to(Backend::url('vsb/pnecardregister/cardcontroller'));
    }
    public function gotrequest($context=null){
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
        return Redirect::to(Backend::url('vsb/pnecardregister/cardcontroller'));
        // return Response::make(View::make($this->viewPath.'/create#cardref='.$res["cardref"]."pan=".$res["pan"]), 200);
        // return Redirect::to(Backend::url('vsb/pnecardregister/cardcontroller/create#cardref='.$res["cardref"]."pan=".$res["pan"]));
        // return json_encode($res,JSON_PRETTY_PRINT);
    }
    public function onGetCardRef($context=null){
        $data = [
            "data"=>[
                "client_orderid" => "-1",
                "order_desc" => "card register ",
                "first_name" => "Perevedi",
                "last_name" => "Online",
                "birthday" => "",
                "address1" => "Marshala Novikova str., 1",
                "address2" => "office 1307",
                "city" => "Moscow",
                "state" => "",
                "zip_code" => "123098",
                "country" => "RU",
                "phone" => "+79265766710",
                "cell_phone" => "+79265766710",
                "amount" => $this->pneAmount,
                "currency" => $this->pneCurrency,
                "email" => "vsb@kupikriptu.com",
                "ipaddress" => "127.0.0.1",
                "site_url" => $this->host,
                // "redirect_url" => $this->host."/backend/vsb/pnecardregister/cardcontroller/create?done=1",
                "redirect_url" => $this->host."/backend/vsb/pnecardregister/cardcontroller/gotrequest",
                //"server_callback_url" =>  $this->host."/callback",
                //"merchant_data" => "VIP customer"
            ]
        ];
        $data = array_merge($this->pneConfig[$this->pneEnv]["default"],$data);
        $action = $this->pneAction;
        $request = new SaleRequest($data);
        $connector = new Connector();
        $connector->setRequest($request);
        $connector->call();
        $response = $connector->getResponse();
        $retval = $response->toArray();
        $res = $retval;
        if(!isset($retval["error-code"])) $this->pneRedirectUrl = isset($retval["redirect-url"])?$retval["redirect-url"]:false;
        return json_encode($response->toArray());
    }
}
/*
$ariuspay = [
    "test" => [// testdata
        "TransferRequest" => [
            "url" => "https://sandbox.payneteasy.com/paynet/api/v2/",
            "endpoint" => "2593",
            "merchant_key" => "39DF3531-5881-4799-BA24-4415047AB4C4",
            "merchant_login" => "rentkomplekt_test"
        ]
    ],
    "prod" => [// testdata
        "TransferRequest" => [
            "url" => "https://gate.payneteasy.com/paynet/api/v2/",
            "endpoint" => "5818",
            "merchant_key" => "E51B378B-0018-4A7A-B327-C758CC219FE3",
            "merchant_login" => "rentkomplekt"
        ]
    ],
];
$pne = Config::payneteasy();
$aquere = $pne["env"];
$operation = $pne["operation"];
$direction = $_POST["direction"];

$this->host = "https://".$_SERVER["SERVER_NAME"];
$rq = Transaction::register([
    "amount"=>$_POST["amount"],
    "currency"=>$_POST["currencyfrom"],
    "client_ip"=>$_SERVER["REMOTE_ADDR"],
    "status"=>"0",
    "fee" => $_POST["fee"]
]);
// $ssn = "1490";//10001100 - 10001492
$saleData = [
    "data"=>[
        "client_orderid" => $rq->id,
        "order_desc" => "&panto=".$panto."&lang=".$lang,
        "first_name" => "Perevedi",
        "last_name" => "Online",
        "birthday" => "",
        "address1" => "Marshala Novikova str., 1",
        "address2" => "office 1307",
        "city" => "Moscow",
        "state" => "",//isset($data["state"])?$data["state"]:"",
        "zip_code" => "123098",
        "country" => "RU",
        "phone" => "+79265766710",
        "cell_phone" => "+79265766710",
        "amount" => $rq->amount,
        "currency" => $rq->currency,
        "email" => "vsb@garan24.ru",
        //"ssn" => $ssn,
        "ipaddress" => $rq->client_ip,
        "site_url" => $this->host,
        "redirect_url" => $this->host."/response",
        "server_callback_url" =>  $this->host."/callback",
        //"merchant_data" => "VIP customer"
    ]
];
if($direction=='crossboard')$saleData["data"]["ssn"] = "10001".rand(100,492);
$saleData = array_merge($pne[$aquere][$direction][$operation],$saleData);
// print_r($saleData);exit;
$request = new \Garan24\Gateway\Ariuspay\PreauthRequest($saleData);
switch($operation){
    case "CaptureRequest":$request = new \Garan24\Gateway\Ariuspay\CaptureRequest($saleData);break;
    case "SaleRequest":$request = new \Garan24\Gateway\Ariuspay\SaleRequest($saleData);break;
    case "TransferRequest":$request = new \Garan24\Gateway\Ariuspay\TransferRequest($saleData);break;
}
$connector = new \Garan24\Gateway\Ariuspay\Connector();
$connector->setRequest($request);
$connector->call();
$resp = $connector->getResponse();
$respArr = $resp->toArray();
$rq->update(["status"=>"1","order_id"=>$respArr["paynet-order-id"]]);
$retval = $resp->toArray();
if(!isset($retval["error-code"]))$res["success"]=true;
$res["redirect"] = isset($retval["redirect-url"])?$retval["redirect-url"]."?panto=".$panto."&lang=".$lang:false;
//$res["redirect"] = "https://perevedi-v2.bs2/form/?panto=".$panto."&lang=".$lang;
//$res["redirect"] = "https://perevedi-v2.bs2/wait/?panto=".$panto."&lang=".$lang;
return $retval;
*/
