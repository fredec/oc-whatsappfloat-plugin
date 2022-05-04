<?php namespace Diveramkt\Whatsappfloat\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDiveramktWhatsappfloatTel2 extends Migration
{
    public function up()
    {
        Schema::table('diveramkt_whatsappfloat_tel', function($table)
        {
            $table->increments('id')->change();
        });
    }
    
    public function down()
    {
        Schema::table('diveramkt_whatsappfloat_tel', function($table)
        {
            $table->integer('id')->change();
        });
    }
}
