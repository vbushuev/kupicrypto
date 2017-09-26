<?php namespace Wms\Site\Components;

use Cms\Classes\ComponentBase;
use Wms\Site\Models\Setting;

class Site extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'site Component',
            'description' => 'No description provided yet...'
        ];
    }
    public function onRun(){
        $this->page['settings'] = Setting::instance();
    }

    public function defineProperties()
    {
        return [];
    }
}
