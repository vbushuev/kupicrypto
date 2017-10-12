<?php namespace vsb\Crypto\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVsbCryptoRates extends Migration
{
    public function up()
    {
        Schema::create('vsb_crypto_rates', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('market_id')->unsigned();
            $table->string('from', 6);
            $table->string('to', 6);
            $table->decimal('price', 10, 5);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('vsb_crypto_rates');
    }
}
