<?php namespace Diveramkt\Floatingbanner\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDiveramktFloatingbannerPopup extends Migration
{
    public function up()
    {
        Schema::table('diveramkt_floatingbanner_popup', function($table)
        {
            $table->dateTime('data_entrada')->nullable();
            $table->dateTime('data_saida')->nullable();
            $table->integer('dias_oculto')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('diveramkt_floatingbanner_popup', function($table)
        {
            $table->dropColumn('data_entrada');
            $table->dropColumn('data_saida');
            $table->dropColumn('dias_oculto');
        });
    }
}