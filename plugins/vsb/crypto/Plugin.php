<?php namespace vsb\Crypto;

use System\Classes\PluginBase;
use Vsb\Crypto\Models\Settings;
use Vsb\Crypto\Controllers\MarketContoller;
class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            '\Vsb\Crypto\Components\Crypto' => 'vsbCrypto',
        ];
    }

    public function registerSettings()
    {
        return [
            'location' => [
                'label'       => 'Crypto module',
                'description' => '',
                'category'    => 'Settings',
                'icon'        => 'icon-leaf',
                'class'       => 'Vsb\Crypto\Models\Settings',
                'order'       => 500,
            ]
        ];
    }
    public function registerSchedule($schedule){
        $schedule->call(function () {
            $ctrl = new MarketContoller();
            $ctrl->LoadData();
        })->everyMinute()	//Run the task every minute
        // })->hourly()
        // ->sendOutputTo('/Applications/AMPPS/www/kupicrypto/shed.log')
        // ->emailOutputTo('vsb@garan24.ru')
        ;
    }
}
