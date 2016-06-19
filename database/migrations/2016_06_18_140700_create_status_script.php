<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::create('t_bim_status', function (Blueprint $table) {
			$table->increments('id');
			$table->string('status_name')->unique();
			$table->string('status_description')->nullable();
			$table->integer('created_by');
			$table->timestamps();
		});

		Schema::table('t_bim_ontology', function (Blueprint $table) {
			$table->integer('status_id')->after('object_id')->unsigned();
		});

		Schema::table('t_bim_glossaries', function (Blueprint $table) {
			$table->integer('status_id')->after('id')->unsigned();
		});

		Schema::table('t_bim_terms', function (Blueprint $table) {
			$table->integer('status_id')->after('glossary_id')->unsigned();
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
