<?php namespace Vsb\Pne\Controllers;

use Log;
use Request;
use Input;
use Backend\Classes\Controller;
use BackendMenu;
use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\Transaction;
use Vsb\Pne\Models\Setting;
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\SaleRequest;
use Vsb\Pne\Classes\Pne\CreateCardRefRequest;
use Vsb\Pne\Classes\Pne\ReturnRequest;
use Vsb\Pne\Classes\Pne\CallbackResponse;

class CardPoolController extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $bodyClass = 'compact-container';
    public $requiredPermissions = [
        'manager'
    ];

    public function __construct(){
        parent::__construct();
        BackendMenu::setContextOwner('October.Backend');
        BackendMenu::setContext('Vsb.Pne', 'main-menu-item');
    }
    public function getCardFromPool(){
        $amount = post('amount',1);
        $project = post("project_id",1);
        $card = Card::where('enabled','=','1')
            ->whereNull('deleted_at')
            ->where('daily_limit','>=',$amount)
            ->where('monthly_limit','>=',$amount)
            ->where('project_id','=',$project)
            ->orderBy('monthly_limit','desc')->orderBy('daily_limit','desc');
        Log::debug($card->toSql());
        return $card->first();
    }
    public function registerCard(){
        $host=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
        $project_id = post('project_id',0);
        $crd = Card::create([
            'card_ref'=>'',
            'pan' => '',
            "project_id" =>$project_id,
            'enabled' => '0'
        ]);
        $trx = Transaction::create([
            'endpoint'=> Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
            'amount'=>Setting::get('cardregister.0.amount',1),
            'currency'=>Setting::get('cardregister.0.currency','RUB'),
            'type'=>'sale',
            'code'=>'404',
            'card_id' => $crd->id
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
    public function registerCardResponse($data=false,$project_id="1"){
        $data = ($data===false)?file_get_contents('php://input'):$data;
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
                    'parent_id'=>$trx->id,
                    "card_id"=>$crd->id
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
                    'parent_id'=>$trx->id,
                    "card_id"=>$crd->id
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
                $crd = Card::find($trx->card_id);
                $crd->update(['card_ref'=>$res["cardref"],'pan' => $res["pan"],'enabled' => '1']);
                $trx_cardref->update(["code"=>(!isset($retval["error-code"]))?"0":$retval["error-code"]]);
                // $trx_return->update([]);
                // $trx->update(["card_id"=>$crd->id]);
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
        $res = Card::with(['project']);
        if(post("project_id",false)!==false)$res=$res->where("project_id","=",post("project_id"));
        return $res->get();
    }
    public function removeCard(){
        $cs = post("card_id");
        Card::find($cs)->delete();
    }
    public function updateCard(){
        $cs = post("card_id");
        $card = Card::find($cs);
        $d = Input::all();
        if(isset($d['enabled'])) $d['enabled']= ( $d['enabled']=="On" )? "1":"0";
        if(count($d)&&!is_null($card))$card->update($d);
        return $card;
    }

}
