<?php namespace Vsb\Pne\Models;

use Model;

/**
 * Model
 */
class Card extends Model
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
    public $table = 'vsb_pne_cards';
    protected $fillable = [
        'card_ref','pan','expire','cvv2','daily_balance','monthly_balance'
    ];
}
