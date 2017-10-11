<?php namespace Vsb\Pne\Components;

use Log;
use Lang;
use Request;
use Redirect;
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
        $this->page['title'] = Lang::get('vsb.pne::lang.transfer.title');
        $this->controller = new CardPoolController();
        $this->controller->makeLists();
        $this->page['list'] = $this->controller->listRender();
        $this->page['contoller'] = $this->controller;
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
        ];
        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            // print_r($validation);
            // Redirect::back()->withErrors($validation);
            throw new ValidationException($validation);
        }
        $controller = new TransactionController();
        $retval = $controller->transferRequest();
        return  (!isset($retval["error"]) && isset($retval["redirectUrl"]))?Redirect::away($retval["redirectUrl"]):$retval;
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
