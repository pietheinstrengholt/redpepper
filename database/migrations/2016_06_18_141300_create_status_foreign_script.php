<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusForeignScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::table('t_bim_ontology', function (Blueprint $table) {
			$table->foreign('status_id')->references('id')->on('t_bim_status')->onDelete('cascade');
		});

		Schema::table('t_bim_terms', function (Blueprint $table) {
			$table->foreign('status_id')->references('id')->on('t_bim_status')->onDelete('cascade');
		});

		Schema::table('t_bim_glossaries', function (Blueprint $table) {
			$table->foreign('status_id')->references('id')->on('t_bim_status')->onDelete('cascade');
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
