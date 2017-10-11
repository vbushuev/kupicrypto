<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVsbPneProject extends Migration
{
    public function up()
    {
        Schema::create('vsb_pne_project', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('name', 127);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('vsb_pne_project');
    }
}
