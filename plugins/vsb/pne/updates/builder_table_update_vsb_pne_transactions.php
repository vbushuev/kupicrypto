<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPneTransactions extends Migration
{
    public function up()
    {
        Schema::table('vsb_pne_transactions', function($table)
        {
            $table->integer('card_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pne_transactions', function($table)
        {
            $table->dropColumn('card_id');
        });
    }
}
