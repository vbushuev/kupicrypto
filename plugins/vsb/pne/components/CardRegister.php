<?php namespace Vsb\Pne\Components;

use Lang;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Vsb\Pne\Models\Card;

class CardRegister extends ComponentBase
{
    public $title;
    public function oInit(){
        $this->page['title'] = Lang::get('vsb.pne::plugin.cardregister_title');
    }
    public function componentDetails()
    {
        return [
            'name'        => 'Card Register',
            'description' => 'Implements of autoregistering cards'
        ];
    }

    public function defineProperties()
    {
        return [
            'max' => [
                'description'       => 'The most amount of todo items allowed',
                'title'             => 'Max items',
                'default'           => 10,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items value is required and should be integer.'
            ]
        ];
    }

    public function cards(){
        $this->page['cards'] = Card::all();
    }
    public function onAddCard(){
        $this->page['cards'] = Card::all();
        return $this->page['cards'];
    }
    // public function onAddItem()
    // {
    //     $items = post('items', []);
    //
    //     if (count($items) >= $this->property('max')) {
    //         throw new ApplicationException(sprintf('Sorry only %s items are allowed.', $this->property('max')));
    //     }
    //
    //     if (($newItem = post('newItem')) != '') {
    //         $items[] = $newItem;
    //     }
    //
    //     $this->page['items'] = $items;
    // }
}
