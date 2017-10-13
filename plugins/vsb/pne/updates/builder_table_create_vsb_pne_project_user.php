<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVsbPneProjectUser extends Migration
{
    public function up()
    {
        Schema::create('vsb_pne_project_user', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('user_id')->unsigned();
            $table->integer('project_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('vsb_pne_project_user');
    }
}
