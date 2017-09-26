<?php namespace Vsb\Pne;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    // public function pluginDetails()
    // {
    //     return [
    //         'name'        => 'pnecardregister',
    //         'description' => 'No description provided yet...',
    //         'author'      => 'vsb',
    //         'icon'        => 'icon-leaf'
    //     ];
    // }
    public function registerComponents()
    {
        return [
            '\Vsb\Pne\Components\CardRegister' => 'cardRegister'
        ];
    }

    public function registerSettings()
    {
        // return [
        //     'location' => [
        //         'label'       => 'Payment module',
        //         'description' => '',
        //         'category'    => 'Settings',
        //         'icon'        => 'icon-leaf',
        //         'class'       => 'vsb\pnecardregister\models\Setting',
        //         'order'       => 500,
        //     ]
        // ];
    }

    // /**
    //  * Registers back-end navigation items for this plugin.
    //  *
    //  * @return array
    //  */
    // public function registerNavigation()
    // {
    //
    //
    // }
}
