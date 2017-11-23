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

class Transactions extends ComponentBase{
    public function onRun(){
        $this->addJs('/plugins/vsb/pne/assets/js/pne.js');
        $this->addCss('/plugins/vsb/pne/assets/css/pne.css');
        $this->page['title'] = Lang::get('vsb.pne::lang.transactions.title');
        $this->page['transactions'] = Transaction::with('card')->orderBy('id','desc')->paginate();
        $this->page['transaction_count'] = $this->page['transactions']->total();
    }
    public function onTransactionInfo(){
        $this->page['transaction'] = Transaction::find(post('transaction_id'));
    }
    public function componentDetails()
    {
        return [
            'name'        => Lang::get('vsb.pne::lang.transactions.title'),
            'description' => Lang::get('vsb.pne::lang.transactions.description')
        ];
    }
    protected function getTransactions(){

    }

}
