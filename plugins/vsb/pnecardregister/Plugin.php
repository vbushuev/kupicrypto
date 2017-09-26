<?php namespace Vsb\Pnecardregister;

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
    }

    public function registerSettings()
    {
        return [
            'location' => [
                'label'       => 'Card register settings',
                'description' => '',
                'category'    => 'Settings',
                'icon'        => 'icon-globe',
                'class'       => 'vsb\pnecardregister\models\Setting',
                'order'       => 500,
            ]
        ];
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
