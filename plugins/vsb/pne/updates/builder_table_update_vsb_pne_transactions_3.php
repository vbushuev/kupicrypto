<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPneTransactions3 extends Migration
{
    public function up()
    {
        Schema::table('vsb_pne_transactions', function($table)
        {
            $table->string('currency', 3)->default('RUB');
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pne_transactions', function($table)
        {
            $table->dropColumn('currency');
        });
    }
}
