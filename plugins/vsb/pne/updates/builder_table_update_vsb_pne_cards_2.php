<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPneCards2 extends Migration
{
    public function up()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->integer('enabled')->unsigned()->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->dropColumn('enabled');
        });
    }
}
