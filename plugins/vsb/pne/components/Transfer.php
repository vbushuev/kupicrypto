<?php namespace Vsb\Pne\Components;

use Log;
use Lang;
use Request;
use Redirect;
use Response;
use Validator;
use ValidationException;
use Cms\Classes\ComponentBase;
use ApplicationException;

use Vsb\Pne\Controllers\CardPoolController;
use Vsb\Pne\Controllers\TransactionController;
use Vsb\Pne\Models\Setting;


use Backend\Widgets\Lists;

class Transfer extends ComponentBase{
    public function onRun(){
        $this->addCss('/plugins/vsb/pne/assets/css/transfer.css');
        $this->addJs('/plugins/vsb/pne/assets/js/transfer.js');
        $this->page['title'] = Lang::get('vsb.pne::lang.transfer.title');
        // $this->controller = new CardPoolController();
        // $this->controller->makeLists();
        // $this->page['list'] = $this->controller->listRender();
        // $this->page['contoller'] = $this->controller;
        $this->page['project_id'] = Setting::get('project_id');
    }
    public function componentDetails()
    {
        return [
            'name'        => Lang::get('vsb.pne::lang.transfer.title'),
            'description' => Lang::get('vsb.pne::lang.transfer.description')
        ];
    }
    public function onTransfer(){
        $rules = [
            'amount' => 'required|numeric|max:'.Setting::get('cardregister.0.maxDaily'),
            'make' => 'required|accepted',
            'wallet_number' => 'required',
        ];
        $validation = Validator::make(post(), $rules, [
            'amount.required'=>'Поле Сумма обязательное.',
            'amount.max'=>'Сумма не может быть больше 75 000руб.',
            'make.required'=>'Для совершения операции необходимо принять соглашение',
            'wallet_number.required'=>'Введите номер кошелька, куда переводить криптовалюту'
        ]);
        $validation->sometimes(['maxAmount'],'max',function($input){
            switch($input->currency){
                case "RUB": return $input->amount<=75000;
                case "EUR": return $input->amount<=1500;
                case "USD": return $input->amount<=2000;
            }
        });
        if ($validation->fails()) {
            // print_r($validation);
            return Redirect::back()->withErrors($validation)->withInput();
            // throw new ValidationException($validation);
            // $this->page["messages"] = $validation;
            // return false;
        }
        $this->page["amount"] = post("amount");
        $this->page["currency"] = post("currency");
        $this->page["wallet"] = post("wallet");
        $this->page["wallet_number"] = post("wallet_number");
        $this->page["coins"] = post("coins");
        $data = post();
        $this->page["description"] = http_build_query($data);
        $controller = new TransactionController();
        $retval = $controller->transferRequest();
        $this->page["redirectUrl"] = "/pne/form_first?".$this->page["description"];
        $this->renderPartial('@_pne_form.htm');
        // $this->page["redirectUrl"] = (!isset($retval["error"]) && isset($retval["redirectUrl"]))?$retval["redirectUrl"]:Redirect::back();
        return $retval;
    }
    public function onFakePayment(){
        $data = post();
        $this->page["description"] = http_build_query($data);
        return Redirect::to('/pne/form_second?'.$this->page["description"]);
    }
    public function onEmail(){
        $data = post();
        $this->page["description"] = http_build_query($data);
        return Redirect::to('/pne/form_third?'.$this->page["description"]);
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
