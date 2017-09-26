<?php namespace Wms\Site\Components;

use Cms\Classes\ComponentBase;
use Wms\Site\Models\Feedback;

class Feedbacks extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'feedbacks Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function getFeedbacks(){
        return Feedback::where('active',1)->orderBy('id','desc')->get();
    }

    public function defineProperties()
    {
        return [];
    }
}
