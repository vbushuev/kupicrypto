<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPneCards4 extends Migration
{
    public function up()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->integer('user_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->dropColumn('user_id');
        });
    }
}
