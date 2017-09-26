<?php namespace Wms\Site\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Redirect;
use October\Rain\Support\Facades\Flash;
use ValidationException;
use Validator;
use Mail;
use Wms\Site\Models\Call;
use Wms\Site\Models\Setting;


class Calls extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'calls Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onSendCall(){
        $data = post();
        $rules = [
            'phone' => 'required',
            'name' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if($validator->fails()){
            throw new ValidationException($validator);
        }else {
            $call = new Call();
            $call->fill($data);
            $call->save();
            $vars = [
                'data' => post()
            ];
            //dd($reciver->email);
            Mail::send('wms.site::mail.call', $vars, function($message) {
                $message->to(Setting::get('email'), 'Купикрипту.рф');
                $message->subject('Запрос на звонок');
            });
            Flash::success('Ваша заявка принята! Ожидайте');
            return Redirect::refresh();
        }
    }

    public function defineProperties()
    {
        return [];
    }
}
