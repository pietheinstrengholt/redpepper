<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalDescriptions extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::create('t_technical_descriptions', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('type_id')->unsigned();	
			$table->string('content')->nullable();
			$table->longText('description')->nullable();
			$table->timestamps();
		});
		
		Schema::table('t_technical_descriptions', function (Blueprint $table) {
			$table->index('type_id');
		});
		
		Schema::table('t_technical_descriptions', function (Blueprint $table) {
			$table->foreign('type_id')->references('id')->on('t_technical_types')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		Schema::drop('t_technical_descriptions');
	}
}
