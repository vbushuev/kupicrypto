<?php namespace vsb\Crypto\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVsbCryptoCurrency extends Migration
{
    public function up()
    {
        Schema::create('vsb_crypto_currency', function($table)
        {
            $table->engine = 'InnoDB';
            $table->string('code', 6);
            $table->text('name');
            $table->primary(['code']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('vsb_crypto_currency');
    }
}
