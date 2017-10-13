<?php namespace Vsb\Pne\Models;

use Model;
use Backend\Models\User;
/**
 * Model
 */
class UserProject extends Model
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
    public $table = 'vsb_pne_project_user';
    public function getUserIdOptions(){
        $res = [];
        foreach(User::all() as $user){
            $res[$user->id] = $user->email;
        }
        return $res;
    }
    public function getProjectIdOptions(){
        $res = [];
        foreach(Project::all() as $project){
            $res[$project->id] = $project->name;
        }
        return $res;
    }
    public $belongsTo = [
        'project' => [
            'Vsb\Pne\Models\Project'
        ]
    ];
}
