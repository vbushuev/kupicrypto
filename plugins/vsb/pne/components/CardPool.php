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
use Vsb\Pne\Models\Project;
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\SaleRequest;
use Vsb\Pne\Classes\Pne\CreateCardRefRequest;
use Vsb\Pne\Classes\Pne\ReturnRequest;
use Vsb\Pne\Classes\Pne\CallbackResponse;
use Vsb\Pne\Controllers\CardPoolController;


use Backend\Widgets\Lists;

class CardPool extends ComponentBase{
    protected $controller;
    public function onInit(){
        $this->controller = new CardPoolController();
    }
    public function onRun(){
        $this->addJs('/plugins/vsb/pne/assets/js/pne.js');
        $this->addCss('/plugins/vsb/pne/assets/css/pne.css');
        $this->page['title'] = Lang::get('vsb.pne::lang.cardpool.title');
        $this->controller = new CardPoolController();
        // $this->controller->makeLists();
        // $this->page['list'] = $this->controller->listRender();
        $this->page['contoller'] = $this->controller;
        $this->page['cardpool'] = $this->controller->getList();
        $this->page['cardpool_count'] = count($this->page['cardpool']);
        $this->page['projects'] = Project::all();

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
    public function onDelete(){
        $this->controller = new CardPoolController();
    }
    public function onAddCard(){
        $this->controller = new CardPoolController();
        $retval = $this->controller->registerCard();

        return  (!isset($retval["error-code"]))?Redirect::away($retval["redirect-url"]):$retval;
    }
    public function onEditCard(){
        $this->page['projects'] = Project::all();
        $this->page['card'] = Card::find(post("card_id"));
    }
    public function onUpdateCard(){
        $this->controller = new CardPoolController();
        $r = $this->controller->updateCard();
        $this->page['cardpool'] = $this->controller->getList();
        $this->page['cardpool_count'] = count($this->controller->getList());
        // return;
    }
    public function onDeleteCard(){
        $this->controller = new CardPoolController();
        $retval = $this->controller->removeCard();
        $this->page['cardpool'] = $this->controller->getList();
        $this->page['cardpool_count'] = count($this->controller->getList());
        return;
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
