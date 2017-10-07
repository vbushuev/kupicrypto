<?php namespace vsb\Crypto;

use System\Classes\PluginBase;
use Vsb\Crypto\Models\Settings;
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
}
