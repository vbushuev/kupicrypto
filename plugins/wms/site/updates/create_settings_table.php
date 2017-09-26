<?php namespace Wms\Site\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('wms_site_settings', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->text('contacts')->nullable();
            $table->string('copywrite')->nullable();
            $table->text('terms')->nullable();
            $table->text('offer')->nullable();
            $table->text('warranty')->nullable();
            $table->text('howitwork')->nullable();
            $table->text('credit')->nullable();
            $table->string('phone')->nullable();
            $table->text('politics')->nullable();
            $table->text('transaction')->nullable();
            $table->string('email')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wms_site_settings');
    }
}
