<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPneCards3 extends Migration
{
    public function up()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->integer('project_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->dropColumn('project_id');
        });
    }
}
