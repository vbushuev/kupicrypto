<?php namespace Vsb\Pne\Models;

use Model;
use Vsb\Pne\Models\Project;
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
        'card_ref','pan','expire','cvv2','daily_limit','monthly_limit','enabled','project_id'
    ];
    public function limits($amt){
        $this->daily_limit -= floatval($amt);
        $this->monthly_limit -= floatval($amt);
        $this->save();
    }
    public function rollback($amt){
        $this->daily_limit += floatval($amt);
        $this->monthly_limit += floatval($amt);
        $this->save();
    }
    public function getProjectIdOptions(){
        $projects = Project::all();$ret=[];
        foreach($projects as $project){
            $ret[$project->id]=$project->name;
        }
        return $ret;
    }
    public $belongsTo = [
        'project' => [
            'Vsb\Pne\Models\Project'
        ]
    ];
}
