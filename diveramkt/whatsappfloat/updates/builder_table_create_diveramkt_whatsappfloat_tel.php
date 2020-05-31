<?php namespace Diveramkt\Whatsappfloat\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDiveramktWhatsappfloatTel extends Migration
{
    public function up()
    {
        Schema::create('diveramkt_whatsappfloat_tel', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->string('numero', 25);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('diveramkt_whatsappfloat_tel');
    }
}
