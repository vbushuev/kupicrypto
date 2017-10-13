<?php namespace Vsb\Pne\Controllers;

use Log;
use Backend\Models\User;
use Request;
use Response;
use Input;

use Vsb\Pne\Classes\Exception;
use Cms\Classes\Controller;
use Vsb\Pne\Models\Card;
use Vsb\Pne\Models\Project;
use Vsb\Pne\Models\UserProject;
use Vsb\Pne\Models\Transaction;
use Vsb\Pne\Models\Setting;
use Vsb\Pne\Classes\Pne\Exception as PneException;
use Vsb\Pne\Classes\Pne\Connector;
use Vsb\Pne\Classes\Pne\SaleRequest;
use Vsb\Pne\Classes\Pne\CreateCardRefRequest;
use Vsb\Pne\Classes\Pne\ReturnRequest;
use Vsb\Pne\Classes\Pne\CallbackResponse;

class CardPoolApiController extends Controller{
    public function __construct(){}
    protected function checkUser($data){
        // check user
        $user = isset($data->token)?$data->token:false;
        if($user == false) throw new Exception("Authenticate error. Wrong token in request",403);
        $user = User::where('email','=',$user)->first();
        if(is_null($user))throw new Exception("Authenticate error. No user found",403);
        $project = isset($data->project)?$data->project:false;
        if($project===false){
            $project = UserProject::where("user_id","=",$user->id);
            if(count($project->get()->toArray())>1)throw new Exception("Should specify project",403);
            else $project = $project->first();
        }
        else $project = UserProject::where("user_id","=",$user->id)->where('project_id','=',$project)->first();
        if(is_null($project))throw new Exception("Not accepteble to project",403);
        // end check user
        return [$user,$project];
    }
    public function getProjects(){
        $res = [];
        try{
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData);
            list($user,$project) = $this->checkUser($data);
            $projects=UserProject::where('user_id','=',$user->id)->lists('project_id');
            $projects = Project::whereIn('id',$projects)->get();
            $res = [
                "error"=>"0",
                "message"=>"Ok",
                "response" => $projects
            ];
        }
        catch(Exception $e){
            $res = [
                "error"=>$e->getCode(),
                "message"=>$e->getMessage()
            ];
        }
        catch(\Exception $e){
            $res = [
                "error"=>"500",
                "message"=>$e->getMessage()
            ];
        }
        return Response::json($res);
    }
    public function getCardPool(){
        $res = [];
        try{
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData);
            list($user,$project) = $this->checkUser($data);
            $projects=UserProject::with('project')->where('user_id','=',$user->id)->lists('project_id');
            $cards = Card::whereIn("project_id",$projects)->get();
            $res = [
                "error"=>"0",
                "message"=>"Ok",
                "response" => $cards
            ];
        }
        catch(Exception $e){
            $res = [
                "error"=>$e->getCode(),
                "message"=>$e->getMessage()
            ];
        }
        catch(\Exception $e){
            $res = [
                "error"=>"500",
                "message"=>$e->getMessage()
            ];
        }
        return Response::json($res);
    }
    public function getCardFromPool(){
        $res = [];
        try{
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData);
            list($user,$project) = $this->checkUser($data);
            $amount = isset($data->request->amount)?$data->request->amount:1000;
            $currency = isset($data->request->currency)?$data->request->currency:'RUB';

            $card = Card::where('enabled','=','1')
                ->whereNull('deleted_at')
                ->where('daily_limit','>=',$amount)
                ->where('monthly_limit','>=',$amount)
                ->where('project_id','=',$project->project_id)
                ->orderBy('monthly_limit','desc')->orderBy('daily_limit','desc');
            $card=$card->first();
            if(is_null($card))throw new Exception("No card avaliable",404);
            $trx = Transaction::create([
                'endpoint'=> '0',
                'amount'=>$amount,
                'currency'=>$currency,
                'type'=>'cardpool',
                'code'=>'200',
                'card_id' => $card->id
            ]);
            $card->limits($amount);
            $res = [
                "id" => $trx->id,
                "error"=>"0",
                "message"=>"Ok",
                "response" => $card->toArray()
            ];
        }
        catch(Exception $e){
            $res = [
                "error"=>$e->getCode(),
                "message"=>$e->getMessage()
            ];
        }
        catch(\Exception $e){
            $res = [
                "error"=>"500",
                "message"=>$e->getMessage()
            ];
        }
        return Response::json($res);
    }
    public function getCardFromPoolRollback(){
        $res = [];
        try{
            $rawData = file_get_contents('php://input');
            $res = [
                "error"=>"404",
                "message"=>"Not found",
            ];
            $data = json_decode($rawData);
            list($user,$project) = $this->checkUser($data);
            $id = isset($data->request->id)?$data->request->id:0;
            $trx = Transaction::find($id);
            if(!is_null($trx)){
                $card = Card::find($trx->card_id);
                $card->rollback($trx->amount);
                $res = [
                    "id" => $trx->id,
                    "error"=>"0",
                    "message"=>"Ok",
                    "response" => $card->toArray()
                ];
            }
        }
        catch(Exception $e){
            $res = [
                "error"=>$e->getCode(),
                "message"=>$e->getMessage()
            ];
        }
        catch(\Exception $e){
            $res = [
                "error"=>"500",
                "message"=>$e->getMessage()
            ];
        }
        return Response::json($res);
    }
    public function callAction($method,$parameters){
        Log::debug($method);
        if(method_exists($this,$method)){
            return $this->$method($parameters);
        }
    }
    public function getMiddleware(){ return [];}
    public function getBeforeFilters(){ return [];}
    public function getAfterFilters(){ return [];}
}
