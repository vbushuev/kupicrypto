<?php namespace Vsb\Pne;

use Backend;
use System\Classes\PluginBase;

use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\Setting;

class Plugin extends PluginBase{
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
                'currency' => [$this, 'makeTextCurrency']
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
    public function makeTextCurrency($text){
        switch($text){
            case "RUB": return "Руб.";
            case "USD": return "$";
            case "EUR": return "Eur";
        }
        return strtoupper($text);
    }
}
