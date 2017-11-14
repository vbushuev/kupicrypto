<?php namespace vsb\Crypto\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbCryptoRates extends Migration
{
    public function up()
    {
        Schema::table('vsb_crypto_rates', function($table)
        {
            $table->integer('volate')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('vsb_crypto_rates', function($table)
        {
            $table->dropColumn('volate');
        });
    }
}
