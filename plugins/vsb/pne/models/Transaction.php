<?php namespace Vsb\Pne\Models;

use Model;

/**
 * Model
 */
class Transaction extends Model
{
    use \October\Rain\Database\Traits\Validation;

    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'vsb_pne_transactions';
    protected $fillable = [
        'endpoint','amount','currency','type','code','card_id','parent_id','description'
    ];
}
