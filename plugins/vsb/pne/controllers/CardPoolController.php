<?php namespace Vsb\Pne\Controllers;

use Log;
use Request;
use Input;
use Backend\Classes\Controller;
use BackendMenu;
use BackendAuth;
use Auth;
use RainLab\User\Models\UserGroup;
use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\UserProject;
use Vsb\Pne\Models\Project;
use Vsb\Pne\Models\Transaction;
use Vsb\Pne\Models\Setting;
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\PreauthRequest;
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
        $user = Auth::getUser();
        $host=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
        $project_id = post('project_id',0);
        $crd = Card::create([
            'card_ref'=>'',
            'pan' => '',
            "project_id" =>$project_id,
            'enabled' => '0',
            'user_id' => $user->id
        ]);
        $trx = Transaction::create([
            'endpoint'=> Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
            'amount'=>Setting::get('cardregister.0.amount',1),
            'currency'=>Setting::get('cardregister.0.currency','RUB'),
            'type'=>'preauth',
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
        // $request = new SaleRequest($data,Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.version'));
        $request = new PreauthRequest($data,Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.version'));
        $connector = new Connector();
        $connector->setRequest($request);
        $connector->call();
        $response = $connector->getResponse();
        $retval = $response->toArray();
        $trx->update(["code"=>(!isset($retval["error-code"]))?"200":$retval["error-code"]]);
        return $retval;
    }
    public function registerCardResponse($data=false,$project_id="1"){
        $data = ($data===false)?file_get_contents('php://input'):$data;
        // $dataArr = [];
        // parse_str($data,$dataArr);
        $res=[];
        $res['response'] = $data;
        $r = [
            "url" => isset($_SERVER["HTTP_ORIGIN"])?$_SERVER["HTTP_ORIGIN"]:$_SERVER["HTTP_HOST"],
            // "data" => $dataArr
            "data" => $data
        ];
        Log::debug("RAW response: ".$data);
        $redirect_url = "";
        try{
            $obj = new CallbackResponse($r,function($d){},Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.version'));
            $dataArr=$obj->toArray();
            if($obj->accept()){
                $trx = Transaction::find($obj->client_orderid);
                // $trx = Transaction::find($dataArr['merchant_order']);
                $res['success']=true;
                // $res['amount']=floatval($trx->amount)+floatval($trx->fee);
                $res['pan'] = $dataArr['bin']."******".$dataArr['last-four-digits'];
                // $res['panto'] = $dataArr['dest-bin']."******".$dataArr['dest-last-four-digits'];
                $res['date'] = date('d.m.Y H:i');
                $res['order_id'] = $obj->orderid;
                $res['client_orderid'] = $obj->client_orderid;
                $trx_return = Transaction::create([
                    'endpoint'=> Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
                    'amount'=>Setting::get('cardregister.0.amount',1),
                    'currency'=>Setting::get('cardregister.0.currency','RUB'),
                    'type'=>'return',
                    'code'=>'404',
                    'parent_id'=>$trx->id,
                    "card_id"=>$trx->card_id
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
                // Log::debug("ReturnRequest:",$request);
                $connector->setRequest($request);
                $connector->call();
                $response = $connector->getResponse();
                $retval = $response->toArray();
                $trx_return->update(["code"=>(!isset($retval["error-code"]))?"200":$retval["error-code"]]);
                $trx_cardref = Transaction::create([
                    'endpoint'=> Setting::get('endpoint.'.Setting::get('cardregister.0.current_endpoint').'.endpoint'),
                    'amount'=>'0',
                    'currency'=>Setting::get('cardregister.0.currency','RUB'),
                    'type'=>'cardref',
                    'code'=>'404',
                    'parent_id'=>$trx->id,
                    "card_id"=>$trx->card_id
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
                // Log::debug("CreateCardRefRequest:",$request);
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
            // Log::error($e);
            $res['error'] = isset($dataArr['error_code'])?$dataArr['error_code']:$e->getCode();
            $res['message'] = isset($dataArr['error_message'])?$dataArr['error_message']:$e->getMessage();
        }
        Log::debug("res:",$res);
        return $res;
    }
    public function onAddCard(){
        $retval = $this->registerCard();
        return (!isset($retval["error-code"]))?Redirect::away($retval["redirect-url"]):$retval;
    }
    public function getProjectList(){
        $user = Auth::getUser();
        $prs = ($this->checkSuperUser($user))
            ?Project::all()
            :Project::whereIn('id',UserProject::where('user_id','=',$user->id)->lists('project_id'))->get();
        return $prs;
    }
    public function getList(){
        $user = Auth::getUser();
        $res=Card::with(['project','user'])->whereNull('deleted_at');
        if(!$this->checkSuperUser($user)){
            $projects = UserProject::where('user_id','=',$user->id)->lists('project_id');
            $res = $res->whereIn('project_id',$projects)->orWhere(function($query)use($user){
                $query->where('user_id',$user->id)->whereNull('deleted_at');
            });
        }
        $project_id =post("project_id","false");
        if($project_id!=="false")$res=$res->where("project_id","=",post("project_id"));
        Log::debug("cards:".$res->toSql());
        return $res->orderBy('id','desc')->get();
    }
    public function removeCard(){
        $cs = post("card_id");
        $card = Card::find($cs);
        if(!is_null($card))$card->delete();
    }
    public function updateCard(){
        $cs = post("card_id");
        $card = Card::find($cs);
        $d = Input::all();
        if(isset($d['enabled'])) $d['enabled']= ( $d['enabled']=="On" || $d['enabled']=="1")? "1":"0";
        if(count($d)&&!is_null($card))$card->update($d);
        return $card;
    }
    public function checkSuperUser($user=false){
        if($user===false)$user = Auth::getUser();
        $ug = UserGroup::with(['users'])
            // ->where('code','superusers')
            ->where('code',Setting::get('cardregister.0.supergroup'))
            ->first();
        if(!is_null($ug)){
            foreach($ug->users as $u){
                if($user->id == $u->id)return true;
            }
        }
        return false;
    }
}
