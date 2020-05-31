<?php namespace Diveramkt\Whatsappfloat\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDiveramktWhatsappfloatTel extends Migration
{
    public function up()
    {
        Schema::table('diveramkt_whatsappfloat_tel', function($table)
        {
            $table->integer('id')->unsigned()->change();
            $table->primary(['id']);
        });
    }
    
    public function down()
    {
        Schema::table('diveramkt_whatsappfloat_tel', function($table)
        {
            $table->dropPrimary(['id']);
            $table->integer('id')->unsigned(false)->change();
        });
    }
}
