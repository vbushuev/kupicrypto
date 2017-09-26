<?php namespace Vsb\Pnecardregister\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVsbPnecardregisterCards extends Migration
{
    public function up()
    {
        Schema::create('vsb_pnecardregister_cards', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('card_ref', 128);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('vsb_pnecardregister_cards');
    }
}
