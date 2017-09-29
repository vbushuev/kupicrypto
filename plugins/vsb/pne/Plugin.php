<?php namespace Vsb\Pne;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase{
    public function registerComponents()
    {
        return [
            '\Vsb\Pne\Components\CardPool' => 'cardPool',
            '\Vsb\Pne\Components\CardPoolRegisterResponse' => 'CardPoolRegisterResponse'
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
