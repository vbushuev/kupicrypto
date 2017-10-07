<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPneCards extends Migration
{
    public function up()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->renameColumn('daily_balance', 'daily_limit');
            $table->renameColumn('monthly_balance', 'monthly_limit');
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pne_cards', function($table)
        {
            $table->renameColumn('daily_limit', 'daily_balance');
            $table->renameColumn('monthly_limit', 'monthly_balance');
        });
    }
}
