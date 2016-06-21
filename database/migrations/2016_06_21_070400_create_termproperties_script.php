<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermPropertiesScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::create('t_bim_terms_properties', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('term_id')->unsigned();
			$table->string('property_name');
			$table->string('property_value')->nullable();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::table('t_bim_terms_properties', function (Blueprint $table) {
			$table->foreign('term_id')->references('id')->on('t_bim_terms')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
	{
		//
	}
}
