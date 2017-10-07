<?php namespace Vsb\Crypto\Controllers;

use Log;
use Request;
use Input;
use Backend\Classes\Controller;
use BackendMenu;

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

    public function __construct(){
        parent::__construct();
        // BackendMenu::setContextOwner('October.Backend');
        // BackendMenu::setContext('Vsb.Pne', 'main-menu-item');
    }
    public function onGetExchange(){
        $url= Setting::get('markets.0.url');
        $url.= 'last_price/'.post('wallet').'/'.post('currency');
        $res = json_decode(file_get_contents($url));
        return $res;
    }

    public function registerCard(){
        $host=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
        $trx = Transaction::create([
            'endpoint'=> Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
            'amount'=>Setting::get('cardregister.0.amount',1),
            'currency'=>Setting::get('cardregister.0.currency','RUB'),
            'type'=>'sale',
            'code'=>'404'
        ]);
        $data = [
            "data"=>[
                "client_orderid" => $trx->id,
                "order_desc" => "card register",
                "first_name" => "Kupi",
                "last_name" => "Crypto",
                "birthday" => "",
                "address1" => "Marshala Novikova str., 1",
                "address2" => "office 1307",
                "city" => "Moscow",
                "state" => "",
                "zip_code" => "123098",
                "country" => "RU",
                "phone" => "+79265766710",
                "cell_phone" => "+79265766710",
                "amount" => Setting::get('cardregister.0.amount',1),
                "currency" => Setting::get('cardregister.0.currency',"RUB"),
                "email" => "reply@kupikriptu.com",
                "ipaddress" => Request::server('REMOTE_ADDR'),
                "site_url" => Request::getBaseUrl(),
                // "redirect_url" => $this->host."/backend/vsb/pnecardregister/cardcontroller/create?done=1",
                "redirect_url" => $host.Setting::get('cardregister.0.response'),
                "server_callback_url" =>  $host.Setting::get('cardregister.0.callback'),
                //"merchant_data" => "VIP customer"
            ]
        ];

        $data = array_merge([
            "url" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.url'),
            "endpoint" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
            "merchant_key" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.key'),
            "merchant_login" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.login')
        ],$data);
        Log::debug($data);
        $request = new SaleRequest($data,Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.version'));
        $connector = new Connector();
        $connector->setRequest($request);
        $connector->call();
        $response = $connector->getResponse();
        $retval = $response->toArray();
        $trx->update(["code"=>(!isset($retval["error-code"]))?"0":$retval["error-code"]]);
        return $retval;
    }
    public function registerCardResponse(){
        $data = file_get_contents('php://input');
        Log::debug($data);
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
                $trx = Transaction::find($obj->client_orderid);
                // $trx = Transaction::find($dataArr['merchant_order']);
                $res['success']=true;
                // $res['amount']=floatval($trx->amount)+floatval($trx->fee);
                $res['pan'] = $dataArr['bin']."******".$dataArr['last-four-digits'];
                // $res['panto'] = $dataArr['dest-bin']."******".$dataArr['dest-last-four-digits'];
                $res['date'] = date('d.m.Y H:i');
                $res['order_id'] = $dataArr['orderid'];
                $trx_return = Transaction::create([
                    'endpoint'=> Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
                    'amount'=>Setting::get('cardregister.0.amount',1),
                    'currency'=>Setting::get('cardregister.0.currency','RUB'),
                    'type'=>'return',
                    'code'=>'404',
                    'parent_id'=>$trx->id
                ]);
                $connector = new Connector();
                $request = new ReturnRequest(array_merge([
                        "url" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.url'),
                        "endpoint" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
                        "merchant_key" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.key'),
                        "merchant_login" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.login')
                    ],["data"=>[
                        "client_orderid"=>$obj->client_orderid,
                        "orderid"=>$obj->orderid,
                        "amount"=>Setting::get('cardregister.0.amount',1),"currency"=>Setting::get('cardregister.0.currency','RUB'),"comment"=>"Checking card only, no sale need"
                    ]]));

                $connector->setRequest($request);
                $connector->call();
                $response = $connector->getResponse();
                $retval = $response->toArray();
                $trx_return->update(["code"=>(!isset($retval["error-code"]))?"0":$retval["error-code"]]);
                $trx_cardref = Transaction::create([
                    'endpoint'=> Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
                    'amount'=>'0',
                    'currency'=>Setting::get('cardregister.0.currency','RUB'),
                    'type'=>'cardref',
                    'code'=>'404',
                    'parent_id'=>$trx->id
                ]);
                $res = array_merge($res,["return"=>$response->toArray()]);
                $request = new CreateCardRefRequest( array_merge([
                        "url" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.url'),
                        "endpoint" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
                        "merchant_key" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.key'),
                        "merchant_login" => Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.login')
                    ],["data"=>[
                        'client_orderid' => $obj->client_orderid,
                        'orderid' => $obj->orderid
                    ]]));
                $connector->setRequest($request);
                $connector->call();
                $response = $connector->getResponse();
                $retval2 = $response->toArray();

                $key = "card-ref-id";
                $res["cardref"] =  $response->$key;
                $crd = Card::create([
                    'card_ref'=>$res["cardref"],
                    'pan' => $res["pan"]
                ]);
                $trx_cardref->update(["card_id"=>$crd->id,"code"=>(!isset($retval["error-code"]))?"0":$retval["error-code"]]);
                $trx_return->update(["card_id"=>$crd->id]);
                $trx->update(["card_id"=>$crd->id]);
            }

        }catch(\Exception $e){
            $res['error'] = isset($dataArr['error_code'])?$dataArr['error_code']:'1005';
            $res['message'] = isset($dataArr['error_message'])?$dataArr['error_message']:'error message';
        }
    }
    public function onAddCard(){
        $retval = $this->registerCard();
        return (!isset($retval["error-code"]))?Redirect::away($retval["redirect-url"]):$retval;
    }
    public function getList(){
        return Card::paginate(20);
    }
    public function removeCard(){
        $cs = post("card_id");
        Card::find($cs)->delete();
    }
    public function updateCard(){
        $cs = post("card_id");
        $card = Card::find($cs);
        $d = Input::all();
        $card->update($d);
        return $card;
    }

}
