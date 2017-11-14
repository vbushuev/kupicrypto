<?php namespace Vsb\Pne;

use Log;
use User;
use Event;
use Backend;
use Redirect;
use System\Classes\PluginBase;

use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\Setting;

class Plugin extends PluginBase{
    public $elevated=true;
    public function registerComponents()
    {
        return [
            '\Vsb\Pne\Components\CardPool' => 'cardPool',
            '\Vsb\Pne\Components\CardPoolRegisterResponse' => 'cardPoolRegisterResponse',
            '\Vsb\Pne\Components\Transfer' => 'transferRequest',
            '\Vsb\Pne\Components\TransferResponse' => 'transferResponse',
        ];
    }

    public function registerSettings()
    {
        return [
            'location' => [
                'label'       => 'Payneteasy module',
                'description' => '',
                'category'    => 'Settings',
                'icon'        => 'icon-leaf',
                'class'       => 'Vsb\Pne\Models\Setting',
                'order'       => 500,
            ]
        ];
    }
    public function registerSchedule($schedule){
        $schedule->call(function () {
            $cards = Card::update(['daily_limit'=>Setting::get('cardregister.0.maxDaily')]);
        })->dailyAt('03:00'); // every day at 3:00
        $schedule->call(function () {
            $cards = Card::update(['monthly_limit'=>Setting::get('cardregister.0.maxMonthly')]);
        })->cron('0 3 1 * *'); //first day of month at 3:00
    }
    public function registerMarkupTags(){
        return [
            'filters' => [
                // A global function, i.e str_plural()
                // 'plural' => 'str_plural',

                // A local method, i.e $this->makeTextAllCaps()
                'currency' => [$this, 'makeTextCurrency'],
                'visa' => function($text){ return substr($text,0,1) == '4';},
                'mastercard' => function($text){ return substr($text,0,1) == '5';},
            ],
            // 'functions' => [
            //     // A static method call, i.e Form::open()
            //     'form_open' => ['October\Rain\Html\Form', 'open'],
            //
            //     // Using an inline closure
            //     'helloWorld' => function() { return 'Hello World!'; }
            // ]
        ];
    }
    public function makeTextCurrency($number,$text){
        $cur = $text;
        switch($text){
            case "RUB": $cur= "&#8381;";break;
            case "USD": $cur= "$";break;
            case "EUR": $cur= "&euro;";break;
        }
        return number_format ( floatval($number) , 2, "." , " ") . $cur;
        // return strtoupper($text);
    }
    public function boot(){
        Event::listen('backend.user.login', function($user) {
            Log::debug('backend.user.login event fired.');
            Log::debug($user);
            if ($user->hasPermission([
                'cardpool'
            ])) {
                Log::debug('backend.user has cardpool permision');
                Redirect::to("manager/cardpool");
                // dd(Event::firing());
            }

        });
    }
}
