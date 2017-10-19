<?php namespace Vsb\Pne\Models;

use Model;
use Cms\Classes\Page;
use RainLab\User\Models\UserGroup;

/**
 * Model
 */
 class Setting extends Model
 {
     public $implement = ['System.Behaviors.SettingsModel'];

     public $settingsCode = 'vsb_pne_settings';

     // Reference to field configuration
     public $settingsFields = 'fields.yaml';

    //  protected $jsonable = ['endpoint','current_endpoint','cardregister'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
    public function getCurrentEndpointOptions(){
        $ret = [];
        if(!isset($this->endpoint) || !count($this->endpoint)) return [];
        foreach($this->endpoint as $ep){
            $ret[] = $ep['endpoint']." - ".(isset($ep['version'])?$ep['version']:"v2");
        }
        return $ret;
    }
    public function getProjectIdOptions(){
        $res = [];
        foreach(Project::all() as $project){
            $res[$project->id] = $project->name;
        }
        return $res;
    }
    public function getResponseOptions(){
        return Page::sortBy('baseFileName')->lists('baseFileName', 'url');
    }
    public function getCallbackOptions(){
        return Page::sortBy('baseFileName')->lists('baseFileName', 'url');
    }
    public function getPageOptions(){
        return Page::sortBy('baseFileName')->lists('baseFileName', 'url');
    }
    public function getSupergroupOptions(){
        $res = [];
        foreach (UserGroup::all() as $group) {
            $res[$group->code]=$group->name;
        }
        return $res;
    }
 }
