<?php namespace Vsb\Pne\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateVsbPneTransactions extends Migration
{
    public function up()
    {
        Schema::create('vsb_pne_transactions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('type', 64);
            $table->string('endpoint', 64);
            $table->decimal('amount', 12, 5);
            $table->integer('code');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('vsb_pne_transactions');
    }
}
