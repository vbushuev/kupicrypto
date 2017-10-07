<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPneTransactions2 extends Migration
{
    public function up()
    {
        Schema::table('vsb_pne_transactions', function($table)
        {
            $table->integer('parent_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pne_transactions', function($table)
        {
            $table->dropColumn('parent_id');
        });
    }
}
