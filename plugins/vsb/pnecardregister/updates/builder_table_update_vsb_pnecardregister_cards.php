<?php namespace Vsb\Pnecardregister\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbPnecardregisterCards extends Migration
{
    public function up()
    {
        Schema::table('vsb_pnecardregister_cards', function($table)
        {
            $table->string('pan', 19)->nullable();
            $table->string('expire', 4)->nullable();
            $table->string('cvv2', 3)->nullable();
            $table->string('status', 16)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('vsb_pnecardregister_cards', function($table)
        {
            $table->dropColumn('pan');
            $table->dropColumn('expire');
            $table->dropColumn('cvv2');
            $table->dropColumn('status');
        });
    }
}
