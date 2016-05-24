<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalDescriptionsScript extends Migration
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
