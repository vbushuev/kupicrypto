<?php namespace Wms\Site\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFeedbacksTable extends Migration
{
    public function up()
    {
        Schema::create('wms_site_feedbacks', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('subject')->nullable();
            $table->text('text')->nullable();
            $table->integer('sort')->nullable();
            $table->integer('active')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wms_site_feedbacks');
    }
}
