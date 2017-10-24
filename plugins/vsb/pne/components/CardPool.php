<?php namespace Vsb\Pne\Components;

use Log;
use Auth;
use Lang;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\UserProject;
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

use BackendAuth;


use Backend\Widgets\Lists;

class CardPool extends ComponentBase{
    protected $controller;
    public function onInit(){
        $this->controller = new CardPoolController();
    }
    protected function getProjects(){
        $controller = new CardPoolController();
        $this->page['superuser'] = $controller->checkSuperUser();
        $this->page['projects'] = $controller->getProjectList();
        $this->page['contoller'] = $controller;
        $this->page['cardpool'] = $controller->getList();
        $this->page['cardpool_count'] = count($this->page['cardpool']);
    }
    public function onRun(){

        $this->addJs('/plugins/vsb/pne/assets/js/pne.js');
        $this->addCss('/plugins/vsb/pne/assets/css/pne.css');
        $this->page['title'] = Lang::get('vsb.pne::lang.cardpool.title');
        $this->getProjects();

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
    // 
    // public function getCards(){
    //     return Card::orderBy('created_at','desc')->get();
    // }
    public function onDelete(){
        $this->getProjects();
    }
    public function onAddCard(){
        $this->controller = new CardPoolController();
        $retval = $this->controller->registerCard();

        return  (!isset($retval["error-code"]))?Redirect::away($retval["redirect-url"]):$retval;
    }
    public function onEditCard(){
        $this->getProjects();
        $this->page['card'] = Card::find(post("card_id"));
    }
    public function onUpdateCard(){
        $this->controller = new CardPoolController();
        $r = $this->controller->updateCard();
        $this->getProjects();
        // return;
    }
    public function onDeleteCard(){
        $controller = new CardPoolController();
        $retval = $controller->removeCard();
        $this->getProjects();
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
