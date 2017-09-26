<?php namespace Wms\Site;

use Backend;
use System\Classes\PluginBase;

/**
 * site Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'site',
            'description' => 'No description provided yet...',
            'author'      => 'wms',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    public function registerSettings()
    {
        return [
            'location' => [
                'label'       => 'Настройки сайта',
                'description' => '',
                'category'    => 'Settings',
                'icon'        => 'icon-globe',
                'class'         => 'wms\site\models\Setting',
                'order'       => 500,
            ]
        ];
    }
    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Wms\Site\Components\Site' => 'Site',
            'Wms\Site\Components\Calls' => 'Calls',
            'Wms\Site\Components\Feedbacks' => 'Feedbacks',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'wms.site.some_permission' => [
                'tab' => 'site',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {

        return [
            'site' => [
                'label'       => 'Настройки сайта',
                'url'         => Backend::url('wms/site/Calls'),
                'icon'        => 'icon-leaf',
                'permissions' => ['wms.site.*'],
                'order'       => 500,
                'sideMenu'    => [
                    'Calls'   => [
                        'label' => 'Запросы на звонок',
                        'icon'        => 'icon-list-alt',
                        'url'         => \Backend::url('wms/site/Calls'),
                    ],
                    'Feedbacks'   => [
                        'label' => 'Отзывы',
                        'icon'        => 'icon-list-alt',
                        'url'         => \Backend::url('wms/site/Feedbacks'),
                    ],
                ]
            ],
        ];
    }
}
