<?php namespace vsb\Crypto\Models;

use Model;

/**
 * Model
 */
class Rate extends Model
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
    public $table = 'vsb_crypto_rates';
    protected $fillable = [
        'market_id','from','to','price','volation','isdefault'
    ];
    public $belongsTo = [
        'market' => [
            'Vsb\Crypto\Models\Market',
        ]
    ];
}
