<?php namespace vsb\Crypto\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateVsbCryptoMarkets extends Migration
{
    public function up()
    {
        Schema::table('vsb_crypto_markets', function($table)
        {
            $table->integer('enabled')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('vsb_crypto_markets', function($table)
        {
            $table->dropColumn('enabled');
        });
    }
}
