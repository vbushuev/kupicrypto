<?php namespace Wms\Site\Models;

use Model;

/**
 * setting Model
 */
class Setting extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'wms_site_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    protected $jsonable = ['credit','transaction','howitwork','warranty'];

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
}
