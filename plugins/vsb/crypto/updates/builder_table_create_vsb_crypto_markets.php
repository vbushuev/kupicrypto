<?php namespace vsb\Crypto\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVsbCryptoMarkets extends Migration
{
    public function up()
    {
        Schema::create('vsb_crypto_markets', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->text('url');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('vsb_crypto_markets');
    }
}
