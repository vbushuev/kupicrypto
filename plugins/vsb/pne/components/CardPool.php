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
use Vsb\Pne\Controllers\CardPoolController;


use Backend\Widgets\Lists;

class CardPool extends ComponentBase
{
    public function onRun(){
        $this->page['title'] = Lang::get('vsb.pne::lang.cardpool.title');
        $listRender = new CardPoolController();
        $listRender->makeLists();
        $this->page['list'] = $listRender->listRender();
    }
    public function componentDetails()
    {
        return [
            'name'        => Lang::get('vsb.pne::lang.cardpool.title'),
            'description' => Lang::get('vsb.pne::lang.cardpool.description')
        ];
    }

    // public function defineProperties()
    // {
    //     return [];
    // }

    public function getCards(){
        return Card::orderBy('created_at','desc')->get();
    }
    public function onAddCard(){
        $host=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
        $data = [
            "data"=>[
                "client_orderid" => "-1",
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
            "url" => Setting::get('endpoint.'.Setting::get('current_endpoint').'.url'),
            "endpoint" => Setting::get('endpoint.'.Setting::get('current_endpoint').'.endpoint'),
            "merchant_key" => Setting::get('endpoint.'.Setting::get('current_endpoint').'.key'),
            "merchant_login" => Setting::get('endpoint.'.Setting::get('current_endpoint').'.login')
        ],$data);

        $request = new SaleRequest($data);
        $connector = new Connector();
        $connector->setRequest($request);
        $connector->call();
        $response = $connector->getResponse();
        $retval = $response->toArray();
        // $res = $retval;
        $this->pneRedirectUrl = isset($retval["redirect-url"])?$retval["redirect-url"]:false;
        return (!isset($retval["error-code"]))?Redirect::away($retval["redirect-url"]):$retval;
        // return json_encode($response->toArray());
    }
    // public function onAddItem()
    // {
    //     $items = post('items', []);
    //
    //     if (count($items) >= $this->property('max')) {
    //         throw new ApplicationException(sprintf('Sorry only %s items are allowed.', $this->property('max')));
    //     }
    //
    //     if (($newItem = post('newItem')) != '') {
    //         $items[] = $newItem;
    //     }
    //
    //     $this->page['items'] = $items;
    // }
}
