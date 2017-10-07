<?php namespace Vsb\Crypto\Models;

use Model;
use Cms\Classes\Page;

/**
 * Model
 */
 class Settings extends Model
 {
     public $implement = ['System.Behaviors.SettingsModel'];

     public $settingsCode = 'vsb_crypto_settings';

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

 }
