<?php namespace Vsb\Pne\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Request;
use Log;
use Lang;

use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\Transaction;
use Vsb\Pne\Models\Setting;
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\TransferRequest;
use Vsb\Pne\Classes\Pne\ReturnRequest;
use Vsb\Pne\Classes\Pne\CallbackResponse;

use Vsb\Pne\Controllers\CardPoolController;

class TransactionController extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\ReorderController'];

    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [

    ];

    public function __construct(){
        parent::__construct();
        BackendMenu::setContext('Vsb.Pne', 'main-menu-item', 'side-menu-transactions');
    }
    public function transferRequest(){
        $amount = post('amount');
        $currency = post('currency');
        $cardpool = new CardPoolController();
        $card = $cardpool->getCardFromPool();
        Log::debug("Found card from cardpool: ".$card->__toString());
        if($card==false || is_null($card))return ; //need Exception maybe
        $description = http_build_query(post());
        $host=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
        $trx = Transaction::create([
            'endpoint'=> Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.endpoint'),
            'amount'=>$amount,
            'currency'=>$currency,
            'type'=>'transfer',
            'code'=>'404',
            'card_id'=>$card->id,
            'description' => $description
        ]);
        $data = [
            "data"=>[
                "client_orderid" => $trx->id,
                "order_desc" => $description,
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
                "amount" => $trx->amount,
                "currency" => $trx->currency,
                "email" => "reply@kupikriptu.com",
                "ipaddress" => Request::server('REMOTE_ADDR'),
                "site_url" => $host,
                // "redirect_url" => $this->host."/backend/vsb/pnecardregister/cardcontroller/create?done=1",
                "redirect_url" => $host.Setting::get('transfer.0.response'),
                "server_callback_url" =>  $host.Setting::get('transfer.0.callback'),
                //"merchant_data" => "VIP customer"
                "cardref" => $card->card_ref

            ]
        ];

        $data = array_merge([
            "url" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.url'),
            "endpoint" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.endpoint'),
            "merchant_key" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.key'),
            "merchant_login" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.login')
        ],$data);
        // Log::debug($data);
        $request = new TransferRequest($data,Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.version'));
        $connector = new Connector();
        $connector->setRequest($request);
        $connector->call();
        $response = $connector->getResponse();
        $retval = $response->toArray();
        return $retval;
    }
    public function transferResponse(){
        $data = file_get_contents('php://input');
        $res=[];
        $r = [
            "url" => isset($_SERVER["HTTP_ORIGIN"])?$_SERVER["HTTP_ORIGIN"]:$_SERVER["HTTP_HOST"],
            "data" => $data
        ];
        $redirect_url = "";
        $dataArr = [];
        try{
            // $obj = new CallbackResponse($data,function($d){},Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.version'));
            $obj = new CallbackResponse($r,function($d){});
            $dataArr=$obj->toArray();
            $trx = Transaction::find($obj->client_orderid);
            $res["trx"] = $trx;
            if($obj->accept()){
                $card = Card::find($trx->card_id);
                $trx->update(["code"=>"0"]);
                $card->daily_limit-=$trx->amount;
                $card->monthly_limit-=$trx->amount;
                $card->save();
            }else{
                $trx->update(["code"=>$dataArr["error-code"]]);
            }

        }catch(\Exception $e){
            $res["error"]="500";
            $res["message"] = $e->getMessage();
            // $res['error'] = isset($dataArr['error_code'])?$dataArr['error_code']:'1005';
            // $res['message'] = isset($dataArr['error_message'])?$dataArr['error_message']:'error message';
        }
        $res['response'] = $data;
        Log::debug("transferResponse:",$res);
        return $res;
    }
    public function reversalRequest($trx){
        $trx_return = Transaction::create([
            'endpoint'=> Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.endpoint'),
            'amount'=>$trx->amount,
            'currency'=>$trx->currency,
            'type'=>'return',
            'code'=>'404',
            'parent_id'=>$trx->id,
            "card_id"=>$trx->card_id
        ]);
        $connector = new Connector();
        $request = new ReturnRequest(array_merge([
                "url" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.url'),
                "endpoint" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.endpoint'),
                "merchant_key" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.key'),
                "merchant_login" => Setting::get('endpoint.'.Setting::get('transfer.0.current_endpoint').'.login')
            ],["data"=>[
                "client_orderid"=>$trx->id,
                "orderid"=>$trx->orderid,
                'amount'=>$trx->amount,
                'currency'=>$trx->currency,
                "comment"=>"Return failed operation"
            ]]));
        // Log::debug("ReturnRequest:",$request);
        $connector->setRequest($request);
        $connector->call();
        $response = $connector->getResponse();
        $retval = $response->toArray();
        $trx_return->update(["code"=>(!isset($retval["error-code"]))?"200":$retval["error-code"]]);
    }
}
/*
[05/10/2017, 11:57:40] Alexey Oleynyak: {"sender":{"address":{"city":"Moscow","country":"RUS","postcodeZip":"123123","street":"Red sq, 1"},"firstName":"John","lastName":"Smith","ipAddress":"127.0.0.1"},"destinationOfFunds":{"reference":{"cardReferenceId":"135"}},"order":{"description":"","siteUrl":""},"urls":{"redirectUrl":"http://kupikriptu.bs2/pne/transfer/response","callbackUrl":"http://kupikriptu.bs2/pne/callback"},"transaction":{"amountCentis":"12000","currency":"RUB"}}
[05/10/2017, 11:57:58] Alexey Oleynyak: то подпись должна быть следующей: 73a6fafabba57b692d890bfc3b31c01a74d54bf5
[05/10/2017, 11:58:15] Alexey Oleynyak: hash_hmac( "sha1", $str, hex2bin( "E51B378B00184A7AB327C758CC219FE3" ) )
*/
