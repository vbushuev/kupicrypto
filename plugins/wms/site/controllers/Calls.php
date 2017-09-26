<?php namespace Wms\Site\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Calls Back-end Controller
 */
class Calls extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Wms.Site', 'site', 'calls');
    }
}
