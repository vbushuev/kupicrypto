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
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\SaleRequest;
use Vsb\Pne\Classes\Pne\CreateCardRefRequest;
use Vsb\Pne\Classes\Pne\ReturnRequest;
use Vsb\Pne\Classes\Pne\CallbackResponse;
use Vsb\Pne\Controllers\TransactionController;

class TransferResponse extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => Lang::get('vsb.pne::lang.transfer.response.title'),
            'description' => Lang::get('vsb.pne::lang.transfer.response.description')
        ];
    }
    public function onRun(){
        $cpc = new TransactionController();
        $cpc->transferResponse();
        // return Redirect::back();
    }
}
