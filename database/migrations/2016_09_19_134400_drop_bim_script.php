<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropBimScript extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		if (Schema::hasTable('t_bim_terms_properties')) {
			Schema::drop('t_bim_terms_properties');
		}

		if (Schema::hasTable('t_bim_ontology')) {
			Schema::drop('t_bim_ontology');
		}

		if (Schema::hasTable('t_bim_relation_types')) {
			Schema::drop('t_bim_relation_types');
		}

		if (Schema::hasTable('t_bim_terms')) {
			Schema::drop('t_bim_terms');
		}

		if (Schema::hasTable('t_bim_glossaries')) {
			Schema::drop('t_bim_glossaries');
		}

		if (Schema::hasTable('t_bim_status')) {
			Schema::drop('t_bim_status');
		}

		Schema::create('t_terms', function (Blueprint $table) {
			$table->increments('id');
			$table->string('term_name')->nullable();
			$table->string('term_description')->nullable();
			$table->integer('owner_id')->unsigned();
			$table->integer('created_by');
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
		//
	}
}
