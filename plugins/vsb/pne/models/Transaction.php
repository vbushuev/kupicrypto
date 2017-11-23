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
    protected $casts = [
        'raw' => 'array',
    ];
    /**
     * @var string The database table used by the model.
     */
    public $table = 'vsb_pne_transactions';
    protected $fillable = [
        'endpoint','amount','currency','type','code','card_id','parent_id','description'
    ];
    public $belongsTo = [
        'card' => [
            'Vsb\Pne\Models\Card'
        ]
    ];
    public function getRawAttribute(){
        $res = [];
        parse_str($this->description,$res);
        return $res;
    }
}
