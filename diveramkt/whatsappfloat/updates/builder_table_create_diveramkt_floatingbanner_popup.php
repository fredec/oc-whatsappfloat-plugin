<?php namespace Diveramkt\Floatingbanner\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use DB;

class BuilderTableCreateDiveramktFloatingbannerPopup extends Migration
{
    public function up()
    {
        Schema::create('diveramkt_floatingbanner_popup', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title', 255);
            $table->string('image', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->boolean('enabled')->nullable();
            $table->text('description')->nullable();
        });
        
        // Insert some stuff
        DB::table('diveramkt_floatingbanner_popup')->insert(
            array(
                'id' => '1',
                'title' => 'FloatingBanner',
                'enabled' => false
            )
        );
    }
    
    public function down()
    {
        Schema::dropIfExists('diveramkt_floatingbanner_popup');
    }
}