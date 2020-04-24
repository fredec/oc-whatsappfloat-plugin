<?php namespace Diveramkt\Floatingbanner\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use DB;

class BuilderTableCreateDiveramktFloatingbannerExitpopup extends Migration
{
    public function up()
    {
        Schema::create('diveramkt_floatingbanner_exitpopup', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title', 255);
            $table->string('image', 255);
            $table->string('link', 255);
            $table->boolean('enabled');
            $table->text('description');
        });
        
        
        // Insert some stuff
        DB::table('diveramkt_floatingbanner_exitpopup')->insert(
            array(
                'id' => '1',
                'title' => 'ExitBanner',
                'enabled' => false
            )
        );
    }
    
    public function down()
    {
        Schema::dropIfExists('diveramkt_floatingbanner_exitpopup');
    }
}